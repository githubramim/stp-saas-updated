<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\FeeAdjustment;
use App\Models\FeeDueReminder;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use App\Models\FeePlan;
use App\Models\Student;
use App\Models\StudentFeePlan;
use App\Services\FeeReminderService;
use App\Services\FeeService;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeeController extends Controller
{
    public function __construct(
        private readonly FeeService $fees,
        private readonly FeeReminderService $reminders
    ) {}

    public function dashboard()
    {
        $this->authorizeFinance();
        $instituteId = (int) Auth::user()->institute_id;

        $plans = StudentFeePlan::where('institute_id', $instituteId);
        $gross = (float) (clone $plans)->sum('gross_amount');
        $net = (float) (clone $plans)->sum('net_amount');
        $collected = (float) FeePayment::where('institute_id', $instituteId)->sum('amount');

        $stats = [
            'total_plans' => (clone $plans)->count(),
            'active_plans' => (clone $plans)->where('status', StudentFeePlan::STATUS_ACTIVE)->count(),
            'gross_amount' => $gross,
            'net_amount' => $net,
            'collected' => $collected,
            'outstanding' => max(0, $net - $collected),
            'overdue_count' => FeeInstallment::where('institute_id', $instituteId)
                ->whereIn('status', [
                    FeeInstallment::STATUS_UNPAID,
                    FeeInstallment::STATUS_PARTIAL,
                    FeeInstallment::STATUS_OVERDUE,
                ])
                ->whereColumn('paid_amount', '<', 'amount')
                ->whereDate('due_date', '<', today())
                ->count(),
        ];

        $dueInstallments = FeeInstallment::with(['student', 'studentFeePlan'])
            ->where('institute_id', $instituteId)
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereNotIn('status', [FeeInstallment::STATUS_PAID, FeeInstallment::STATUS_WAIVED])
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $recentPayments = FeePayment::with(['student', 'installment'])
            ->where('institute_id', $instituteId)
            ->latest('payment_date')
            ->take(8)
            ->get();

        $reminders = FeeDueReminder::with(['student', 'installment'])
            ->where('institute_id', $instituteId)
            ->latest('due_date')
            ->take(5)
            ->get();

        return view('fees.dashboard', compact('stats', 'dueInstallments', 'recentPayments', 'reminders'));
    }

    public function feePlans()
    {
        $this->authorizeFinance();
        $instituteId = (int) Auth::user()->institute_id;

        $plans = FeePlan::with(['course', 'batch'])
            ->where('institute_id', $instituteId)
            ->latest()
            ->paginate(15);

        $courses = $this->visibleCourses($instituteId);
        $batches = Batch::where('institute_id', $instituteId)->orderBy('name')->get();

        return view('fees.plans', compact('plans', 'courses', 'batches'));
    }

    public function storeFeePlan(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'duration_months' => ['required', 'in:3,6,12'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->fees->createPlan($data, (int) Auth::user()->institute_id, $this->branchId());

        return back()->with('success', 'Fee plan created successfully.');
    }

    public function studentPlans()
    {
        $this->authorizeFinance();
        $instituteId = (int) Auth::user()->institute_id;

        $plans = StudentFeePlan::with(['student', 'feePlan', 'installments'])
            ->where('institute_id', $instituteId)
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(request('plan_months'), fn ($q, $months) => $q->where('plan_months', $months))
            ->latest()
            ->paginate(15);

        $students = Student::where('institute_id', $instituteId)
            ->whereIn('status', ['admitted', 'active', 'applied'])
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'reference_no', 'status']);

        $feePlans = FeePlan::where('institute_id', $instituteId)
            ->where('status', FeePlan::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('fees.students', compact('plans', 'students', 'feePlans'));
    }

    public function storeStudentPlan(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_plan_id' => ['nullable', 'exists:fee_plans,id'],
            'plan_months' => ['required', 'in:3,6,12'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'scholarship_amount' => ['nullable', 'numeric', 'min:0'],
            'waiver_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
            'scholarship_reason' => ['nullable', 'string', 'max:255'],
            'waiver_reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $plan = $this->fees->createStudentPlan(
            $data,
            (int) Auth::user()->institute_id,
            $this->branchId(),
            (int) Auth::id()
        );

        return redirect()->route('fees.students.show', $plan)
            ->with('success', 'Student fee plan and installment schedule created.');
    }

    public function showStudentPlan(StudentFeePlan $studentFeePlan)
    {
        $this->authorizeFinance();
        $this->assertBranchContext($studentFeePlan);

        $plan = $studentFeePlan->load([
            'student', 'feePlan', 'course', 'batch', 'adjustments', 'installments.payments', 'payments',
        ]);

        return view('fees.student-show', compact('plan'));
    }

    public function installments()
    {
        $this->authorizeFinance();

        $installments = FeeInstallment::with(['student', 'studentFeePlan'])
            ->where('institute_id', (int) Auth::user()->institute_id)
            ->when(request('status'), function ($q, $status) {
                if (in_array($status, FeeInstallment::STATUSES, true)) {
                    $q->where('status', $status);
                }
            })
            ->when(request('plan_months'), function ($q, $months) {
                if (in_array((int) $months, StudentFeePlan::MONTHS, true)) {
                    $q->whereHas('studentFeePlan', fn ($sq) => $sq->where('plan_months', $months));
                }
            })
            ->orderBy('due_date')
            ->paginate(20);

        $statuses = FeeInstallment::STATUSES;

        return view('fees.installments', compact('installments', 'statuses'));
    }

    public function storePayment(Request $request, FeeInstallment $feeInstallment)
    {
        $this->authorizeFinance();
        $this->assertBranchContext($feeInstallment);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
        ]);

        $this->fees->recordPayment(
            $feeInstallment,
            $data,
            (int) Auth::user()->institute_id,
            $this->branchId(),
            (int) Auth::id()
        );

        return back()->with('success', 'Fee payment recorded and posted to the income ledger.');
    }

    public function adjustments()
    {
        $this->authorizeFinance();
        $instituteId = (int) Auth::user()->institute_id;

        $adjustments = FeeAdjustment::with(['studentFeePlan.student'])
            ->where('institute_id', $instituteId)
            ->when(request('type'), function ($q, $type) {
                if (in_array($type, FeeAdjustment::TYPES, true)) {
                    $q->where('type', $type);
                }
            })
            ->latest()
            ->paginate(20);

        $plans = StudentFeePlan::with('student')
            ->where('institute_id', $instituteId)
            ->where('status', '!=', StudentFeePlan::STATUS_CANCELLED)
            ->orderBy('code')
            ->get();

        return view('fees.adjustments', compact('adjustments', 'plans'));
    }

    public function storeAdjustment(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'student_fee_plan_id' => ['required', 'exists:student_fee_plans,id'],
            'type' => ['required', 'in:discount,scholarship,waiver'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $plan = StudentFeePlan::withoutGlobalScope('branch')
            ->where('institute_id', (int) Auth::user()->institute_id)
            ->findOrFail($data['student_fee_plan_id']);

        $this->assertBranchContext($plan);

        $this->fees->addAdjustment(
            $plan,
            $data,
            (int) Auth::user()->institute_id,
            $this->branchId(),
            (int) Auth::id()
        );

        return back()->with('success', 'Adjustment applied and installments re-amortized.');
    }

    public function destroyAdjustment(FeeAdjustment $feeAdjustment)
    {
        $this->authorizeFinance();
        $this->assertBranchContext($feeAdjustment);

        $this->fees->removeAdjustment($feeAdjustment);

        return back()->with('success', 'Adjustment removed and installment balances updated.');
    }

    public function reminders()
    {
        $this->authorizeFinance();
        $instituteId = (int) Auth::user()->institute_id;

        $reminders = FeeDueReminder::with(['student', 'installment'])
            ->where('institute_id', $instituteId)
            ->when(request('channel'), function ($q, $channel) {
                if (in_array($channel, FeeDueReminder::CHANNELS, true)) {
                    $q->where('channel', $channel);
                }
            })
            ->when(request('status'), function ($q, $status) {
                if (in_array($status, FeeDueReminder::STATUSES, true)) {
                    $q->where('status', $status);
                }
            })
            ->latest('due_date')
            ->paginate(20);

        $stats = [
            'due_count' => FeeInstallment::where('institute_id', $instituteId)
                ->whereColumn('paid_amount', '<', 'amount')
                ->whereDate('due_date', '<=', today())
                ->count(),
            'sms_sent' => FeeDueReminder::where('institute_id', $instituteId)
                ->where('channel', FeeDueReminder::CHANNEL_SMS)
                ->where('status', FeeDueReminder::STATUS_SENT)
                ->count(),
            'failed_count' => FeeDueReminder::where('institute_id', $instituteId)
                ->where('status', FeeDueReminder::STATUS_FAILED)
                ->count(),
        ];

        $channels = FeeDueReminder::CHANNELS;
        $statuses = FeeDueReminder::STATUSES;

        return view('fees.reminders', compact('reminders', 'stats', 'channels', 'statuses'));
    }

    public function sendReminders(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'days' => ['required', 'integer', 'min:0', 'max:30'],
        ]);

        $result = $this->reminders->sendDueReminders(
            (int) $data['days'],
            (int) Auth::user()->institute_id,
            $this->branchId()
        );

        return back()->with(
            'success',
            sprintf(
                'Due reminder run complete: %d checked, %d SMS sent, %d failed, %d skipped.',
                $result['total'],
                $result['sent'],
                $result['failed'],
                $result['skipped']
            )
        );
    }

    private function authorizeFinance(): void
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Fee Management.');
    }

    private function branchId(): int
    {
        return BranchContext::scopeId() ?: (int) (BranchContext::current(Auth::user())?->id ?? 0);
    }

    /**
     * Institute-wide course catalog plus project courses that belong to the
     * selected branch. Paid courses with no project are intentionally included.
     */
    private function visibleCourses(int $instituteId)
    {
        return Course::withoutGlobalScope('branch')
            ->where('institute_id', $instituteId)
            ->where(function ($q) {
                $q->whereNull('project_id')
                    ->orWhereHas('project', fn ($pq) => $pq->where('branch_id', $this->branchId()));
            })
            ->orderBy('name')
            ->get();
    }
}
