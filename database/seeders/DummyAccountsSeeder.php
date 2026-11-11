<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\SalarySheet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds six months of demo income/expense activity plus payroll (draft for the
 * current month, paid for the previous two months). Safe to run repeatedly.
 */
class DummyAccountsSeeder extends Seeder
{
    /**
     * @var array<int, array{0: string, 1: float, 2: int}>
     */
    private array $incomePlan = [
        ['Course Fees', 320000, 3],
        ['Admission Fees', 48000, 7],
        ['Exam Fees', 18000, 12],
    ];

    /**
     * @var array<int, array{0: string, 1: float, 2: int}>
     */
    private array $expensePlan = [
        ['Rent', 25000, 1],
        ['Utilities', 6500, 4],
        ['Internet & Phone', 3200, 5],
        ['Marketing', 12000, 6],
        ['Training Materials', 28000, 8],
        ['Transport', 7500, 9],
        ['Office Supplies', 4200, 10],
        ['Refreshments', 3500, 11],
        ['Equipment Maintenance', 6000, 13],
    ];

    /**
     * @var array<int, array{0: string, 1: float, 2: string}>
     */
    private array $dailyPlan = [
        ['Transport', 350, 'CNG fare for admission follow-up'],
        ['Refreshments', 620, 'Tea and snacks for staff meeting'],
        ['Office Supplies', 480, 'Printer paper and files'],
        ['Transport', 500, 'Courier delivery of certificates'],
        ['Equipment Maintenance', 750, 'Chair repair in classroom'],
        ['Internet & Phone', 300, 'Mobile recharge for office phone'],
        ['Refreshments', 450, 'Refreshments for guardian meeting'],
        ['Miscellaneous', 250, 'Miscellaneous daily expense'],
    ];

    public function run(): void
    {
        $instituteId = User::query()->whereNotNull('institute_id')->value('institute_id')
            ?? Institute::query()->value('id');

        if (! $instituteId) {
            $this->command?->error('No institute found. Seed an institute/user first.');

            return;
        }

        $transactions = $this->seedMonthlyTransactions($instituteId);
        $daily = $this->seedDailyExpenses($instituteId);
        [$salarySheets, $salaryTransactions] = $this->seedPayroll($instituteId);

        $this->command?->info(sprintf(
            'Dummy accounts ready: %d monthly ledger entries, %d daily expenses, %d salary sheets, %d salary transactions.',
            $transactions,
            $daily,
            $salarySheets,
            $salaryTransactions
        ));
    }

