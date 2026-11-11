<x-app-layout>
    <x-slot name="header">
        <div class="no-print flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Profit & Loss') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Know the exact profit or loss of the institute for any period.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('accounts.profit-loss.csv', request()->query()) }}" class="btn btn-outline-success rounded-3">
                    <i class="bi bi-file-earmark-excel me-1"></i> {{ __('Export CSV') }}
                </a>
                <button type="button" class="btn btn-outline-primary rounded-3" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> {{ __('Print Report') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('accounts.partials.nav')

            @if(session('success'))
                <div class="no-print mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="no-print mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <div class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Quick Periods') }}</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach([
                                'this_month' => __('This Month'),
                                'last_month' => __('Last Month'),
                                'this_quarter' => __('This Quarter'),
                                'this_year' => __('This Year'),
                            ] as $value => $label)
                                <a href="{{ route('accounts.profit-loss', ['period' => $value]) }}"
                                   class="rounded-xl px-3 py-2 text-xs font-semibold transition
                                          {{ $period === $value ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <form method="GET" action="{{ route('accounts.profit-loss') }}" class="flex flex-wrap items-end gap-2">
                        <input type="hidden" name="period" value="custom">
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('From') }}</label>
                            <input type="date" name="date_from" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('To') }}</label>
                            <input type="date" name="date_to" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary rounded-3">
                            <i class="bi bi-funnel me-1"></i> {{ __('Apply') }}
                        </button>
                    </form>
                </div>
                <div class="mt-4 text-xs text-slate-500">
                    <i class="bi bi-calendar-range me-1"></i>
                    {{ __('Showing') }}
                    <strong>{{ $from->format('d M Y') }}</strong> — <strong>{{ $to->format('d M Y') }}</strong>
                </div>
            </div>

            {{-- Summary --}}
            <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="print-compact rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Income') }}</div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">৳{{ number_format($incomeTotal, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $incomeRows->count() }} {{ __('income categories') }}</div>
                </div>
                <div class="print-compact rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Expense') }}</div>
                    <div class="mt-2 text-2xl font-black text-rose-600">৳{{ number_format($expenseTotal, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $expenseRows->count() }} {{ __('expense categories') }}</div>
                </div>
                <div class="print-compact rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $net >= 0 ? __('Net Profit') : __('Net Loss') }}</div>
                    <div class="mt-2 text-2xl font-black {{ $net >= 0 ? 'text-indigo-600' : 'text-rose-600' }}">
                        {{ $net >= 0 ? '' : '−' }}৳{{ number_format(abs($net), 2) }}
                    </div>
                    <div class="mt-1 text-xs text-slate-400">{{ $net >= 0 ? __('Income exceeds expenses') : __('Expenses exceed income') }}</div>
                </div>
                <div class="print-compact rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Profit Margin') }}</div>
                    <div class="mt-2 text-2xl font-black {{ $margin >= 0 ? 'text-slate-800' : 'text-rose-600' }}">{{ number_format($margin, 1) }}%</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Net profit ÷ total income') }}</div>
                </div>
            </div>

            {{-- Category breakdown --}}
            <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="print-compact rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800">
                            <i class="bi bi-arrow-down-left me-1 text-emerald-600"></i> {{ __('Income by Category') }}
                        </h3>
                        <span class="text-xs font-bold text-emerald-600">৳{{ number_format($incomeTotal, 2) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Entries</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomeRows as $row)
                                    <tr>
                                        <td class="fw-semibold">{{ $row->category }}</td>
                                        <td class="text-center text-muted">{{ $row->entries }}</td>
                                        <td class="text-end pe-4 fw-bold text-emerald-600">৳{{ number_format($row->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-6 text-center text-muted">{{ __('No income recorded for this period.') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">{{ __('Total Income') }}</th>
                                    <th class="text-end pe-4 text-emerald-700">৳{{ number_format($incomeTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="print-compact rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800">
                            <i class="bi bi-arrow-up-right me-1 text-rose-500"></i> {{ __('Expense by Category') }}
                        </h3>
                        <span class="text-xs font-bold text-rose-600">৳{{ number_format($expenseTotal, 2) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Entries</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenseRows as $row)
                                    <tr>
                                        <td class="fw-semibold">{{ $row->category }}</td>
                                        <td class="text-center text-muted">{{ $row->entries }}</td>
                                        <td class="text-end pe-4 fw-bold text-rose-600">৳{{ number_format($row->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-6 text-center text-muted">{{ __('No expenses recorded for this period.') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">{{ __('Total Expense') }}</th>
                                    <th class="text-end pe-4 text-rose-700">৳{{ number_format($expenseTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Monthly breakdown --}}
            <div class="print-compact rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h3 class="text-sm font-bold text-slate-800">{{ __('Monthly Breakdown') }}</h3>
                    <span class="text-[11px] text-slate-400">{{ count($monthly) }} {{ __('month(s) in range') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small datatable"
                           data-dt-paging="false" data-dt-searching="false" data-dt-info="false">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Month</th>
                                <th class="text-end">{{ __('Income') }}</th>
                                <th class="text-end">{{ __('Expense') }}</th>
                                <th class="text-end pe-4">{{ __('Net') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthly as $row)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $row['label'] }}</td>
                                    <td class="text-end text-emerald-600">৳{{ number_format($row['income'], 2) }}</td>
                                    <td class="text-end text-rose-600">৳{{ number_format($row['expense'], 2) }}</td>
                                    <td class="text-end pe-4 fw-bold {{ $row['net'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $row['net'] >= 0 ? '' : '−' }}৳{{ number_format(abs($row['net']), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Total') }}</th>
                                <th class="text-end">৳{{ number_format(array_sum(array_column($monthly, 'income')), 2) }}</th>
                                <th class="text-end">৳{{ number_format(array_sum(array_column($monthly, 'expense')), 2) }}</th>
                                <th class="text-end pe-4 {{ $net >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $net >= 0 ? '' : '−' }}৳{{ number_format(abs($net), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mt-4 text-center text-[11px] text-slate-400">
                {{ __('Generated') }} {{ now()->format('d M Y, h:i A') }} · {{ __('Salary payments are included as expenses automatically.') }}
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print, aside, header, nav, .sidebar-item, .btn {
                display: none !important;
            }
            body { background: #fff !important; }
            main { padding-top: 0 !important; }
            .print-compact {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                break-inside: avoid;
            }
            .print-compact .table { font-size: 10px !important; }
            .max-w-7xl { max-width: 100% !important; }
        }
    </style>
</x-app-layout>
