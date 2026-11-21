<?php

namespace App\Services;

use App\Models\FeeAdjustment;
use App\Models\FeeDueReminder;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use App\Models\FeePlan;
use App\Models\Student;
use App\Models\StudentFeePlan;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class FeeService
{
    /**
     * Create a reusable course/batch fee template.
     */
    public function createPlan(array $data, int $instituteId, int $branchId): FeePlan
    {
        if (! in_array((int) $data['duration_months'], FeePlan::MONTHS, true)) {
            throw new InvalidArgumentException('Installment plan must be 3, 6, or 12 months.');
        }

        return FeePlan::create([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'course_id' => $data['course_id'] ?: null,
            'batch_id' => $data['batch_id'] ?: null,
            'name' => $data['name'],
            'code' => $data['code'] ?: null,
            'duration_months' => (int) $data['duration_months'],
            'total_amount' => (float) $data['total_amount'],
            'description' => $data['description'] ?: null,
            'status' => $data['status'] ?? FeePlan::STATUS_ACTIVE,
        ]);
    }

    /**
     * Create a per-student fee plan, its adjustment ledger, and installment schedule.
     */
    public function createStudentPlan(array $data, int $instituteId, int $branchId, ?int $userId = null): StudentFeePlan
    {
        $months = (int) $data['plan_months'];
        if (! in_array($months, StudentFeePlan::MONTHS, true)) {
            throw new InvalidArgumentException('Installment plan must be 3, 6, or 12 months.');
        }

        $student = Student::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->findOrFail($data['student_id']);

        $template = ! empty($data['fee_plan_id'])
            ? FeePlan::where('institute_id', $instituteId)->where('branch_id', $branchId)->find($data['fee_plan_id'])
            : null;

        return DB::transaction(function () use ($data, $student, $template, $months, $instituteId, $branchId, $userId) {
            $plan = StudentFeePlan::create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'student_id' => $student->id,
                'fee_plan_id' => $template?->id,
                'course_id' => $template?->course_id ?? $student->course_id,
                'batch_id' => $template?->batch_id ?? $student->batch_id,
                'code' => $this->generatePlanCode(),
                'plan_months' => $months,
                'gross_amount' => (float) $data['gross_amount'],
                'discount_amount' => 0,
                'scholarship_amount' => 0,
                'waiver_amount' => 0,
                'net_amount' => (float) $data['gross_amount'],
                'start_date' => $data['start_date'],
                'status' => StudentFeePlan::STATUS_ACTIVE,
                'notes' => $data['notes'] ?: null,
            ]);

            foreach (FeeAdjustment::TYPES as $type) {
                $amount = round((float) ($data[$type.'_amount'] ?? 0), 2);

                if ($amount <= 0) {
                    continue;
                }

                FeeAdjustment::create([
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'student_fee_plan_id' => $plan->id,
                    'type' => $type,
                    'amount' => $amount,
                    'reason' => $data[$type.'_reason'] ?: ucfirst($type),
                    'reference' => $data[$type.'_reference'] ?? null,
                    'created_by' => $userId,
                ]);
            }

            $plan->recalculateAmounts();
            $this->generateInstallments($plan);

            return $plan->load(['student', 'installments', 'adjustments']);
        });
    }

    /**
     * Rebuild the installment schedule for a newly created plan.
     */
    public function generateInstallments(StudentFeePlan $plan): void
    {
        $plan->installments()->delete();

        $months = max(1, (int) $plan->plan_months);
        $total = round((float) $plan->net_amount, 2);
        $base = floor(($total / $months) * 100) / 100;
        $remainder = round($total - ($base * $months), 2);
        $start = Carbon::parse($plan->start_date);

        for ($sequence = 1; $sequence <= $months; $sequence++) {
            $amount = $sequence === $months
                ? round($base + $remainder, 2)
                : $base;

            FeeInstallment::create([
                'institute_id' => $plan->institute_id,
                'branch_id' => $plan->branch_id,
                'student_fee_plan_id' => $plan->id,
                'student_id' => $plan->student_id,
                'sequence' => $sequence,
                'due_date' => $start->copy()->addMonthsNoOverflow($sequence - 1),
                'amount' => $amount,
                'paid_amount' => 0,
                'status' => $amount <= 0 ? FeeInstallment::STATUS_WAIVED : FeeInstallment::STATUS_UNPAID,
            ]);
        }
    }

    /**
     * Add a discount, scholarship, or waiver and re-amortize the unpaid balance.
     */
    public function addAdjustment(StudentFeePlan $plan, array $data, int $instituteId, int $branchId, ?int $userId = null): FeeAdjustment
    {
        if (! in_array($data['type'], FeeAdjustment::TYPES, true)) {
            throw new InvalidArgumentException('Invalid adjustment type.');
        }

        $adjustment = DB::transaction(function () use ($plan, $data, $instituteId, $branchId, $userId) {
            $adjustment = FeeAdjustment::create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'student_fee_plan_id' => $plan->id,
                'type' => $data['type'],
                'amount' => (float) $data['amount'],
                'reason' => $data['reason'],
                'reference' => $data['reference'] ?: null,
                'created_by' => $userId,
            ]);

            $plan->recalculateAmounts();
            $this->rebalanceInstallments($plan);

            return $adjustment;
        });

        return $adjustment;
    }

    /**
     * Remove an adjustment and restore the re-amortized balance.
     */
    public function removeAdjustment(FeeAdjustment $adjustment): void
    {
        $plan = $adjustment->studentFeePlan;

        DB::transaction(function () use ($adjustment, $plan) {
            $adjustment->delete();
            $plan->recalculateAmounts();
            $this->rebalanceInstallments($plan);
        });
    }

    /**
     * Spread the outstanding balance over installments that are not closed yet.
     * Already paid amounts are never reduced.
     */
    public function rebalanceInstallments(StudentFeePlan $plan): void
    {
        $installments = $plan->installments()->orderBy('sequence')->get();
        $totalPaid = (float) $installments->sum('paid_amount');
        $remaining = max(0, round((float) $plan->net_amount - $totalPaid, 2));

        $open = $installments->filter(
            fn (FeeInstallment $installment) => ! in_array($installment->status, [
                FeeInstallment::STATUS_PAID, FeeInstallment::STATUS_WAIVED,
            ], true)
        )->values();

        if ($open->isEmpty()) {
            return;
        }

        $share = round($remaining / $open->count(), 2);
        $allocated = 0.0;

        foreach ($open as $index => $installment) {
            $portion = $index === $open->count() - 1
                ? round($remaining - $allocated, 2)
                : $share;

            $paid = (float) $installment->paid_amount;
            $installment->amount = round(max($paid, $paid + $portion), 2);

            if ($installment->amount <= $paid && $paid <= 0) {
                $installment->status = FeeInstallment::STATUS_WAIVED;
                $installment->save();
            } else {
                $installment->syncStatus();
            }

            $allocated += $portion;
        }
    }

    /**
     * Record a payment, post it to the income ledger, and update the installment.
     */
    public function recordPayment(
        FeeInstallment $installment,
        array $data,
        int $instituteId,
        int $branchId,
        ?int $userId = null
    ): FeePayment {
        $amount = round((float) $data['amount'], 2);

        return DB::transaction(function () use ($installment, $data, $amount, $instituteId, $branchId, $userId) {
            $locked = FeeInstallment::withoutGlobalScope('branch')
                ->whereKey($installment->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless((int) $locked->institute_id === $instituteId, 404);
            abort_unless((int) $locked->branch_id === $branchId, 404);

            $balance = (float) $locked->balance;

            if ($amount <= 0 || $amount > $balance + 0.01) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero and cannot exceed the installment balance (৳'.number_format($balance, 2).').',
                ]);
            }

            $receipt = 'RCP-'.strtoupper(Str::random(10));
            $description = 'Fee payment — '.($locked->studentFeePlan?->code ?? 'student plan')
                .' installment #'.$locked->sequence;

            $transaction = Transaction::create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'student_id' => $locked->student_id,
                'batch_id' => $locked->studentFeePlan?->batch_id,
                'type' => 'income',
                'category' => 'Course Fees',
                'amount' => $amount,
                'transaction_date' => $data['payment_date'],
                'receipt_number' => $receipt,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'description' => $description,
            ]);

            $payment = FeePayment::create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'fee_installment_id' => $locked->id,
                'student_fee_plan_id' => $locked->student_fee_plan_id,
                'student_id' => $locked->student_id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'reference_no' => $data['reference_no'] ?: null,
                'receipt_number' => $receipt,
                'note' => $data['note'] ?: null,
                'created_by' => $userId,
            ]);

            $locked->paid_amount = round((float) $locked->paid_amount + $amount, 2);
            $locked->syncStatus();
            $this->syncPlanStatus($locked->studentFeePlan);

            return $payment;
        });
    }

    public function syncPlanStatus(StudentFeePlan $plan): void
    {
        if ($plan->status === StudentFeePlan::STATUS_CANCELLED) {
            return;
        }

        $plan->status = $plan->due <= 0.01
            ? StudentFeePlan::STATUS_COMPLETED
            : StudentFeePlan::STATUS_ACTIVE;
        $plan->save();
    }

    /**
     * Public/portal fee payload. Never relies on the branch scope because a
     * staff member may be logged into the same browser as the student portal.
     */
    public function portalSummary(Student $student): array
    {
        $plans = StudentFeePlan::withoutGlobalScope('branch')
            ->where('student_id', $student->id)
            ->where('institute_id', $student->institute_id)
            // The portal is public; a staff member may be logged into the same
            // browser with a branch scope active. Eager-loaded installments
            // must bypass that scope too, or the fee table appears empty.
            ->with(['installments' => fn ($q) => $q->withoutGlobalScope('branch')])
            ->get();

        $installments = $plans->flatMap->installments
            ->sortBy(fn (FeeInstallment $installment) => [$installment->due_date, $installment->sequence])
            ->values();

        $gross = (float) $plans->sum('gross_amount');
        $net = (float) $plans->sum('net_amount');
        $paid = (float) $installments->sum('paid_amount');
        $due = max(0, round($net - $paid, 2));
        $overdue = (float) $installments->filter(fn (FeeInstallment $i) => $i->is_due)->sum('balance');

        $reminders = FeeDueReminder::withoutGlobalScope('branch')
            ->where('student_id', $student->id)
            ->where('status', FeeDueReminder::STATUS_SENT)
            ->latest('due_date')
            ->take(5)
            ->get();

        return [
            'feePlans' => $plans,
            'feeInstallments' => $installments,
            'feeSummary' => [
                'gross' => $gross,
                'net' => $net,
                'paid' => $paid,
                'due' => $due,
                'overdue' => $overdue,
            ],
            'nextInstallment' => $installments->first(fn (FeeInstallment $i) => $i->balance > 0),
            'feeReminders' => $reminders,
        ];
    }

    private function generatePlanCode(): string
    {
        do {
            $code = 'SFP-'.date('Y').'-'.strtoupper(Str::random(6));
        } while (StudentFeePlan::withoutGlobalScope('branch')->where('code', $code)->exists());

        return $code;
    }
}
