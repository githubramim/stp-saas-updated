<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeProfile;
use App\Models\InternalEvaluation;
use App\Models\SalarySheet;
use App\Models\User;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * HR & Employee Management (BRD module #22).
 *
 * Manages trainers and staff: joining date and salary structure, leave
 * balance, employee attendance, payroll and trainer performance ratings.
 * Every query is restricted to the branch selected in the top bar.
 */
class HRController extends Controller
{
    private function guard(): void
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to HR & Employee Management.');
        abort_if(! Auth::user()->institute_id, 403, 'No institute context.');
    }

    private function instituteId(): int
    {
        return (int) Auth::user()->institute_id;
    }

    private function branchId(): int
    {
        return BranchContext::scopeId();
    }

    /**
     * Staff of the selected branch only.
     */
    private function staff()
    {
        return User::query()
            ->where('institute_id', $this->instituteId())
            ->where('branch_id', $this->branchId())
            ->orderBy('name');
    }

    private function profileFor(User $user): EmployeeProfile
    {
        return EmployeeProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'institute_id' => $user->institute_id,
                'employee_code' => 'EMP-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                'joining_date' => $user->created_at?->toDateString(),
                'basic_salary' => $user->monthly_salary ?? 0,
                'status' => EmployeeProfile::STATUS_ACTIVE,
            ]
        );
    }

    private function normalizeMonth(?string $month): string
    {
        return is_string($month) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)
            ? $month
            : now()->format('Y-m');
    }

    private function monthDates(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $dates = [];

        for ($day = $start->copy(); $day->month === $start->month; $day->addDay()) {
            $dates[] = $day->toDateString();
        }

        return $dates;
    }

    /**
     * Working days in a month (Friday is the weekly holiday).
     */
    private function workingDays(string $month): int
    {
        return collect($this->monthDates($month))
            ->filter(fn (string $date) => (int) date('N', strtotime($date)) !== 5)
            ->count();
    }

    /**
     * Attendance tally for one staff member in a month.
     *
     * @return array<string, float>
     */
    private function attendanceSummary(int $userId, string $month): array
    {
        $rows = EmployeeAttendance::where('user_id', $userId)
            ->whereBetween('date', [
                Carbon::createFromFormat('Y-m', $month)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $month)->endOfMonth(),
            ])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'present' => (float) ($rows[EmployeeAttendance::STATUS_PRESENT] ?? 0),
            'late' => (float) ($rows[EmployeeAttendance::STATUS_LATE] ?? 0),
            'half_day' => (float) ($rows[EmployeeAttendance::STATUS_HALF_DAY] ?? 0),
            'absent' => (float) ($rows[EmployeeAttendance::STATUS_ABSENT] ?? 0),
            'leave' => (float) ($rows[EmployeeAttendance::STATUS_LEAVE] ?? 0),
            'total' => (float) $rows->sum(),
        ];
    }

    // ------------------------------------------------------------------ Dashboard

    public function dashboard()
    {
        $this->guard();

        $staff = $this->staff()->get();
        $userIds = $staff->pluck('id');

        foreach ($staff as $user) {
            $this->profileFor($user);
        }

        $month = now()->format('Y-m');
        $today = now()->toDateString();

        $todayRows = EmployeeAttendance::whereIn('user_id', $userIds)
            ->where('date', $today)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $payrollTotal = (float) SalarySheet::where('institute_id', $this->instituteId())
            ->where('month', $month)
            ->whereIn('user_id', $userIds)
            ->sum('net_salary');

        $pendingLeaves = EmployeeLeave::with('user')
            ->where('institute_id', $this->instituteId())
            ->where('status', EmployeeLeave::STATUS_PENDING)
            ->whereIn('user_id', $userIds)
            ->latest()
            ->limit(5)
            ->get();

        $weekly = collect(range(6, 0))
            ->map(fn (int $daysAgo) => now()->subDays($daysAgo)->toDateString())
            ->map(function (string $date) use ($userIds) {
                $counts = EmployeeAttendance::whereIn('user_id', $userIds)
                    ->where('date', $date)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                return [
                    'label' => Carbon::parse($date)->format('D d'),
                    'present' => (int) (($counts['present'] ?? 0) + ($counts['late'] ?? 0)),
                    'absent' => (int) ($counts['absent'] ?? 0),
                ];
            });

        $topPerformers = $staff->where('role', 'trainer')
            ->map(function (User $trainer) {
                $profile = $this->profileFor($trainer);
                $ratings = InternalEvaluation::where('trainer_id', $trainer->id)
                    ->where('institute_id', $this->instituteId());

                $studentRating = (float) (clone $ratings)->avg('rating_score');

                return [
                    'user' => $trainer,
                    'student_rating' => $studentRating,
                    'rating_count' => (clone $ratings)->count(),
                    'admin_rating' => $profile->performance_rating ? (float) $profile->performance_rating : null,
                    'official_rating' => $profile->performance_rating ? (float) $profile->performance_rating : $studentRating,
                ];
            })
            ->sortByDesc('official_rating')
            ->take(5);

        return view('hr.dashboard', compact(
            'staff', 'month', 'todayRows', 'payrollTotal', 'pendingLeaves', 'weekly', 'topPerformers'
        ));
    }

    // ------------------------------------------------------------------ Employees

    public function employees(Request $request)
    {
        $this->guard();

        $query = $this->staff();
        $search = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', $term)
                    ->orWhere('email', 'ilike', $term)
                    ->orWhere('designation', 'ilike', $term)
                    ->orWhere('phone', 'ilike', $term);
            });
        }

        if (in_array($role, ['admin', 'trainer', 'staff'], true)) {
            $query->where('role', $role);
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $ids = EmployeeProfile::where('status', $status)->pluck('user_id');
            $query->whereIn('id', $ids);
        }

        $employees = $query->get()->map(function (User $user) {
            $profile = $this->profileFor($user);
            $summary = $this->attendanceSummary($user->id, now()->format('Y-m'));

            return [
                'user' => $user,
                'profile' => $profile,
                'leave_balance' => $profile->leaveBalance((int) now()->year),
                'present_days' => $summary['present'] + $summary['late'],
            ];
        });

        return view('hr.employees', compact(
            'employees', 'search', 'role', 'status'
        ));
    }

    public function createEmployee()
    {
        $this->guard();

        $user = new User;
        $profile = new EmployeeProfile(['annual_leave_entitlement' => 12, 'employment_type' => 'full_time', 'status' => 'active']);
        $branches = Branch::where('institute_id', $this->instituteId())->orderBy('id')->get();

        return view('hr.employee-form', compact('user', 'profile', 'branches'));
    }

    private function employeeRules(User $user): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'in:admin,trainer,staff'],
            'designation' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('institute_id', $this->instituteId())],
            'password' => [$user->exists ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'employee_code' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['required', 'date'],
            'employment_type' => ['required', 'in:'.implode(',', EmployeeProfile::EMPLOYMENT_TYPES)],
            'department' => ['nullable', 'string', 'max:100'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'house_allowance' => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowance' => ['nullable', 'numeric', 'min:0'],
            'annual_leave_entitlement' => ['required', 'integer', 'min:0', 'max:365'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
            'nid' => ['nullable', 'string', 'max:50'],
            'performance_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'performance_notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function storeEmployee(Request $request)
    {
        $this->guard();
        $data = $request->validate($this->employeeRules(new User));

        $profileData = $this->profileFields($data);
        $gross = $profileData['basic_salary'] + $profileData['house_allowance']
            + $profileData['transport_allowance'] + $profileData['other_allowance'];

        $user = new User;
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'institute_id' => $this->instituteId(),
            'branch_id' => (int) $data['branch_id'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'designation' => $data['designation'] ?? null,
            'monthly_salary' => $gross,
        ])->save();

        EmployeeProfile::create(array_merge($profileData, [
            'institute_id' => $this->instituteId(),
            'user_id' => $user->id,
        ]));

        return redirect()->route('hr.employees')
            ->with('success', $user->name.' has been added to HR.');
    }

    public function editEmployee(User $user)
    {
        $this->guard();
        $this->assertBranchUser($user);

        $profile = $this->profileFor($user);
        $branches = Branch::where('institute_id', $this->instituteId())->orderBy('id')->get();

        return view('hr.employee-form', compact('user', 'profile', 'branches'));
    }

    public function updateEmployee(Request $request, User $user)
    {
        $this->guard();
        $this->assertBranchUser($user);
        $data = $request->validate($this->employeeRules($user));

        $profileData = $this->profileFields($data);
        $gross = $profileData['basic_salary'] + $profileData['house_allowance']
            + $profileData['transport_allowance'] + $profileData['other_allowance'];

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'institute_id' => $this->instituteId(),
            'branch_id' => (int) $data['branch_id'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'designation' => $data['designation'] ?? null,
            'monthly_salary' => $gross,
        ]);

        if (! empty($data['password'])) {
            $user->forceFill(['password' => Hash::make($data['password'])]);
        }

        $user->save();
        $this->profileFor($user)->update($profileData);

        return redirect()->route('hr.employees')
            ->with('success', 'Employee record updated.');
    }

    public function toggleStatus(User $user)
    {
        $this->guard();
        $this->assertBranchUser($user);

        $profile = $this->profileFor($user);
        $profile->update([
            'status' => $profile->status === EmployeeProfile::STATUS_ACTIVE
                ? EmployeeProfile::STATUS_INACTIVE
                : EmployeeProfile::STATUS_ACTIVE,
        ]);

        return back()->with('success', $user->name.' is now '.ucfirst($profile->status).'.');
    }

    private function profileFields(array $data): array
    {
        return [
            'employee_code' => $data['employee_code'] ?? null,
            'joining_date' => $data['joining_date'],
            'employment_type' => $data['employment_type'],
            'department' => $data['department'] ?? null,
            'basic_salary' => $data['basic_salary'],
            'house_allowance' => $data['house_allowance'] ?? 0,
            'transport_allowance' => $data['transport_allowance'] ?? 0,
            'other_allowance' => $data['other_allowance'] ?? 0,
            'annual_leave_entitlement' => $data['annual_leave_entitlement'],
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'nid' => $data['nid'] ?? null,
            'performance_rating' => $data['performance_rating'] ?? null,
            'performance_notes' => $data['performance_notes'] ?? null,
            'status' => $data['status'],
        ];
    }

    private function assertBranchUser(User $user): void
    {
        abort_if(
            (int) $user->institute_id !== $this->instituteId() || (int) $user->branch_id !== $this->branchId(),
            404
        );
    }

    // ------------------------------------------------------------------ Attendance

    public function attendance(Request $request)
    {
        $this->guard();

        $month = $this->normalizeMonth($request->query('month'));
        $date = (string) $request->query('date', now()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = now()->toDateString();
        }

        $staff = $this->staff()->get();
        $userIds = $staff->pluck('id');

        $marks = EmployeeAttendance::whereIn('user_id', $userIds)
            ->where('date', $date)
            ->pluck('status', 'user_id');

        $summaries = EmployeeAttendance::whereIn('user_id', $userIds)
            ->whereBetween('date', [
                Carbon::createFromFormat('Y-m', $month)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $month)->endOfMonth(),
            ])
            ->selectRaw('user_id, status, count(*) as total')
            ->groupBy('user_id', 'status')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('total', 'status'));

        return view('hr.attendance', compact('staff', 'month', 'date', 'marks', 'summaries'));
    }

    public function storeAttendance(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:'.implode(',', array_keys(EmployeeAttendance::STATUSES))],
        ]);

        $allowedIds = $this->staff()->pluck('id');

        DB::transaction(function () use ($data, $allowedIds) {
            foreach ($data['statuses'] as $userId => $status) {
                if (! $allowedIds->contains((int) $userId)) {
                    continue;
                }

                EmployeeAttendance::updateOrCreate(
                    ['user_id' => (int) $userId, 'date' => $data['date']],
                    [
                        'institute_id' => $this->instituteId(),
                        'status' => $status,
                        'marked_by' => Auth::id(),
                    ]
                );
            }
        });

        return back()->with('success', 'Employee attendance saved for '.Carbon::parse($data['date'])->format('d M Y').'.');
    }

    // ------------------------------------------------------------------ Leave

    public function leaves(Request $request)
    {
        $this->guard();

        $status = (string) $request->query('status', 'all');
        $staff = $this->staff()->get();
        $userIds = $staff->pluck('id');

        $leaves = EmployeeLeave::with('user')
            ->where('institute_id', $this->instituteId())
            ->whereIn('user_id', $userIds)
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->orderByRaw("case status when 'pending' then 0 when 'approved' then 1 else 2 end")
            ->latest()
            ->get();

        $staff = $staff->map(function (User $user) {
            return ['user' => $user, 'profile' => $this->profileFor($user)];
        });

        return view('hr.leaves', compact('leaves', 'staff', 'status'));
    }

    public function storeLeave(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'leave_type' => ['required', 'in:'.implode(',', array_keys(EmployeeLeave::TYPES))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($this->staff()->pluck('id')->contains((int) $data['user_id']), 404);

        $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;

        EmployeeLeave::create([
            'institute_id' => $this->instituteId(),
            'user_id' => (int) $data['user_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => EmployeeLeave::STATUS_PENDING,
        ]);

        return back()->with('success', 'Leave request recorded and is pending approval.');
    }

    public function updateLeaveStatus(Request $request, EmployeeLeave $leave)
    {
        $this->guard();
        abort_unless((int) $leave->institute_id === $this->instituteId(), 404);
        abort_unless($this->staff()->pluck('id')->contains((int) $leave->user_id), 404);

        $data = $request->validate(['status' => ['required', 'in:approved,rejected']]);

        $leave->update([
            'status' => $data['status'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave request '.$data['status'].'.');
    }

    // ------------------------------------------------------------------ Payroll

    public function payroll(Request $request)
    {
        $this->guard();

        $month = $this->normalizeMonth($request->query('month'));
        $workingDays = $this->workingDays($month);

        $sheets = SalarySheet::where('institute_id', $this->instituteId())
            ->where('month', $month)
            ->whereIn('user_id', $this->staff()->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $rows = $this->staff()->get()->map(function (User $user) use ($month, $workingDays, $sheets) {
            $profile = $this->profileFor($user);
            $summary = $this->attendanceSummary($user->id, $month);
            $paidLeave = $this->paidLeaveDays($user->id, $month);

            $payableDays = $summary['present'] + $summary['late'] + ($summary['half_day'] * 0.5) + $paidLeave;
            // Only deduct when attendance was actually marked; a fresh month
            // with no entries should not silently zero out everyone's salary.
            $deductionDays = $summary['total'] > 0 ? max(0, $workingDays - $payableDays) : 0;
            $gross = $profile->grossSalary();
            $deduction = $workingDays > 0 ? round(($gross / $workingDays) * $deductionDays, 2) : 0;

            return [
                'user' => $user,
                'profile' => $profile,
                'summary' => $summary,
                'paid_leave' => $paidLeave,
                'gross' => $gross,
                'deduction' => $deduction,
                'net' => round($gross - $deduction, 2),
                'sheet' => $sheets->get($user->id),
            ];
        });

        $totals = [
            'gross' => $rows->sum('gross'),
            'deduction' => $rows->sum('deduction'),
            'net' => $rows->sum('net'),
            'paid' => $sheets->where('status', SalarySheet::STATUS_PAID)->sum('net_salary'),
        ];

        return view('hr.payroll', compact('month', 'workingDays', 'rows', 'totals'));
    }

    private function paidLeaveDays(int $userId, string $month): float
    {
        return (float) EmployeeLeave::where('user_id', $userId)
            ->where('status', EmployeeLeave::STATUS_APPROVED)
            ->whereMonth('start_date', Carbon::createFromFormat('Y-m', $month)->month)
            ->whereYear('start_date', Carbon::createFromFormat('Y-m', $month)->year)
            ->sum('days');
    }

    public function generatePayroll(Request $request)
    {
        $this->guard();

        $data = $request->validate(['month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/']]);
        $month = $data['month'];
        $workingDays = $this->workingDays($month);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($month, $workingDays, &$created, &$updated, &$skipped) {
            foreach ($this->staff()->get() as $user) {
                $profile = $this->profileFor($user);

                $sheet = SalarySheet::firstOrNew([
                    'institute_id' => $this->instituteId(),
                    'user_id' => $user->id,
                    'month' => $month,
                ]);

                if ($sheet->exists && $sheet->status === SalarySheet::STATUS_PAID) {
                    $skipped++;

                    continue;
                }

                $wasExisting = $sheet->exists;
                $summary = $this->attendanceSummary($user->id, $month);
                $paidLeave = $this->paidLeaveDays($user->id, $month);

                $payableDays = $summary['present'] + $summary['late'] + ($summary['half_day'] * 0.5) + $paidLeave;
                $deductionDays = $summary['total'] > 0 ? max(0, $workingDays - $payableDays) : 0;
                $gross = $profile->grossSalary();
                $deduction = $workingDays > 0 ? round(($gross / $workingDays) * $deductionDays, 2) : 0;

                $sheet->fill([
                    'basic_salary' => $profile->basic_salary,
                    'allowance' => $profile->totalAllowance(),
                    'deduction' => $deduction,
                    'status' => SalarySheet::STATUS_DRAFT,
                ])->save();

                $user->forceFill(['monthly_salary' => $gross])->save();

                $wasExisting ? $updated++ : $created++;
            }
        });

        return redirect()->route('hr.payroll', ['month' => $month])
            ->with('success', "Payroll generated: {$created} created, {$updated} updated, {$skipped} paid sheet(s) left untouched.");
    }

    // ------------------------------------------------------------------ Performance

    public function performance()
    {
        $this->guard();

        $month = now()->format('Y-m');
        $batchCounts = Batch::where('institute_id', $this->instituteId())
            ->whereNotNull('trainer_id')
            ->selectRaw('trainer_id, count(*) as total')
            ->groupBy('trainer_id')
            ->pluck('total', 'trainer_id');

        $trainers = $this->staff()->where('role', 'trainer')->get()->map(function (User $trainer) use ($month, $batchCounts) {
            $profile = $this->profileFor($trainer);
            $ratings = InternalEvaluation::where('trainer_id', $trainer->id)
                ->where('institute_id', $this->instituteId());

            $studentRating = (float) (clone $ratings)->avg('rating_score');
            $ratingCount = (clone $ratings)->count();
            $adminRating = $profile->performance_rating ? (float) $profile->performance_rating : null;
            $score = $adminRating ?? $studentRating;
            $summary = $this->attendanceSummary($trainer->id, $month);
            $marked = $summary['total'];
            $attendanceRate = $marked > 0
                ? round((($summary['present'] + $summary['late'] + ($summary['half_day'] * 0.5)) / $marked) * 100, 1)
                : null;

            return [
                'user' => $trainer,
                'profile' => $profile,
                'student_rating' => $studentRating,
                'rating_count' => $ratingCount,
                'admin_rating' => $adminRating,
                'score' => $score,
                'label' => $score >= 4.5 ? 'Excellent' : ($score >= 3.5 ? 'Good' : ($score >= 2.5 ? 'Average' : ($score > 0 ? 'Needs Improvement' : 'Not Rated'))),
                'batches' => (int) ($batchCounts[$trainer->id] ?? 0),
                'attendance_rate' => $attendanceRate,
            ];
        })->sortByDesc('score');

        return view('hr.performance', compact('trainers', 'month'));
    }
}
