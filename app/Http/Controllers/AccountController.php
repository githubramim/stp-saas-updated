<?php

namespace App\Http\Controllers;

use App\Models\SalarySheet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    private function guard(): void
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Accounts & Expense Management.');
    }

    private function instituteId(): int
    {
        return (int) Auth::user()->institute_id;
    }

    private function normalizeMonth(?string $month): string
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $month)
            ? $month
            : now()->format('Y-m');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function monthRange(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return [$start, $start->copy()->endOfMonth()];
    }

    private function staffQuery()
    {
        return User::query()
            ->where('institute_id', $this->instituteId())
            ->orderBy('name');
    }

    public function dashboard()
    {
        $this->guard();

        $instituteId = $this->instituteId();
        [$monthStart, $monthEnd] = $this->monthRange(now()->format('Y-m'));

        $monthIncome = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthExpense = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $todayExpense = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->whereDate('transaction_date', now()->toDateString())
            ->sum('amount');

        $totalIncome = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'income')->sum('amount');

        $totalExpense = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'expense')->sum('amount');

        $salaryQuery = SalarySheet::where('institute_id', $instituteId)
            ->forMonth(now()->format('Y-m'));

        $salaryDue = (float) (clone $salaryQuery)->where('status', SalarySheet::STATUS_DRAFT)->sum('net_salary');
        $salaryPaid = (float) (clone $salaryQuery)->where('status', SalarySheet::STATUS_PAID)->sum('net_salary');
        $pendingSalaryCount = (clone $salaryQuery)->where('status', SalarySheet::STATUS_DRAFT)->count();

        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $cursor = now()->copy()->subMonthsNoOverflow($i);
            [$start, $end] = $this->monthRange($cursor->format('Y-m'));

            $income = (float) Transaction::where('institute_id', $instituteId)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $expense = (float) Transaction::where('institute_id', $instituteId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $trend[] = [
                'label' => $cursor->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }

        $expenseByCategory = Transaction::query()
            ->where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as entries')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $recentTransactions = Transaction::with('student')
            ->where('institute_id', $instituteId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $recentSalaries = SalarySheet::with('user')
            ->where('institute_id', $instituteId)
            ->forMonth(now()->format('Y-m'))
            ->orderByDesc('net_salary')
            ->limit(6)
            ->get();

        return view('accounts.dashboard', compact(
            'monthIncome', 'monthExpense', 'todayExpense', 'totalIncome', 'totalExpense',
            'salaryDue', 'salaryPaid', 'pendingSalaryCount', 'trend',
            'expenseByCategory', 'recentTransactions', 'recentSalaries'
        ));
    }

    public function expenses(Request $request)
    {
        $this->guard();

        $instituteId = $this->instituteId();

        $query = Transaction::query()
            ->where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date_to))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('description', 'ilike', '%' . $request->q . '%')
                    ->orWhere('category', 'ilike', '%' . $request->q . '%');
            }));

        $filteredTotal = (float) (clone $query)->sum('amount');
        $filteredCount = (clone $query)->count();

        $expenses = $query->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $todayTotal = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->whereDate('transaction_date', now()->toDateString())
            ->sum('amount');

        [$monthStart, $monthEnd] = $this->monthRange(now()->format('Y-m'));

        $monthTotal = (float) Transaction::where('institute_id', $instituteId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $categories = collect(config('accounts.expense_categories', []))
            ->merge(Transaction::where('institute_id', $instituteId)
                ->where('type', 'expense')
                ->distinct()
                ->pluck('category'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('accounts.expenses', compact(
            'expenses', 'filteredTotal', 'filteredCount', 'todayTotal', 'monthTotal', 'categories'
        ));
    }

    public function storeExpense(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'custom_category' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = $data['category'];
        if (in_array($category, ['Others', 'Other Expense'], true) && filled($data['custom_category'] ?? null)) {
            $category = trim($data['custom_category']);
        }

        Transaction::create([
            'institute_id' => $this->instituteId(),
            'type' => 'expense',
            'category' => $category,
            'amount' => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('accounts.expenses')
            ->with('success', 'Daily expense recorded successfully.');
    }

    public function updateExpense(Request $request, Transaction $transaction)
    {
        $this->guard();
        abort_if($transaction->institute_id !== $this->instituteId(), 403);
        abort_if($transaction->type !== 'expense', 404);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'custom_category' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = $data['category'];
        if (in_array($category, ['Others', 'Other Expense'], true) && filled($data['custom_category'] ?? null)) {
            $category = trim($data['custom_category']);
        }

        $transaction->update([
            'category' => $category,
            'amount' => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('accounts.expenses')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroyExpense(Transaction $transaction)
    {
        $this->guard();
        abort_if($transaction->institute_id !== $this->instituteId(), 403);
        abort_if($transaction->type !== 'expense', 404);

        $transaction->delete();

        return redirect()->route('accounts.expenses')
            ->with('success', 'Expense deleted successfully.');
    }

    public function salaries(Request $request)
    {
        $this->guard();

        $instituteId = $this->instituteId();
        $month = $this->normalizeMonth($request->input('month'));

        $sheets = SalarySheet::with(['user', 'transaction'])
            ->where('institute_id', $instituteId)
            ->forMonth($month)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->get()
            ->sortBy(fn (SalarySheet $sheet) => strtolower($sheet->user?->name ?? ''))
            ->values();

        $staff = $this->staffQuery()->get();
        $existing = $sheets->keyBy('user_id');

        $lastBasics = SalarySheet::where('institute_id', $instituteId)
            ->orderByDesc('month')
            ->get(['user_id', 'basic_salary'])
            ->groupBy('user_id')
            ->map(fn ($rows) => (float) $rows->first()->basic_salary);

        $summary = [
            'count' => $sheets->count(),
            'total' => (float) $sheets->sum('net_salary'),
            'paid' => (float) $sheets->where('status', SalarySheet::STATUS_PAID)->sum('net_salary'),
            'paid_count' => $sheets->where('status', SalarySheet::STATUS_PAID)->count(),
            'due' => (float) $sheets->where('status', SalarySheet::STATUS_DRAFT)->sum('net_salary'),
            'due_count' => $sheets->where('status', SalarySheet::STATUS_DRAFT)->count(),
        ];

        $months = collect(range(0, 11))
            ->map(fn ($i) => now()->copy()->subMonthsNoOverflow($i)->format('Y-m'));

        return view('accounts.salaries', compact(
            'month', 'sheets', 'staff', 'existing', 'lastBasics', 'summary', 'months'
        ));
    }

    public function generateSalaries(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'basics' => ['nullable', 'array'],
            'basics.*' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'array'],
            'allowances.*' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'array'],
            'deductions.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $month = $data['month'];
        $instituteId = $this->instituteId();
        $staff = $this->staffQuery()->get();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($staff, $data, $month, $instituteId, &$created, &$updated, &$skipped) {
            foreach ($staff as $user) {
                $sheet = SalarySheet::firstOrNew([
                    'institute_id' => $instituteId,
                    'user_id' => $user->id,
                    'month' => $month,
                ]);

                if ($sheet->exists && $sheet->status === SalarySheet::STATUS_PAID) {
                    $skipped++;

                    continue;
                }

                $exists = $sheet->exists;

                $fallbackBasic = (float) ($sheet->basic_salary ?: $user->monthly_salary ?: 0);
                $basic = $this->numericInput($data['basics'][$user->id] ?? null, $fallbackBasic);
                $allowance = $this->numericInput($data['allowances'][$user->id] ?? null, (float) $sheet->allowance);
                $deduction = $this->numericInput($data['deductions'][$user->id] ?? null, (float) $sheet->deduction);

                $sheet->fill([
                    'basic_salary' => $basic,
                    'allowance' => $allowance,
                    'deduction' => $deduction,
                    'status' => SalarySheet::STATUS_DRAFT,
                ])->save();

                if ($basic > 0 && (float) $user->monthly_salary !== $basic) {
                    $user->forceFill(['monthly_salary' => $basic])->save();
                }

                $exists ? $updated++ : $created++;
            }
        });

        return redirect()->route('accounts.salaries', ['month' => $month])
            ->with('success', "Salary sheet generated: {$created} created, {$updated} updated, {$skipped} paid sheet(s) left untouched.");
    }

    public function updateSalary(Request $request, SalarySheet $salarySheet)
    {
        $this->guard();
        abort_if($salarySheet->institute_id !== $this->instituteId(), 403);
        abort_if($salarySheet->isPaid(), 403, 'Paid salary sheets cannot be edited. Revert the payment first.');

        $data = $request->validate([
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $salarySheet->update([
            'basic_salary' => $data['basic_salary'],
            'allowance' => $data['allowance'] ?? 0,
            'deduction' => $data['deduction'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('accounts.salaries', ['month' => $salarySheet->month])
            ->with('success', 'Salary sheet updated.');
    }

    public function paySalary(Request $request, SalarySheet $salarySheet)
    {
        $this->guard();
        abort_if($salarySheet->institute_id !== $this->instituteId(), 403);

        if ($salarySheet->isPaid()) {
            return back()->with('error', 'This salary is already marked as paid.');
        }

        if ((float) $salarySheet->net_salary <= 0) {
            return back()->with('error', 'Net salary must be greater than zero before payment.');
        }

        $data = $request->validate([
            'paid_on' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
        ]);

        $paidOn = $data['paid_on'] ?? now()->toDateString();

        DB::transaction(function () use ($salarySheet, $paidOn, $data) {
            $transaction = Transaction::create([
                'institute_id' => $salarySheet->institute_id,
                'type' => 'expense',
                'category' => config('accounts.salary_category', 'Salary'),
                'amount' => $salarySheet->net_salary,
                'transaction_date' => $paidOn,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'description' => 'Salary — ' . ($salarySheet->user?->name ?? 'Staff') . ' (' . $salarySheet->month . ')',
            ]);

            $salarySheet->update([
                'status' => SalarySheet::STATUS_PAID,
                'paid_on' => $paidOn,
                'transaction_id' => $transaction->id,
            ]);
        });

        return redirect()->route('accounts.salaries', ['month' => $salarySheet->month])
            ->with('success', 'Salary paid and an expense transaction was recorded automatically.');
    }

    public function unpaySalary(SalarySheet $salarySheet)
    {
        $this->guard();
        abort_if($salarySheet->institute_id !== $this->instituteId(), 403);

        if (! $salarySheet->isPaid()) {
            return back()->with('error', 'This salary is not marked as paid.');
        }

        DB::transaction(function () use ($salarySheet) {
            $salarySheet->transaction?->delete();
            $salarySheet->update([
                'status' => SalarySheet::STATUS_DRAFT,
                'paid_on' => null,
                'transaction_id' => null,
            ]);
        });

        return redirect()->route('accounts.salaries', ['month' => $salarySheet->month])
            ->with('success', 'Salary payment reverted and its expense transaction removed.');
    }

    public function payAllSalaries(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'payment_method' => ['nullable', 'string', 'max:100'],
        ]);

        $month = $data['month'];
        $paidOn = now()->toDateString();

        $sheets = SalarySheet::with('user')
            ->where('institute_id', $this->instituteId())
            ->forMonth($month)
            ->where('status', SalarySheet::STATUS_DRAFT)
            ->where('net_salary', '>', 0)
            ->get();

        if ($sheets->isEmpty()) {
            return back()->with('error', 'There are no unpaid salary sheets for this month.');
        }

        DB::transaction(function () use ($sheets, $paidOn, $data) {
            foreach ($sheets as $sheet) {
                $transaction = Transaction::create([
                    'institute_id' => $sheet->institute_id,
                    'type' => 'expense',
                    'category' => config('accounts.salary_category', 'Salary'),
                    'amount' => $sheet->net_salary,
                    'transaction_date' => $paidOn,
                    'payment_method' => $data['payment_method'] ?? 'Cash',
                    'description' => 'Salary — ' . ($sheet->user?->name ?? 'Staff') . ' (' . $sheet->month . ')',
                ]);

                $sheet->update([
                    'status' => SalarySheet::STATUS_PAID,
                    'paid_on' => $paidOn,
                    'transaction_id' => $transaction->id,
                ]);
            }
        });

        return redirect()->route('accounts.salaries', ['month' => $month])
            ->with('success', $sheets->count() . ' salary sheet(s) paid. Expenses were recorded automatically.');
    }

    public function destroySalary(SalarySheet $salarySheet)
    {
        $this->guard();
        abort_if($salarySheet->institute_id !== $this->instituteId(), 403);

        if ($salarySheet->isPaid()) {
            return back()->with('error', 'Paid salary sheets cannot be deleted. Revert the payment first.');
        }

        $month = $salarySheet->month;
        $salarySheet->delete();

        return redirect()->route('accounts.salaries', ['month' => $month])
            ->with('success', 'Salary sheet deleted.');
    }

    public function profitLoss(Request $request)
    {
        $this->guard();

        [$from, $to] = $this->resolvePeriod($request);
        $report = $this->buildProfitLoss($this->instituteId(), $from, $to);
        $period = $request->input('period', 'this_month');

        return view('accounts.profit-loss', array_merge($report, [
            'from' => $from,
            'to' => $to,
            'period' => $period,
        ]));
    }

    public function profitLossCsv(Request $request)
    {
        $this->guard();

        [$from, $to] = $this->resolvePeriod($request);
        $report = $this->buildProfitLoss($this->instituteId(), $from, $to);

        $filename = 'profit-loss-' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($report, $from, $to) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Profit & Loss Report']);
            fputcsv($handle, ['From', $from->format('Y-m-d'), 'To', $to->format('Y-m-d')]);
            fputcsv($handle, []);

            fputcsv($handle, ['INCOME']);
            fputcsv($handle, ['Category', 'Entries', 'Amount']);
            foreach ($report['incomeRows'] as $row) {
                fputcsv($handle, [$row->category, $row->entries, $row->total]);
            }
            fputcsv($handle, ['Total Income', '', $report['incomeTotal']]);
            fputcsv($handle, []);

            fputcsv($handle, ['EXPENSES']);
            fputcsv($handle, ['Category', 'Entries', 'Amount']);
            foreach ($report['expenseRows'] as $row) {
                fputcsv($handle, [$row->category, $row->entries, $row->total]);
            }
            fputcsv($handle, ['Total Expense', '', $report['expenseTotal']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Net Profit / Loss', '', $report['net']]);
            fputcsv($handle, ['Profit Margin (%)', '', $report['margin']]);
            fputcsv($handle, []);

            fputcsv($handle, ['MONTHLY BREAKDOWN']);
            fputcsv($handle, ['Month', 'Income', 'Expense', 'Net']);
            foreach ($report['monthly'] as $row) {
                fputcsv($handle, [$row['label'], $row['income'], $row['expense'], $row['net']]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function numericInput($value, float $fallback): float
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        return (float) $value;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = $request->input('period');
        $fromInput = $request->input('date_from');
        $toInput = $request->input('date_to');

        if (! $period && ($fromInput || $toInput)) {
            $period = 'custom';
        }

        $period = $period ?: 'this_month';
        $now = now();

        switch ($period) {
            case 'last_month':
                $from = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $to = $from->copy()->endOfMonth();
                break;

            case 'this_quarter':
                $from = $now->copy()->startOfQuarter();
                $to = $now->copy()->endOfQuarter();
                break;

            case 'this_year':
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfYear();
                break;

            case 'custom':
                try {
                    $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : $now->copy()->startOfMonth();
                } catch (\Throwable) {
                    $from = $now->copy()->startOfMonth();
                }

                try {
                    $to = $toInput ? Carbon::parse($toInput)->endOfDay() : $now->copy()->endOfDay();
                } catch (\Throwable) {
                    $to = $now->copy()->endOfDay();
                }

                if ($from->gt($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                break;

            case 'this_month':
            default:
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                break;
        }

        return [$from, $to];
    }

    private function buildProfitLoss(int $instituteId, Carbon $from, Carbon $to): array
    {
        $base = Transaction::query()
            ->where('institute_id', $instituteId)
            ->whereBetween('transaction_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $incomeRows = (clone $base)
            ->where('type', 'income')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as entries')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $expenseRows = (clone $base)
            ->where('type', 'expense')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as entries')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $incomeTotal = (float) $incomeRows->sum('total');
        $expenseTotal = (float) $expenseRows->sum('total');
        $net = $incomeTotal - $expenseTotal;
        $margin = $incomeTotal > 0 ? round(($net / $incomeTotal) * 100, 1) : 0.0;

        $monthly = [];
        $cursor = $from->copy()->startOfMonth();
        $last = $to->copy()->startOfMonth();
        $guard = 0;

        while ($cursor->lte($last) && $guard < 36) {
            $start = $cursor->copy()->startOfMonth();
            if ($start->lt($from)) {
                $start = $from->copy()->startOfDay();
            }

            $end = $cursor->copy()->endOfMonth();
            if ($end->gt($to)) {
                $end = $to->copy()->endOfDay();
            }

            $income = (float) Transaction::where('institute_id', $instituteId)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $expense = (float) Transaction::where('institute_id', $instituteId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $monthly[] = [
                'label' => $cursor->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];

            $cursor->addMonthNoOverflow();
            $guard++;
        }

        return [
            'incomeRows' => $incomeRows,
            'expenseRows' => $expenseRows,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'net' => $net,
            'margin' => $margin,
            'monthly' => $monthly,
        ];
    }
}
