<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Payroll') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Attendance-based salary calculation from each employee’s salary structure.') }}</p>
            </div>
            <form method="POST" action="{{ route('hr.payroll.generate') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-magic me-1"></i> {{ __('Generate Salary Sheets') }}</button>
            </form>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('hr.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                <form method="POST" action="{{ route('ui.set', 'hr.payroll') }}" class="d-flex align-items-center gap-2">
                    @csrf
                    <label for="pay_month" class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Month') }}</label>
                    <input id="pay_month" type="month" name="month" value="{{ $month }}" class="form-control form-control-sm rounded-3" onchange="this.form.submit()">
                </form>
                <div class="text-xs text-slate-500">{{ $workingDays }} {{ __('working days (Friday holiday)') }}</div>
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Gross') }}</div>
                    <div class="mt-2 text-xl font-black text-slate-800">৳{{ number_format($totals['gross'], 2) }}</div>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Deduction') }}</div>
                    <div class="mt-2 text-xl font-black text-rose-600">৳{{ number_format($totals['deduction'], 2) }}</div>
                </div>
                <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Net Payable') }}</div>
                    <div class="mt-2 text-xl font-black text-indigo-600">৳{{ number_format($totals['net'], 2) }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Paid (sheets)') }}</div>
                    <div class="mt-2 text-xl font-black text-emerald-600">৳{{ number_format($totals['paid'], 2) }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Employee') }}</th>
                                <th class="text-end">{{ __('Basic') }}</th>
                                <th class="text-end">{{ __('Allowance') }}</th>
                                <th>{{ __('Attendance') }}</th>
                                <th class="text-end">{{ __('Deduction') }}</th>
                                <th class="text-end">{{ __('Net') }}</th>
                                <th>{{ __('Sheet') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $row['user']->name }}</div>
                                        <div class="text-muted small">{{ ucfirst($row['user']->role) }}</div>
                                    </td>
                                    <td class="text-end">৳{{ number_format($row['profile']->basic_salary, 2) }}</td>
                                    <td class="text-end">৳{{ number_format($row['profile']->totalAllowance(), 2) }}</td>
                                    <td>
                                        <span class="text-success">{{ (int) $row['summary']['present'] }} P</span> /
                                        <span class="text-warning">{{ (int) $row['summary']['late'] }} L</span> /
                                        <span class="text-danger">{{ (int) $row['summary']['absent'] }} A</span> /
                                        <span>{{ $row['paid_leave'] }} {{ __('leave') }}</span>
                                    </td>
                                    <td class="text-end">৳{{ number_format($row['deduction'], 2) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format($row['net'], 2) }}</td>
                                    <td>
                                        @if($row['sheet'])
                                            <span class="badge bg-{{ $row['sheet']->status === 'paid' ? 'success' : 'secondary' }}">{{ ucfirst($row['sheet']->status) }}</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ __('Not generated') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-6 text-center text-muted">{{ __('No employees in this branch.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-400">
                {{ __('Generated sheets appear in Accounts → Salary Sheet, where they can be marked paid and posted to the ledger. Paid sheets are never overwritten.') }}
            </p>
        </div>
    </div>
</x-app-layout>
