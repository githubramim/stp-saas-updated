<?php

namespace App\Services;

use App\Models\FeeDueReminder;
use App\Models\FeeInstallment;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class FeeReminderService
{
    public function __construct(private readonly SmsService $sms) {}

    /**
     * Generate and dispatch due reminders. Console commands have no branch
     * scope, so branch/institute filters are applied explicitly here.
     *
     * @return array{sent:int, failed:int, skipped:int, total:int}
     */
    public function sendDueReminders(int $days = 0, ?int $instituteId = null, ?int $branchId = null): array
    {
        $cutoff = Carbon::today()->addDays(max(0, $days))->endOfDay();

        $installments = FeeInstallment::withoutGlobalScope('branch')
            ->with([
                'student' => fn ($q) => $q->withoutGlobalScope('branch')->with('institute'),
                'studentFeePlan',
            ])
            ->whereIn('status', [
                FeeInstallment::STATUS_UNPAID,
                FeeInstallment::STATUS_PARTIAL,
                FeeInstallment::STATUS_OVERDUE,
            ])
            ->whereDate('due_date', '<=', $cutoff)
            ->when($instituteId, fn ($q) => $q->where('institute_id', $instituteId))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'total' => $installments->count()];

        foreach ($installments as $installment) {
            $student = $installment->student;

            if (! $student instanceof Student || $installment->balance <= 0) {
                $result['skipped']++;

                continue;
            }

            $message = $this->messageFor($installment, $student);

            // The portal channel is "sent" as soon as the due entry is visible.
            $this->log($installment, FeeDueReminder::CHANNEL_PORTAL, FeeDueReminder::STATUS_SENT, $message, 'Due entry displayed in student portal.', now());

            $phone = $student->contact ?: $student->guardian_contact;

            if (! $phone) {
                $this->log($installment, FeeDueReminder::CHANNEL_SMS, FeeDueReminder::STATUS_FAILED, $message, 'No phone number on student record.');
                $result['failed']++;

                continue;
            }

            try {
                $response = $this->sms->send($phone, $message);
                $status = ($response['status'] ?? 'failed') === 'sent'
                    ? FeeDueReminder::STATUS_SENT
                    : FeeDueReminder::STATUS_FAILED;

                $this->log(
                    $installment,
                    FeeDueReminder::CHANNEL_SMS,
                    $status,
                    $message,
                    $response['response'] ?? json_encode($response),
                    $status === FeeDueReminder::STATUS_SENT ? now() : null
                );

                $status === FeeDueReminder::STATUS_SENT
                    ? $result['sent']++
                    : $result['failed']++;
            } catch (\Throwable $e) {
                Log::warning('Fee due SMS failed', [
                    'installment_id' => $installment->id,
                    'error' => $e->getMessage(),
                ]);

                $this->log($installment, FeeDueReminder::CHANNEL_SMS, FeeDueReminder::STATUS_FAILED, $message, $e->getMessage());
                $result['failed']++;
            }
        }

        return $result;
    }

    public function sendForInstallment(FeeInstallment $installment): array
    {
        $installment->loadMissing([
            'student' => fn ($q) => $q->withoutGlobalScope('branch')->with('institute'),
            'studentFeePlan',
        ]);

        $student = $installment->student;
        $message = $this->messageFor($installment, $student);
        $phone = $student->contact ?: $student->guardian_contact;

        if (! $phone) {
            $this->log($installment, FeeDueReminder::CHANNEL_SMS, FeeDueReminder::STATUS_FAILED, $message, 'No phone number on student record.');

            return ['sent' => 0, 'failed' => 1, 'message' => 'No phone number on student record.'];
        }

        $response = $this->sms->send($phone, $message);
        $sent = ($response['status'] ?? 'failed') === 'sent';

        $this->log(
            $installment,
            FeeDueReminder::CHANNEL_SMS,
            $sent ? FeeDueReminder::STATUS_SENT : FeeDueReminder::STATUS_FAILED,
            $message,
            $response['response'] ?? json_encode($response),
            $sent ? now() : null
        );

        return [
            'sent' => $sent ? 1 : 0,
            'failed' => $sent ? 0 : 1,
            'message' => $response['response'] ?? ($sent ? 'SMS sent.' : 'SMS failed.'),
        ];
    }

    private function messageFor(FeeInstallment $installment, Student $student): string
    {
        $institute = $student->institute?->name ?: 'your institute';
        $plan = $installment->studentFeePlan;

        return sprintf(
            'Fee reminder: Dear %s, installment #%d for %s (%s) of ৳%s is due on %s. Paid ৳%s, balance ৳%s. Please clear your dues at the student portal. — %s',
            $student->name_en ?: $student->name,
            $installment->sequence,
            $plan?->code ?? 'your fee plan',
            $plan?->plan_months ? $plan->plan_months.'-month plan' : 'fee plan',
            number_format((float) $installment->amount, 2),
            $installment->due_date?->format('d M Y') ?? 'soon',
            number_format((float) $installment->paid_amount, 2),
            number_format((float) $installment->balance, 2),
            $institute
        );
    }

    private function log(
        FeeInstallment $installment,
        string $channel,
        string $status,
        string $message,
        ?string $response = null,
        ?Carbon $sentAt = null
    ): FeeDueReminder {
        return FeeDueReminder::withoutGlobalScope('branch')->updateOrCreate(
            [
                'fee_installment_id' => $installment->id,
                'channel' => $channel,
                'due_date' => $installment->due_date,
            ],
            [
                'institute_id' => $installment->institute_id,
                'branch_id' => $installment->branch_id,
                'student_id' => $installment->student_id,
                'status' => $status,
                'message' => $message,
                'response' => $response,
                'sent_at' => $sentAt,
            ]
        );
    }
}
