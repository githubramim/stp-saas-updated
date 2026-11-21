<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                    <i class="bi bi-calendar2-week text-primary"></i>
                    <span class="truncate">{{ __('Fee Installments') }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Track every due amount and record collections installment by installment.') }}</p>
            </div>
            <a href="{{ route('fees.students') }}" class="btn btn-outline-primary font-bold shadow-sm rounded-3">
                <i class="bi bi-person-badge me-1"></i> {{ __('Student Plans') }}
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

            {{-- Filters --}}
            <form method="POST" action="{{ route('ui.set', 'fees.installments') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="installment_status" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                        <select id="installment_status" name="status" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All statuses') }}</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="waived" {{ request('status') === 'waived' ? 'selected' : '' }}>Waived</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="installment_plan_months" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Plan Duration') }}</label>
                        <select id="installment_plan_months" name="plan_months" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All durations') }}</option>
                            <option value="3" {{ request('plan_months') === '3' ? 'selected' : '' }}>3 {{ __('months') }}</option>
                            <option value="6" {{ request('plan_months') === '6' ? 'selected' : '' }}>6 {{ __('months') }}</option>
                            <option value="12" {{ request('plan_months') === '12' ? 'selected' : '' }}>12 {{ __('months') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 font-bold"><i class="bi bi-funnel me-1"></i>{{ __('Filter') }}</button>
                        <a href="{{ route('fees.installments') }}" class="btn btn-sm btn-outline-secondary rounded-3">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-list-check me-1 text-indigo-500"></i>{{ __('Installment Schedule') }}</h4>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('Pay buttons open a quick collection form.') }}</span>
                    </div>

                    @if($installments->isEmpty())
                        <div class="p-10 text-center">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <i class="bi bi-calendar2-x text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('No installments found') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Try changing the filters or assign a fee plan to a student.') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small" style="min-width: 1040px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Student') }}</th>
                                        <th>{{ __('Plan') }}</th>
                                        <th>{{ __('Installment') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th class="text-end">{{ __('Paid') }}</th>
                                        <th class="text-end">{{ __('Balance') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-center pe-4">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($installments as $installment)
                                        @php $rowPlan = $installment->studentFeePlan ?? $installment->plan; @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">{{ $installment->student?->name_en ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $installment->student?->reference_no ?? '—' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $rowPlan?->code ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $rowPlan?->plan_months ?? '—' }} {{ __('months') }}</div>
                                            </td>
                                            <td class="fw-bold">#{{ $installment->sequence }}</td>
                                            <td class="{{ $installment->due_date?->isPast() ? 'text-danger fw-semibold' : 'text-slate-700' }}">
                                                {{ $installment->due_date?->format('d M Y') ?? '—' }}
                                            </td>
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
                        @if(method_exists($installments, 'links'))
                            <div class="mt-3">
                                @include('partials.clean-pagination', ['paginator' => $installments])
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Modals --}}
    @foreach($installments as $installment)
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
                                {{ $installment->student?->name_en ?? '—' }} · {{ __('Balance') }}: <strong>৳{{ number_format((float) $installment->balance, 2) }}</strong>
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
