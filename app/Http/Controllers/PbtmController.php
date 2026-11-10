<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\JobPlacement;
use App\Models\Project;
use App\Models\ProjectAsset;
use App\Models\Stipend;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PbtmController extends Controller
{
    private function guard(): void
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Project-Based Training Management.');
    }

    private function instituteId(): int
    {
        return (int) Auth::user()->institute_id;
    }

    private function projects()
    {
        return Project::where('institute_id', $this->instituteId())->orderBy('name')->get();
    }

    /**
     * Resolve the active project from the query string, falling back to the
     * globally selected project context in the session.
     */
    private function resolveProject(Request $request): ?Project
    {
        if ($request->has('project_id')) {
            $projectId = $request->integer('project_id');
        } else {
            $projectId = session('current_project_id');
        }

        if (!$projectId) {
            return null;
        }

        return Project::where('institute_id', $this->instituteId())->find($projectId);
    }

    private function scopedStudents(?Project $project): Builder
    {
        return Student::query()
            ->where('institute_id', $this->instituteId())
            ->when($project, fn (Builder $q) => $q->where('project_id', $project->id));
    }

    private function scopedStipends(?Project $project): Builder
    {
        return Stipend::query()
            ->where('institute_id', $this->instituteId())
            ->when($project, fn (Builder $q) => $q->where('project_id', $project->id));
    }

    private function scopedAssets(?Project $project): Builder
    {
        return ProjectAsset::query()
            ->where('institute_id', $this->instituteId())
            ->when($project, fn (Builder $q) => $q->where('project_id', $project->id));
    }

    private function scopedPlacements(?Project $project): Builder
    {
        return JobPlacement::query()
            ->where('institute_id', $this->instituteId())
            ->when($project, fn (Builder $q) => $q->where('project_id', $project->id));
    }

    public function index(Request $request)
    {
        $this->guard();

        $projects = $this->projects();
        $project = $this->resolveProject($request);

        $students = $this->scopedStudents($project);
        $stipends = $this->scopedStipends($project);
        $assets = $this->scopedAssets($project);
        $placements = $this->scopedPlacements($project);

        $stats = [
            'trainees' => (clone $students)->count(),
            'applied' => (clone $students)->where('status', 'applied')->count(),
            'shortlisted' => (clone $students)->where('status', 'shortlisted')->count(),
            'selected' => (clone $students)->whereIn('status', ['selected', 'admitted', 'active'])->count(),
            'completed' => (clone $students)->where('status', 'completed')->count(),
            'stipend_paid' => (float) (clone $stipends)->where('status', Stipend::STATUS_PAID)->sum('amount'),
            'stipend_pending' => (float) (clone $stipends)->where('status', Stipend::STATUS_PENDING)->sum('amount'),
            'assets_total' => (clone $assets)->sum('quantity'),
            'assets_out' => (clone $assets)->where('status', ProjectAsset::STATUS_DISTRIBUTED)->sum('quantity'),
            'placements' => (clone $placements)->where('status', '!=', JobPlacement::STATUS_DROPPED)->count(),
        ];

        $stats['placement_rate'] = $stats['completed'] > 0
            ? round(($stats['placements'] / max($stats['completed'], 1)) * 100, 1)
            : 0.0;

        $recentStipends = (clone $stipends)->with('student')->latest()->take(5)->get();
        $recentPlacements = (clone $placements)->with('student')->latest()->take(5)->get();

        return view('pbtm.index', compact('projects', 'project', 'stats', 'recentStipends', 'recentPlacements'));
    }

    public function trainees(Request $request)
    {
        $this->guard();

        $projects = $this->projects();
        $project = $this->resolveProject($request);

        $query = $this->scopedStudents($project)
            ->with(['course', 'batch', 'project'])
            ->orderBy('name_en');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_bn', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        $students = $query->get();
        $statuses = $this->traineeStatuses();

        return view('pbtm.trainees', compact('projects', 'project', 'students', 'statuses'));
    }

    public function updateTraineeStatus(Request $request, Student $student)
    {
        $this->guard();
        abort_if($student->institute_id !== $this->instituteId(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->traineeStatuses()))],
        ]);

        $student->update(['status' => $validated['status']]);

        return back()->with('success', "{$student->name_en} moved to " . $this->traineeStatuses()[$validated['status']] . '.');
    }

    private function traineeStatuses(): array
    {
        return [
            'applied' => 'Applied',
            'shortlisted' => 'Shortlisted',
            'selected' => 'Selected',
            'admitted' => 'Admitted',
            'active' => 'Active',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'dropped_out' => 'Dropped Out',
        ];
    }

    public function stipends(Request $request)
    {
        $this->guard();

        $projects = $this->projects();
        $project = $this->resolveProject($request);

        $query = $this->scopedStipends($project)->with(['student', 'project'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $stipends = $query->get();

        $totals = [
            'paid' => (float) $this->scopedStipends($project)->where('status', Stipend::STATUS_PAID)->sum('amount'),
            'pending' => (float) $this->scopedStipends($project)->where('status', Stipend::STATUS_PENDING)->sum('amount'),
            'count' => $this->scopedStipends($project)->count(),
        ];

        $students = $this->scopedStudents($project)
            ->whereIn('status', ['selected', 'admitted', 'active', 'completed'])
            ->orderBy('name_en')
            ->get();

        $types = Stipend::types();
        $statuses = Stipend::statuses();

        return view('pbtm.stipends', compact('projects', 'project', 'stipends', 'totals', 'students', 'types', 'statuses'));
    }

    public function storeStipend(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'type' => ['required', Rule::in(array_keys(Stipend::types()))],
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_no' => ['nullable', 'string', 'max:100'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(Stipend::statuses()))],
            'remarks' => ['nullable', 'string'],
        ]);

        $student = Student::where('institute_id', $this->instituteId())->findOrFail($validated['student_id']);
        $projectId = $validated['project_id'] ?? $student->project_id;

        if ($projectId) {
            abort_unless(Project::where('institute_id', $this->instituteId())->whereKey($projectId)->exists(), 403);
        }

        if ($validated['status'] === Stipend::STATUS_PAID && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        Stipend::create(array_merge($validated, [
            'institute_id' => $this->instituteId(),
            'project_id' => $projectId,
            'student_id' => $student->id,
        ]));

        return redirect()->route('pbtm.stipends', array_filter(['project_id' => $request->project_id]))
            ->with('success', 'Stipend record saved successfully.');
    }

    public function payStipend(Request $request, Stipend $stipend)
    {
        $this->guard();
        abort_if($stipend->institute_id !== $this->instituteId(), 403);

        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
        ]);

        $stipend->update([
            'status' => Stipend::STATUS_PAID,
            'payment_date' => now()->toDateString(),
            'payment_method' => $validated['payment_method'] ?: $stipend->payment_method,
            'transaction_ref' => $validated['transaction_ref'] ?: $stipend->transaction_ref,
        ]);

        return back()->with('success', 'Stipend marked as paid.');
    }

    public function destroyStipend(Stipend $stipend)
    {
        $this->guard();
        abort_if($stipend->institute_id !== $this->instituteId(), 403);

        $stipend->delete();

        return back()->with('success', 'Stipend record deleted.');
    }

    public function assets(Request $request)
    {
        $this->guard();

        $projects = $this->projects();
        $project = $this->resolveProject($request);

        $query = $this->scopedAssets($project)->with(['student', 'project'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->get();

        $totals = [
            'total' => (int) $this->scopedAssets($project)->sum('quantity'),
            'distributed' => (int) $this->scopedAssets($project)->where('status', ProjectAsset::STATUS_DISTRIBUTED)->sum('quantity'),
            'returned' => (int) $this->scopedAssets($project)->where('status', ProjectAsset::STATUS_RETURNED)->sum('quantity'),
            'value' => (float) $this->scopedAssets($project)->get()->sum(fn (ProjectAsset $asset) => (float) $asset->unit_cost * $asset->quantity),
        ];

        $students = $this->scopedStudents($project)
            ->whereIn('status', ['selected', 'admitted', 'active', 'completed'])
            ->orderBy('name_en')
            ->get();

        $statuses = ProjectAsset::statuses();

        return view('pbtm.assets', compact('projects', 'project', 'assets', 'totals', 'students', 'statuses'));
    }

    public function storeAsset(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'asset_code' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'distribution_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(ProjectAsset::statuses()))],
            'condition' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ]);

        $projectId = $validated['project_id'] ?? ($request->integer('project_id') ?: null);

        if (!empty($validated['student_id'])) {
            $student = Student::where('institute_id', $this->instituteId())->findOrFail($validated['student_id']);
            $projectId = $projectId ?: $student->project_id;
        }

        if ($projectId) {
            abort_unless(Project::where('institute_id', $this->instituteId())->whereKey($projectId)->exists(), 403);
        }

        if ($validated['status'] === ProjectAsset::STATUS_DISTRIBUTED && empty($validated['distribution_date'])) {
            $validated['distribution_date'] = now()->toDateString();
        }

        ProjectAsset::create(array_merge($validated, [
            'institute_id' => $this->instituteId(),
            'project_id' => $projectId,
        ]));

        return redirect()->route('pbtm.assets', array_filter(['project_id' => $request->project_id]))
            ->with('success', 'Asset record saved successfully.');
    }

    public function returnAsset(Request $request, ProjectAsset $asset)
    {
        $this->guard();
        abort_if($asset->institute_id !== $this->instituteId(), 403);

        $validated = $request->validate([
            'condition' => ['nullable', 'string', 'max:100'],
        ]);

        $asset->update([
            'status' => ProjectAsset::STATUS_RETURNED,
            'return_date' => now()->toDateString(),
            'condition' => $validated['condition'] ?: $asset->condition,
        ]);

        return back()->with('success', 'Asset marked as returned.');
    }

    public function destroyAsset(ProjectAsset $asset)
    {
        $this->guard();
        abort_if($asset->institute_id !== $this->instituteId(), 403);

        $asset->delete();

        return back()->with('success', 'Asset record deleted.');
    }

    public function placements(Request $request)
    {
        $this->guard();

        $projects = $this->projects();
        $project = $this->resolveProject($request);

        $query = $this->scopedPlacements($project)->with(['student', 'project'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $placements = $query->get();

        $totals = [
            'placed' => $this->scopedPlacements($project)->whereIn('status', [JobPlacement::STATUS_PLACED, JobPlacement::STATUS_RETAINED])->count(),
            'dropped' => $this->scopedPlacements($project)->where('status', JobPlacement::STATUS_DROPPED)->count(),
            'avg_salary' => (float) $this->scopedPlacements($project)->avg('monthly_salary'),
        ];

        $students = $this->scopedStudents($project)
            ->whereIn('status', ['admitted', 'active', 'completed', 'selected'])
            ->orderBy('name_en')
            ->get();

        $statuses = JobPlacement::statuses();
        $employmentTypes = JobPlacement::employmentTypes();

        return view('pbtm.placements', compact('projects', 'project', 'placements', 'totals', 'students', 'statuses', 'employmentTypes'));
    }

    public function storePlacement(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'employer_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', Rule::in(array_keys(JobPlacement::employmentTypes()))],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'placement_date' => ['nullable', 'date'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(array_keys(JobPlacement::statuses()))],
            'remarks' => ['nullable', 'string'],
        ]);

        $student = Student::where('institute_id', $this->instituteId())->findOrFail($validated['student_id']);
        $projectId = $validated['project_id'] ?? $student->project_id;

        if ($projectId) {
            abort_unless(Project::where('institute_id', $this->instituteId())->whereKey($projectId)->exists(), 403);
        }

        JobPlacement::create(array_merge($validated, [
            'institute_id' => $this->instituteId(),
            'project_id' => $projectId,
            'student_id' => $student->id,
            'placement_date' => $validated['placement_date'] ?? now()->toDateString(),
        ]));

        return redirect()->route('pbtm.placements', array_filter(['project_id' => $request->project_id]))
            ->with('success', 'Job placement saved successfully.');
    }

    public function destroyPlacement(JobPlacement $placement)
    {
        $this->guard();
        abort_if($placement->institute_id !== $this->instituteId(), 403);

        $placement->delete();

        return back()->with('success', 'Job placement deleted.');
    }

    public function reports(Request $request)
    {
        $this->guard();

        $projects = $this->projects();
        $project = $this->resolveProject($request);
        $year = (int) ($request->integer('year') ?: now()->year);
        $view = $request->get('view') === 'quarterly' ? 'quarterly' : 'monthly';

        $report = $this->buildReport($project, $year, $view);

        return view('pbtm.reports', array_merge(
            compact('projects', 'project', 'year', 'view'),
            $report
        ));
    }

    public function reportCsv(Request $request)
    {
        $this->guard();

        $project = $this->resolveProject($request);
        $year = (int) ($request->integer('year') ?: now()->year);
        $view = $request->get('view') === 'quarterly' ? 'quarterly' : 'monthly';
        $report = $this->buildReport($project, $year, $view);

        $filename = 'pbtm-progress-' . ($project?->code ?: 'all-projects') . '-' . $year . '-' . $view . '.csv';

        return response()->streamDownload(function () use ($project, $year, $view, $report) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Project-Based Training Management — NSDA/SEIP Progress Report']);
            fputcsv($handle, ['Project', $project?->name ?? 'All Projects']);
            fputcsv($handle, ['Donor', $project?->donor_name ?? '']);
            fputcsv($handle, ['MoU No', $project?->mou_number ?? '']);
            fputcsv($handle, ['Budget (BDT)', $project?->budget_amount ?? '']);
            fputcsv($handle, ['Report Year', $year]);
            fputcsv($handle, ['Reporting Frequency', ucfirst($view)]);
            fputcsv($handle, []);

            fputcsv($handle, [$view === 'quarterly' ? 'Quarter' : 'Month', 'New Enrolments', 'Selected/Admitted', 'Stipend Paid (BDT)', 'Attendance %', 'Job Placements']);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['enrolled'],
                    $row['selected'],
                    number_format($row['stipend_paid'], 2, '.', ''),
                    $row['attendance'],
                    $row['placements'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Total', $report['totals']['enrolled'], $report['totals']['selected'], number_format($report['totals']['stipend_paid'], 2, '.', ''), $report['totals']['attendance'], $report['totals']['placements']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Trainees', $report['summary']['trainees']]);
            fputcsv($handle, ['Completed', $report['summary']['completed']]);
            fputcsv($handle, ['Placed', $report['summary']['placed']]);
            fputcsv($handle, ['Assets Distributed', $report['summary']['assets_distributed']]);
            fputcsv($handle, ['Total Budget (BDT)', $project?->budget_amount ?? 0]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildReport(?Project $project, int $year, string $view = 'monthly'): array
    {
        $rows = [];
        $enrolledTotal = 0;
        $selectedTotal = 0;
        $stipendTotal = 0.0;
        $presentTotal = 0;
        $attendanceTotal = 0;
        $placementTotal = 0;

        for ($month = 1; $month <= 12; $month++) {
            $start = now()->setDate($year, $month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            $enrolled = $this->scopedStudents($project)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $selected = $this->scopedStudents($project)
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['selected', 'admitted', 'active', 'completed'])
                ->count();

            $stipendPaid = (float) $this->scopedStipends($project)
                ->where('status', Stipend::STATUS_PAID)
                ->whereBetween('payment_date', [$start, $end])
                ->sum('amount');

            $placements = $this->scopedPlacements($project)
                ->whereBetween('placement_date', [$start, $end])
                ->where('status', '!=', JobPlacement::STATUS_DROPPED)
                ->count();

            $attendance = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->where('attendances.institute_id', $this->instituteId())
                ->when($project, fn ($q) => $q->where('students.project_id', $project->id))
                ->whereBetween('attendance_sessions.session_date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw("count(*) as total, sum(case when attendances.status in ('present', 'late') then 1 else 0 end) as present")
                ->first();

            $monthAttendanceTotal = (int) ($attendance->total ?? 0);
            $monthAttendancePresent = (int) ($attendance->present ?? 0);

            $enrolledTotal += $enrolled;
            $selectedTotal += $selected;
            $stipendTotal += $stipendPaid;
            $presentTotal += $monthAttendancePresent;
            $attendanceTotal += $monthAttendanceTotal;
            $placementTotal += $placements;

            $rows[] = [
                'month' => $month,
                'label' => $start->format('M Y'),
                'short' => $start->format('M'),
                'enrolled' => $enrolled,
                'selected' => $selected,
                'stipend_paid' => $stipendPaid,
                'attendance' => $monthAttendanceTotal > 0
                    ? round(($monthAttendancePresent / $monthAttendanceTotal) * 100, 1)
                    : 0,
                'present' => $monthAttendancePresent,
                'attendance_total' => $monthAttendanceTotal,
                'placements' => $placements,
            ];
        }

        if ($view === 'quarterly') {
            $quarterly = [];
            foreach (array_chunk($rows, 3) as $index => $months) {
                $present = array_sum(array_column($months, 'present'));
                $attendanceTotalForQuarter = array_sum(array_column($months, 'attendance_total'));
                $quarterly[] = [
                    'month' => $index + 1,
                    'label' => 'Q' . ($index + 1) . ' (' . $months[0]['short'] . '–' . $months[count($months) - 1]['short'] . ')',
                    'short' => 'Q' . ($index + 1),
                    'enrolled' => array_sum(array_column($months, 'enrolled')),
                    'selected' => array_sum(array_column($months, 'selected')),
                    'stipend_paid' => array_sum(array_column($months, 'stipend_paid')),
                    'attendance' => $attendanceTotalForQuarter > 0
                        ? round(($present / $attendanceTotalForQuarter) * 100, 1)
                        : 0,
                    'present' => $present,
                    'attendance_total' => $attendanceTotalForQuarter,
                    'placements' => array_sum(array_column($months, 'placements')),
                ];
            }
            $rows = $quarterly;
        }

        $summary = [
            'trainees' => $this->scopedStudents($project)->count(),
            'completed' => $this->scopedStudents($project)->where('status', 'completed')->count(),
            'placed' => $this->scopedPlacements($project)->whereIn('status', [JobPlacement::STATUS_PLACED, JobPlacement::STATUS_RETAINED])->count(),
            'assets_distributed' => (int) $this->scopedAssets($project)->where('status', ProjectAsset::STATUS_DISTRIBUTED)->sum('quantity'),
            'stipend_paid' => (float) $this->scopedStipends($project)->where('status', Stipend::STATUS_PAID)->sum('amount'),
        ];

        return [
            'rows' => $rows,
            'totals' => [
                'enrolled' => $enrolledTotal,
                'selected' => $selectedTotal,
                'stipend_paid' => $stipendTotal,
                'attendance' => $attendanceTotal > 0 ? round(($presentTotal / $attendanceTotal) * 100, 1) : 0,
                'placements' => $placementTotal,
            ],
            'summary' => $summary,
        ];
    }
}
