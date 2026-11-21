<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('fees.students') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Back to student plans') }}
                </a>
                <h2 class="mt-1 font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex flex-wrap items-center gap-2 min-w-0">
                    <i class="bi bi-person-badge text-primary"></i>
                    <span class="truncate">{{ $plan->student?->name_en ?? '—' }}</span>
                    <span class="badge {{ $plan->status === 'active' ? 'bg-success' : ($plan->status === 'completed' ? 'bg-primary' : 'bg-secondary') }}">{{ ucfirst($plan->status) }}</span>
                    <span class="badge bg-light text-dark border"><i class="bi bi-card-list me-1 text-indigo-500"></i>{{ $plan->feePlan?->name ?? '—' }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    {{ __('Reference') }}: {{ $plan->student?->reference_no ?? '—' }} · {{ __('Plan code') }}: {{ $plan->code ?: '—' }}
                    · {{ __('Course') }}: {{ $plan->course?->name ?? '—' }} · {{ __('Batch') }}: {{ $plan->batch?->name ?? '—' }}
                </p>
            </div>
            <a href="{{ route('fees.installments') }}" class="btn btn-outline-primary font-bold shadow-sm rounded-3">
                <i class="bi bi-calendar2-check me-1"></i> {{ __('All Installments') }}
            </a>
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

            {{-- Student plan metrics --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Gross Amount') }}</div>
                    <div class="mt-2 text-2xl font-black text-slate-800">৳{{ number_format((float) $plan->gross_amount, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $plan->plan_months }} {{ __('months plan') }}</div>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Adjustments') }}</div>
                    <div class="mt-2 text-2xl font-black text-amber-600">৳{{ number_format((float) ($plan->discount_amount + $plan->scholarship_amount + $plan->waiver_amount), 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Discount, scholarship & waiver') }}</div>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Net Amount') }}</div>
                    <div class="mt-2 text-2xl font-black text-sky-600">৳{{ number_format((float) $plan->net_amount, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Payable after adjustments') }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Paid') }}</div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">৳{{ number_format((float) $plan->paid, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Collected so far') }}</div>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Due') }}</div>
                    <div class="mt-2 text-2xl font-black {{ (float) $plan->due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">৳{{ number_format((float) $plan->due, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Remaining balance') }}</div>
                </div>
            </div>

            {{-- Plan information strip --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Start Date') }}</div>
                    <div class="mt-1 text-sm font-bold text-slate-800">{{ $plan->start_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Reason / Notes') }}</div>
                    <div class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $plan->discount_reason ?? $plan->scholarship_reason ?? $plan->waiver_reason ?? $plan->notes ?? '—' }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Scope') }}</div>
                    <div class="mt-1 text-sm font-bold text-slate-800">{{ $plan->course?->name ?? $plan->batch?->name ?? __('General') }}</div>
                </div>
            </div>

            {{-- Adjustments --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-sliders2 me-1 text-amber-500"></i>{{ __('Adjustments') }}</h4>
                        <a href="{{ route('fees.adjustments') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('Manage adjustments') }} →</a>
                    </div>
                    @if($plan->adjustments->isEmpty())
                        <div class="py-8 text-center">
                            <i class="bi bi-sliders2 d-block mb-2 fs-2 text-slate-300"></i>
                            <p class="text-sm text-slate-500">{{ __('No adjustments applied to this plan.') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Type') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th class="pe-4">{{ __('Reference') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plan->adjustments as $adjustment)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge {{ $adjustment->type === 'discount' ? 'bg-info' : ($adjustment->type === 'scholarship' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ ucfirst($adjustment->type) }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-amber-600">৳{{ number_format((float) $adjustment->amount, 2) }}</td>
                                            <td class="text-slate-600">{{ $adjustment->reason ?: '—' }}</td>
                                            <td class="pe-4 text-muted">{{ $adjustment->reference ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Installments --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-calendar2-week me-1 text-indigo-500"></i>{{ __('Installments') }}</h4>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('Use Pay to record a collection against an installment.') }}</span>
                    </div>
                    @if($plan->installments->isEmpty())
                        <div class="py-8 text-center">
                            <i class="bi bi-calendar2-x d-block mb-2 fs-2 text-slate-300"></i>
                            <p class="text-sm text-slate-500">{{ __('No installments have been generated for this plan.') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small" style="min-width: 860px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Sequence') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th class="text-end">{{ __('Paid') }}</th>
                                        <th class="text-end">{{ __('Balance') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-center pe-4">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plan->installments as $installment)
                                        <tr>
                                            <td class="ps-4 fw-bold">#{{ $installment->sequence }}</td>
                                            <td>{{ $installment->due_date?->format('d M Y') ?? '—' }}</td>
                                            <td class="text-end fw-semibold text-slate-700">৳{{ number_format((float) $installment->amount, 2) }}</td>
                                            <td class="text-end fw-semibold text-success">৳{{ number_format((float) $installment->paid_amount, 2) }}</td>
                                            <td class="text-end fw-black {{ (float) $installment->balance > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format((float) $installment->balance, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $installment->status === 'paid' ? 'bg-success' : ($installment->status === 'partial' ? 'bg-warning text-dark' : ($installment->status === 'overdue' ? 'bg-danger' : ($installment->status === 'waived' ? 'bg-secondary' : 'bg-light text-dark border'))) }}">{{ ucfirst($installment->status) }}</span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $installment->id }}">
                                                    <i class="bi bi-cash me-1"></i>{{ __('Pay') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payments --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-receipt me-1 text-success"></i>{{ __('Payment History') }}</h4>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('All collections recorded for this plan.') }}</span>
                    </div>
                    @if($plan->payments->isEmpty())
                        <div class="py-8 text-center">
                            <i class="bi bi-receipt d-block mb-2 fs-2 text-slate-300"></i>
                            <p class="text-sm text-slate-500">{{ __('No payments recorded yet.') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small" style="min-width: 760px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Date') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th>{{ __('Method') }}</th>
                                        <th>{{ __('Receipt') }}</th>
                                        <th class="pe-4">{{ __('Reference') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plan->payments as $payment)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $payment->payment_date?->format('d M Y') ?? '—' }}</td>
                                            <td class="text-end fw-black text-success">৳{{ number_format((float) $payment->amount, 2) }}</td>
                                            <td class="text-muted">{{ $payment->payment_method ?: '—' }}</td>
                                            <td class="font-monospace small">{{ $payment->receipt_number ?: '—' }}</td>
                                            <td class="pe-4 text-muted">{{ $payment->reference_no ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Modals --}}
    @foreach($plan->installments as $installment)
        <div class="modal fade" id="paymentModal{{ $installment->id }}" tabindex="-1" aria-labelledby="paymentModalLabel{{ $installment->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-dark text-white rounded-top-4">
                        <h5 class="modal-title fw-bold text-white" id="paymentModalLabel{{ $installment->id }}">
                            <i class="bi bi-cash-coin text-warning"></i> {{ __('Record Payment') }} — #{{ $installment->sequence }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <form method="POST" action="{{ route('fees.payments.store', $installment) }}">
                        @csrf
                        <div class="modal-body p-4">
                            @include('partials.modal-errors')
                            <div class="mb-3 rounded-3 bg-light px-3 py-2 small text-slate-600">
                                {{ __('Installment balance') }}: <strong>৳{{ number_format((float) $installment->balance, 2) }}</strong>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="payment_amount_{{ $installment->id }}" class="form-label fw-semibold text-slate-700 small">{{ __('Amount (৳)') }} *</label>
                                    <input id="payment_amount_{{ $installment->id }}" type="number" name="amount" value="{{ old('amount', $installment->balance) }}" class="form-control rounded-3" min="0" step="0.01" placeholder="0.00" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_date_{{ $installment->id }}" class="form-label fw-semibold text-slate-700 small">{{ __('Payment Date') }} *</label>
                                    <input id="payment_date_{{ $installment->id }}" type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_method_{{ $installment->id }}" class="form-label fw-semibold text-slate-700 small">{{ __('Payment Method') }} *</label>
                                    <select id="payment_method_{{ $installment->id }}" name="payment_method" class="form-select rounded-3" required>
                                        <option value="">{{ __('— Select method —') }}</option>
                                        <option value="Cash">Cash</option>
                                        <option value="bKash">bKash</option>
                                        <option value="Nagad">Nagad</option>
                                        <option value="Rocket">Rocket</option>
                                        <option value="Bank">Bank</option>
                                        <option value="Card">Card</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_reference_{{ $installment->id }}" class="form-label fw-semibold text-slate-700 small">{{ __('Reference No') }}</label>
                                    <input id="payment_reference_{{ $installment->id }}" type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control rounded-3" placeholder="{{ __('Transaction / receipt reference') }}">
                                </div>
                                <div class="col-12">
                                    <label for="payment_note_{{ $installment->id }}" class="form-label fw-semibold text-slate-700 small">{{ __('Note') }}</label>
                                    <textarea id="payment_note_{{ $installment->id }}" name="note" rows="2" class="form-control rounded-3" placeholder="{{ __('Optional note...') }}">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success rounded-3 font-bold px-4">
                                <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Payment') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>
