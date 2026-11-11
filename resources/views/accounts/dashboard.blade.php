<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Accounts & Profit / Loss') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Daily expenses, payroll and live profitability at any time.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('accounts.expenses') }}" class="btn btn-outline-primary rounded-3">
                    <i class="bi bi-receipt me-1"></i> {{ __('Daily Expenses') }}
                </a>
                <a href="{{ route('accounts.salaries') }}" class="btn btn-primary rounded-3">
                    <i class="bi bi-cash-stack me-1"></i> {{ __('Salary Sheet') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('accounts.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                </div>
            @endif

            @php
                $monthNet = $monthIncome - $monthExpense;
                $allTimeNet = $totalIncome - $totalExpense;
            @endphp

            {{-- Primary stats --}}
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('This Month Income') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bi bi-arrow-down-left"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">৳{{ number_format($monthIncome, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ now()->format('F Y') }}</div>
                </div>

                <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('This Month Expense') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-rose-600">৳{{ number_format($monthExpense, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Includes paid salaries') }}</div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Net Profit / Loss') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="bi bi-graph-up-arrow"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black {{ $monthNet >= 0 ? 'text-indigo-600' : 'text-rose-600' }}">৳{{ number_format($monthNet, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $monthNet >= 0 ? __('Profit this month') : __('Loss this month') }}</div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Salary Due This Month') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="bi bi-hourglass-split"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-amber-600">৳{{ number_format($salaryDue, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $pendingSalaryCount }} {{ __('unpaid sheet(s)') }}</div>
                </div>
            </div>

            {{-- Secondary strip --}}
            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-xs font-semibold text-slate-500">{{ __("Today's Expenses") }}</span>
                    <span class="text-sm font-bold text-slate-800">৳{{ number_format($todayExpense, 2) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-xs font-semibold text-slate-500">{{ __('All-time Net Position') }}</span>
                    <span class="text-sm font-bold {{ $allTimeNet >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">৳{{ number_format($allTimeNet, 2) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-xs font-semibold text-slate-500">{{ __('Salary Paid This Month') }}</span>
                    <span class="text-sm font-bold text-slate-800">৳{{ number_format($salaryPaid, 2) }}</span>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                {{-- Six month trend --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Income vs Expense — Last 6 Months') }}</h3>
                        <span class="text-[11px] font-semibold text-slate-400">
                            <span class="me-2 text-emerald-600">● {{ __('Income') }}</span>
                            <span class="text-rose-500">● {{ __('Expense') }}</span>
                        </span>
                    </div>

                    @php
                        $trendMax = max(1, collect($trend)->max(fn ($row) => max($row['income'], $row['expense'])));
                    @endphp

                    <div class="space-y-4">
                        @foreach($trend as $row)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-600">{{ $row['label'] }}</span>
                                    <span class="font-bold {{ $row['net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $row['net'] >= 0 ? '+' : '−' }}৳{{ number_format(abs($row['net']), 2) }}
                                    </span>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, round($row['income'] / $trendMax * 100)) }}%"></div>
                                        </div>
                                        <span class="w-24 text-right text-[11px] font-semibold text-slate-500">৳{{ number_format($row['income'], 0) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-rose-400" style="width: {{ min(100, round($row['expense'] / $trendMax * 100)) }}%"></div>
                                        </div>
                                        <span class="w-24 text-right text-[11px] font-semibold text-slate-500">৳{{ number_format($row['expense'], 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Expense breakdown this month --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Expense Breakdown — This Month') }}</h3>
                        <a href="{{ route('accounts.expenses') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('View all') }} →</a>
                    </div>

                    @if($expenseByCategory->isEmpty())
                        <div class="flex h-52 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                            <i class="bi bi-receipt mb-2 text-2xl text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">{{ __('No expenses recorded this month') }}</p>
                            <a href="{{ route('accounts.expenses') }}" class="mt-2 text-xs font-bold text-indigo-600">{{ __('Record an expense') }} →</a>
                        </div>
                    @else
                        @php
                            $expenseMax = max(1, (float) $expenseByCategory->max('total'));
                        @endphp
                        <div class="space-y-3">
                            @foreach($expenseByCategory as $row)
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-xs">
                                        <span class="font-semibold text-slate-600">{{ $row->category }} <span class="text-slate-400">({{ $row->entries }})</span></span>
                                        <span class="font-bold text-slate-700">৳{{ number_format($row->total, 2) }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: {{ min(100, round($row->total / $expenseMax * 100)) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                {{-- Recent transactions --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Recent Transactions') }}</h3>
                        <a href="{{ route('transactions.index') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('Open Finance') }} →</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td class="fw-semibold">{{ optional($transaction->transaction_date)->format('d M Y') }}</td>
                                        <td>{{ $transaction->category }}</td>
                                        <td>
                                            <span class="badge {{ $transaction->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold {{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                            ৳{{ number_format($transaction->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-6 text-center text-muted">{{ __('No transactions yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Salary snapshot --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Payroll Snapshot') }}</h3>
                        <a href="{{ route('accounts.salaries') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('Manage') }} →</a>
                    </div>
                    <div class="p-4">
                        @forelse($recentSalaries as $sheet)
                            <div class="flex items-center justify-between border-b border-slate-100 py-2 last:border-0">
                                <div class="min-w-0">
                                    <div class="truncate text-xs font-semibold text-slate-700">{{ $sheet->user?->name ?? __('Unknown staff') }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $sheet->monthLabel() }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-slate-800">৳{{ number_format($sheet->net_salary, 2) }}</div>
                                    <span class="badge {{ $sheet->isPaid() ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($sheet->status) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-slate-400">{{ __('No salary sheets for this month yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
