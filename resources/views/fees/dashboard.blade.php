<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                    <i class="bi bi-cash-coin text-primary"></i>
                    <span class="truncate">{{ __('Enrollment & Fee Management') }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Fee plans, student enrollments, installments and collections at a glance.') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('fees.students') }}" class="btn btn-outline-primary font-bold shadow-sm rounded-3">
                    <i class="bi bi-person-badge me-1"></i> {{ __('Student Plans') }}
                </a>
                <a href="{{ route('fees.installments') }}" class="btn btn-primary font-bold shadow-sm rounded-3">
                    <i class="bi bi-calendar2-check me-1"></i> {{ __('Installments') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-light min-vh-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('fees.partials.nav')

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

            {{-- Metric cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Plans') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><i class="bi bi-collection"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($stats['total_plans']) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Student fee plans created') }}</div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Active Plans') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bi bi-check2-circle"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">{{ number_format($stats['active_plans']) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Currently running') }}</div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Gross Amount') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="bi bi-cash-stack"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-indigo-600">৳{{ number_format((float) $stats['gross_amount'], 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Before adjustments') }}</div>
                </div>

                <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Net Amount') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="bi bi-calculator"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-sky-600">৳{{ number_format((float) $stats['net_amount'], 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('After discounts & waivers') }}</div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Collected') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bi bi-wallet2"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">৳{{ number_format((float) $stats['collected'], 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Total payments received') }}</div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Outstanding') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="bi bi-hourglass-split"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-amber-600">৳{{ number_format((float) $stats['outstanding'], 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Still to be collected') }}</div>
                </div>

                <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Overdue Installments') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i class="bi bi-exclamation-triangle"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-rose-600">{{ number_format($stats['overdue_count']) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Need immediate attention') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
                {{-- Due installments --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-calendar2-week me-1 text-amber-500"></i>{{ __('Upcoming & Due Installments') }}</h3>
                        <a href="{{ route('fees.installments') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('View all') }} →</a>
                    </div>
                    @if($dueInstallments->isEmpty())
                        <div class="p-10 text-center">
                            <i class="bi bi-check2-circle d-block mb-2 fs-2 text-success"></i>
                            <p class="text-sm font-semibold text-slate-600">{{ __('No installments due right now.') }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ __('You are all caught up with fee collections.') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Student / Ref') }}</th>
                                        <th>{{ __('Plan') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th class="text-end">{{ __('Balance') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-center pe-4">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dueInstallments as $row)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">{{ $row->student?->name_en ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $row->student?->reference_no ?? '—' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ ($row->studentFeePlan ?? $row->plan)?->code ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ __('Installment') }} #{{ $row->sequence }}</div>
                                            </td>
                                            <td class="{{ $row->due_date?->isPast() ? 'text-danger fw-semibold' : 'text-slate-700' }}">
                                                {{ $row->due_date?->format('d M Y') ?? '—' }}
                                            </td>
                                            <td class="text-end fw-black text-slate-800">৳{{ number_format((float) $row->balance, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $row->status === 'paid' ? 'bg-success' : ($row->status === 'partial' ? 'bg-warning text-dark' : ($row->status === 'overdue' ? 'bg-danger' : ($row->status === 'waived' ? 'bg-secondary' : 'bg-light text-dark border'))) }}">{{ ucfirst($row->status) }}</span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="{{ route('fees.installments') }}" class="btn btn-sm btn-outline-success py-0 px-2" title="{{ __('Record payment') }}">
                                                    <i class="bi bi-cash me-1"></i>{{ __('Pay') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($dueInstallments, 'links'))
                            <div class="px-4">
                                @include('partials.clean-pagination', ['paginator' => $dueInstallments])
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Reminder activity --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-bell me-1 text-indigo-500"></i>{{ __('Latest Reminder Activity') }}</h3>
                        <a href="{{ route('fees.reminders') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('Open') }} →</a>
                    </div>
                    <div class="p-4">
                        @if($reminders->isEmpty())
                            <div class="py-6 text-center">
                                <i class="bi bi-bell-slash d-block mb-2 fs-2 text-slate-300"></i>
                                <p class="text-xs text-slate-400">{{ __('No reminders sent yet.') }}</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($reminders as $reminder)
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 last:border-0 last:pb-0">
                                        <div class="min-w-0">
                                            <div class="truncate text-xs font-semibold text-slate-700">{{ $reminder->student?->name_en ?? '—' }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                <span class="badge bg-light text-dark border">{{ ucfirst($reminder->channel) }}</span>
                                                {{ $reminder->due_date?->format('d M Y') ?? '—' }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge {{ $reminder->status === 'sent' ? 'bg-success' : ($reminder->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ ucfirst($reminder->status) }}</span>
                                            <div class="mt-1 text-[11px] text-slate-400">{{ $reminder->sent_at?->format('d M Y h:i A') ?? '—' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent payments --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-clock-history me-1 text-slate-400"></i>{{ __('Recent Fee Payments') }}</h3>
                    <a href="{{ route('fees.students') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('Student plans') }} →</a>
                </div>
                @if($recentPayments->isEmpty())
                    <div class="p-10 text-center">
                        <i class="bi bi-inbox d-block mb-2 fs-2 text-slate-300"></i>
                        <p class="text-sm font-semibold text-slate-600">{{ __('No payments recorded yet.') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Collections will appear here as soon as they are posted.') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ __('Date') }}</th>
                                    <th>{{ __('Student') }}</th>
                                    <th>{{ __('Installment') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th class="pe-4">{{ __('Receipt') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $payment->payment_date?->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $payment->student?->name_en ?? '—' }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $payment->student?->reference_no ?? '—' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">#{{ $payment->installment?->sequence ?? '—' }}</span>
                                        </td>
                                        <td class="text-end fw-black text-success">৳{{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="text-muted">{{ $payment->payment_method ?: '—' }}</td>
                                        <td class="pe-4 font-monospace small">{{ $payment->receipt_number ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($recentPayments, 'links'))
                        <div class="px-4">
                            @include('partials.clean-pagination', ['paginator' => $recentPayments])
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