    private function seedMonthlyTransactions(int $instituteId): int
    {
        $created = 0;

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonthsNoOverflow($i);
            $stamp = $month->format('Y-m');

            foreach ($this->incomePlan as $index => [$category, $base, $day]) {
                $receipt = 'DEMO-INC-' . $stamp . '-' . ($index + 1);

                $transaction = Transaction::updateOrCreate(
                    ['receipt_number' => $receipt],
                    [
                        'institute_id' => $instituteId,
                        'type' => 'income',
                        'category' => $category,
                        'amount' => $base + (5 - $i) * 4000,
                        'transaction_date' => $this->safeDate($month, $day)->toDateString(),
                        'payment_method' => 'Cash',
                        'description' => 'Demo ' . strtolower($category) . ' — ' . $month->format('M Y'),
                    ]
                );

                if ($transaction->wasRecentlyCreated) {
                    $created++;
                }
            }

            foreach ($this->expensePlan as $index => [$category, $base, $day]) {
                $receipt = 'DEMO-EXP-' . $stamp . '-' . ($index + 1);

                $transaction = Transaction::firstOrCreate(
                    ['receipt_number' => $receipt],
                    [
                        'institute_id' => $instituteId,
                        'type' => 'expense',
                        'category' => $category,
                        'amount' => $base + (5 - $i) * 400,
                        'transaction_date' => $this->safeDate($month, $day)->toDateString(),
                        'payment_method' => 'Cash',
                        'description' => 'Demo ' . strtolower($category) . ' — ' . $month->format('M Y'),
                    ]
                );

                if ($transaction->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        return $created;
    }

    private function seedDailyExpenses(int $instituteId): int
    {
        $created = 0;
        $stamp = now()->format('Y-m');
        $monthStart = now()->copy()->startOfMonth();
        $count = count($this->dailyPlan);

        foreach ($this->dailyPlan as $index => [$category, $amount, $note]) {
            $date = now()->copy()->subDays($count - $index - 1)->startOfDay();
            if ($date->lt($monthStart)) {
                $date = $monthStart->copy();
            }

            $receipt = 'DEMO-DAY-' . $stamp . '-' . ($index + 1);

            $transaction = Transaction::firstOrCreate(
                ['receipt_number' => $receipt],
                [
                    'institute_id' => $instituteId,
                    'type' => 'expense',
                    'category' => $category,
                    'amount' => $amount,
                    'transaction_date' => $date->toDateString(),
                    'payment_method' => $index % 2 === 0 ? 'Cash' : 'bKash',
                    'description' => $note,
                ]
            );

            if ($transaction->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function seedPayroll(int $instituteId): array
    {
        $staff = User::query()
            ->where('institute_id', $instituteId)
            ->orderBy('id')
            ->get();

        if ($staff->isEmpty()) {
            return [0, 0];
        }

        $createdSheets = 0;
        $createdTransactions = 0;

        foreach ($staff as $user) {
            $base = match ($user->role) {
                'admin' => 45000,
                'trainer' => 25000,
                'accountant' => 30000,
                default => 22000,
            };

            $basic = (float) ($user->monthly_salary ?: ($base + ($user->id % 3) * 1500));

            if (! $user->designation || ! $user->monthly_salary) {
                $user->forceFill([
                    'designation' => $user->designation ?: ucfirst((string) ($user->role ?: 'staff')),
                    'monthly_salary' => $basic,
                ])->save();
            }

            for ($i = 2; $i >= 0; $i--) {
                $month = now()->copy()->subMonthsNoOverflow($i);
                $monthLabel = $month->format('Y-m');
                $isCurrent = $i === 0;
                $paidOn = $isCurrent ? null : $month->copy()->endOfMonth()->toDateString();

                $sheet = SalarySheet::firstOrCreate(
                    [
                        'institute_id' => $instituteId,
                        'user_id' => $user->id,
                        'month' => $monthLabel,
                    ],
                    [
                        'basic_salary' => $basic,
                        'allowance' => 3000,
                        'deduction' => 500,
                        'status' => $isCurrent ? SalarySheet::STATUS_DRAFT : SalarySheet::STATUS_PAID,
                        'paid_on' => $paidOn,
                        'notes' => 'Demo payroll for ' . $month->format('F Y'),
                    ]
                );

                if ($sheet->wasRecentlyCreated) {
                    $createdSheets++;
                }

                if ($isCurrent || $sheet->transaction_id) {
                    continue;
                }

                $receipt = 'DEMO-SAL-' . $monthLabel . '-' . $user->id;
                $transaction = Transaction::firstOrCreate(
                    ['receipt_number' => $receipt],
                    [
                        'institute_id' => $instituteId,
                        'type' => 'expense',
                        'category' => config('accounts.salary_category', 'Salary'),
                        'amount' => $sheet->net_salary,
                        'transaction_date' => $paidOn ?? now()->toDateString(),
                        'payment_method' => 'Bank',
                        'description' => 'Salary — ' . $user->name . ' (' . $monthLabel . ')',
                    ]
                );

                if ($transaction->wasRecentlyCreated) {
                    $createdTransactions++;
                }

                if (! $sheet->transaction_id) {
                    $sheet->update([
                        'status' => SalarySheet::STATUS_PAID,
                        'paid_on' => $paidOn,
                        'transaction_id' => $transaction->id,
                    ]);
                }
            }
        }

        return [$createdSheets, $createdTransactions];
    }

    private function safeDate(Carbon $month, int $day): Carbon
    {
        $date = $month->copy()->startOfMonth()->addDays(max(0, $day - 1));

        if ($date->gt($month->copy()->endOfMonth())) {
            $date = $month->copy()->endOfMonth();
        }

        if ($date->gt(now())) {
            $date = now()->copy();
        }

        return $date;
    }
}
