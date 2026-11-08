<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    private function getProjectId()
    {
        return session('current_project_id', optional(Project::where('institute_id', Auth::user()->institute_id)->first())->id);
    }

    public function index(Request $request)
    {
        $batches = Batch::where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->orderBy('name')
            ->get();

        $sessions = AttendanceSession::with(['batch', 'course'])
            ->withCount('attendances')
            ->where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->when($request->batch_id, fn ($q) => $q->where('batch_id', $request->batch_id))
            ->when($request->month, fn ($q) => $q->whereBetween('session_date', [
                Carbon::parse($request->month)->startOfMonth(),
                Carbon::parse($request->month)->endOfMonth(),
            ]))
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('attendance.index', compact('sessions', 'batches'));
    }

    public function create()
    {
        $batches = Batch::with('course')
            ->where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $project = Project::find($this->getProjectId());

        return view('attendance.create', compact('batches', 'project'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'title' => 'required|string|max:255',
            'title_bn' => 'nullable|string|max:255',
            'session_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'late_after_minutes' => 'nullable|integer|min:0|max:240',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        abort_if($batch->institute_id !== Auth::user()->institute_id, 403);

        $sessionDate = Carbon::parse($request->session_date);

        $session = AttendanceSession::create([
            'institute_id' => Auth::user()->institute_id,
            'project_id' => $batch->project_id,
            'course_id' => $batch->course_id,
            'batch_id' => $batch->id,
            'title' => $request->title,
            'title_bn' => $request->title_bn,
            'session_date' => $sessionDate->toDateString(),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'late_after_minutes' => $request->late_after_minutes ?? 10,
            'status' => AttendanceSession::STATUS_OPEN,
            'qr_token' => (string) Str::uuid(),
            'qr_expires_at' => $sessionDate->copy()->endOfDay()->addHours(2),
            'taken_by' => Auth::id(),
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('attendance.show', $session)
            ->with('success', 'Attendance session created. Students can now scan the QR.');
    }

    public function show(AttendanceSession $attendanceSession)
    {
        $session = $this->scopedSession($attendanceSession);

        $students = $session->students()->get();
        $marked = $session->attendances()
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('attendance.show', [
            'session' => $session,
            'students' => $students,
            'marked' => $marked,
            'summary' => $session->summary(),
        ]);
    }

    /**
     * Manual marking by the trainer.
     */
    public function mark(Request $request, AttendanceSession $attendanceSession)
    {
        $session = $this->scopedSession($attendanceSession);

        $validated = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'in:present,late,absent'],
        ]);

        $students = $session->students()->pluck('id');
        $submitted = array_intersect_key($validated['attendance'], array_flip($students->all()));
        $now = now();

        foreach ($submitted as $studentId => $status) {
            Attendance::updateOrCreate(
                ['attendance_session_id' => $session->id, 'student_id' => $studentId],
                [
                    'institute_id' => $session->institute_id,
                    'batch_id' => $session->batch_id,
                    'status' => $status,
                    'method' => Attendance::METHOD_MANUAL,
                    'marked_at' => $now,
                    'marked_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('attendance.show', $session)
            ->with('success', 'Attendance saved for '.count($submitted).' student(s).');
    }

    /**
     * Close a session and default every unmarked student to absent.
     */
    public function close(AttendanceSession $attendanceSession)
    {
        $session = $this->scopedSession($attendanceSession);

        $now = now();
        $markedCount = 0;

        foreach ($session->students()->get() as $student) {
            $already = $session->attendances()->where('student_id', $student->id)->exists();
            if ($already) {
                continue;
            }

            Attendance::create([
                'institute_id' => $session->institute_id,
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
                'batch_id' => $session->batch_id,
                'status' => Attendance::STATUS_ABSENT,
                'method' => Attendance::METHOD_AUTO,
                'marked_at' => $now,
                'marked_by' => Auth::id(),
            ]);
            $markedCount++;
        }

        $session->update(['status' => AttendanceSession::STATUS_CLOSED]);

        return redirect()->route('attendance.show', $session)
            ->with('success', "Session closed. {$markedCount} student(s) marked absent automatically.");
    }

    public function reopen(AttendanceSession $attendanceSession)
    {
        $session = $this->scopedSession($attendanceSession);
        $session->update(['status' => AttendanceSession::STATUS_OPEN]);

        return redirect()->route('attendance.show', $session)
            ->with('success', 'Session reopened.');
    }

    public function regenerateQr(AttendanceSession $attendanceSession)
    {
        $session = $this->scopedSession($attendanceSession);

        $session->update([
            'qr_token' => (string) Str::uuid(),
            'qr_expires_at' => $session->session_date->copy()->endOfDay()->addHours(2),
        ]);

        return redirect()->route('attendance.show', $session)
            ->with('success', 'A new QR code has been generated.');
    }

    public function destroy(AttendanceSession $attendanceSession)
    {
        $session = $this->scopedSession($attendanceSession);
        $session->delete();

        return redirect()->route('attendance.index')->with('success', 'Session deleted.');
    }

    /**
     * Monthly attendance register for one batch.
     */
    public function monthlyReport(Request $request)
    {
        [$batch, $month, $rows, $sessionCount] = $this->monthlyData($request);

        return view('attendance.monthly-report', compact('batch', 'month', 'rows', 'sessionCount') + [
            'batches' => $this->reportBatches(),
        ]);
    }

    public function monthlyReportCsv(Request $request)
    {
        [$batch, $month, $rows, $sessionCount] = $this->monthlyData($request);

        return $this->csv(
            'attendance-'.$batch?->code.'-'.$month->format('Y-m').'.csv',
            [
                ['Reference No', 'Student (EN)', 'Student (BN)', 'Sessions', 'Present', 'Late', 'Absent', 'Unmarked', 'Attendance %'],
                ...$rows->map(fn ($row) => [
                    $row['student']->reference_no,
                    $row['student']->name_en,
                    $row['student']->name_bn,
                    $sessionCount,
                    $row['present'],
                    $row['late'],
                    $row['absent'],
                    $row['unmarked'],
                    $row['percentage'].'%',
                ])->all(),
            ]
        );
    }

    /**
     * Project-wise attendance summary across every batch in the project.
     */
    public function projectReport(Request $request)
    {
        [$month, $batches] = $this->projectReportData($request);

        return view('attendance.project-report', compact('month', 'batches') + [
            'reportBatches' => $this->reportBatches(),
        ]);
    }

    public function projectReportCsv(Request $request)
    {
        [$month, $batches] = $this->projectReportData($request);

        $rows = [['Batch', 'Course', 'Students', 'Sessions', 'Present', 'Late', 'Absent', 'Average %']];

        foreach ($batches as $batch) {
            $rows[] = [
                $batch['batch']->name,
                $batch['batch']->course?->name,
                $batch['students'],
                $batch['sessions'],
                $batch['present'],
                $batch['late'],
                $batch['absent'],
                $batch['percentage'].'%',
            ];
        }

        return $this->csv('project-attendance-'.$month->format('Y-m').'.csv', $rows);
    }

    /**
     * Public landing page a student lands on after scanning the QR.
     */
    public function scanForm(string $token)
    {
        $session = $this->sessionForToken($token);

        return view('attendance.scan', compact('session'));
    }

    public function scanStore(Request $request, string $token)
    {
        $session = $this->sessionForToken($token);

        $validated = $request->validate([
            'reference_no' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
        ]);

        $student = Student::where('batch_id', $session->batch_id)
            ->where('reference_no', $validated['reference_no'])
            ->first();

        if (! $student || $student->dob?->toDateString() !== Carbon::parse($validated['dob'])->toDateString()) {
            return back()->withErrors(['reference_no' => 'No matching student found in this batch for that date of birth.'])->withInput();
        }

        $existing = Attendance::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return redirect()->route('attendance.scan', $token)
                ->with('info', 'You are already marked as '.strtoupper($existing->status).' for this session.');
        }

        $now = now();

        Attendance::create([
            'institute_id' => $session->institute_id,
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'batch_id' => $session->batch_id,
            'status' => $session->statusFor($now),
            'method' => Attendance::METHOD_QR,
            'marked_at' => $now,
            'marked_by' => null,
        ]);

        return redirect()->route('attendance.scan', $token)
            ->with('success', 'Attendance recorded as '.strtoupper($session->statusFor($now)).'. Thank you!');
    }

    /**
     * Shared monthly aggregation for the HTML and CSV reports.
     */
    private function monthlyData(Request $request): array
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
            'month' => 'nullable|date_format:Y-m',
        ]);

        $batch = $request->batch_id
            ? Batch::where('institute_id', Auth::user()->institute_id)->findOrFail($request->batch_id)
            : Batch::where('institute_id', Auth::user()->institute_id)
                ->where('project_id', $this->getProjectId())
                ->orderBy('name')
                ->first();

        $month = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $rows = collect();
        $sessionCount = 0;

        if ($batch) {
            $sessions = AttendanceSession::where('batch_id', $batch->id)
                ->whereBetween('session_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->pluck('id');

            $sessionCount = $sessions->count();

            $tallies = Attendance::whereIn('attendance_session_id', $sessions)
                ->selectRaw('student_id, status, count(*) as total')
                ->groupBy('student_id', 'status')
                ->get()
                ->groupBy('student_id')
                ->map(fn ($group) => $group->pluck('total', 'status'));

            $rows = Student::where('batch_id', $batch->id)
                ->orderBy('name_en')
                ->get()
                ->map(function (Student $student) use ($tallies, $sessionCount) {
                    $counts = $tallies->get($student->id, collect());
                    $present = (int) $counts->get(Attendance::STATUS_PRESENT, 0);
                    $late = (int) $counts->get(Attendance::STATUS_LATE, 0);
                    $absent = (int) $counts->get(Attendance::STATUS_ABSENT, 0);
                    $marked = $present + $late + $absent;

                    return [
                        'student' => $student,
                        'present' => $present,
                        'late' => $late,
                        'absent' => $absent,
                        'unmarked' => max($sessionCount - $marked, 0),
                        'percentage' => $sessionCount > 0
                            ? round((($present + $late) / $sessionCount) * 100, 2)
                            : 0.0,
                    ];
                });
        }

        return [$batch, $month, $rows, $sessionCount];
    }

    /**
     * Aggregate attendance per batch for the whole project.
     */
    private function projectReportData(Request $request): array
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $month = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $batches = Batch::with('course')
            ->where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->orderBy('name')
            ->get()
            ->map(function (Batch $batch) use ($month) {
                $sessions = AttendanceSession::where('batch_id', $batch->id)
                    ->whereBetween('session_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->pluck('id');

                $counts = $sessions->isEmpty()
                    ? collect()
                    : Attendance::whereIn('attendance_session_id', $sessions)
                        ->selectRaw('status, count(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status');

                $present = (int) $counts->get(Attendance::STATUS_PRESENT, 0);
                $late = (int) $counts->get(Attendance::STATUS_LATE, 0);
                $absent = (int) $counts->get(Attendance::STATUS_ABSENT, 0);
                $marked = $present + $late + $absent;

                return [
                    'batch' => $batch,
                    'sessions' => $sessions->count(),
                    'students' => Student::where('batch_id', $batch->id)->count(),
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent,
                    'percentage' => $marked > 0 ? round((($present + $late) / $marked) * 100, 2) : 0.0,
                ];
            });

        return [$month, $batches];
    }

    private function reportBatches(): Collection
    {
        return Batch::where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->orderBy('name')
            ->get();
    }

    private function scopedSession(AttendanceSession $attendanceSession): AttendanceSession
    {
        abort_if($attendanceSession->institute_id !== Auth::user()->institute_id, 403);

        return $attendanceSession;
    }

    private function sessionForToken(string $token): AttendanceSession
    {
        $session = AttendanceSession::where('qr_token', $token)->firstOrFail();

        abort_unless($session->isOpen(), 403, 'This attendance session is closed.');
        abort_unless(
            $session->qr_expires_at === null || $session->qr_expires_at->isFuture(),
            403,
            'This QR code has expired.'
        );

        return $session;
    }

    private function csv(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
