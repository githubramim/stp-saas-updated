<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                    <i class="bi bi-sliders2 text-primary"></i>
                    <span class="truncate">{{ __('Fee Adjustments') }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Record discounts, scholarships and waivers against student fee plans.') }}</p>
            </div>
            <button type="button" class="btn btn-primary font-bold shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createAdjustmentModal">
                <i class="bi bi-plus-circle-fill me-1"></i> {{ __('New Adjustment') }}
            </button>
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

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-list-ul me-1 text-indigo-500"></i>{{ __('Adjustment History') }}</h4>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('Deleting an adjustment recalculates the plan balance.') }}</span>
                    </div>

                    @if($adjustments->isEmpty())
                        <div class="p-10 text-center">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                <i class="bi bi-sliders2 text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('No adjustments recorded') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Add a discount, scholarship or waiver for a student fee plan.') }}</p>
                            <button type="button" class="btn btn-primary rounded-3 mt-4" data-bs-toggle="modal" data-bs-target="#createAdjustmentModal">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('New Adjustment') }}
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small datatable" style="min-width: 960px;" data-dt-page-length="25">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Type') }}</th>
                                        <th>{{ __('Plan / Student') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th>{{ __('Reference') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th class="text-center pe-4">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adjustments as $adjustment)
                                        @php $adjustmentPlan = $adjustment->studentFeePlan ?? $adjustment->student_fee_plan; @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge {{ $adjustment->type === 'discount' ? 'bg-info' : ($adjustment->type === 'scholarship' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ ucfirst($adjustment->type) }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark font-monospace">{{ $adjustmentPlan?->code ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $adjustmentPlan?->student?->name_en ?? '—' }}</div>
                                            </td>
                                            <td class="text-end fw-black text-amber-600">৳{{ number_format((float) $adjustment->amount, 2) }}</td>
                                            <td class="text-slate-600">{{ $adjustment->reason ?: '—' }}</td>
                                            <td class="text-muted">{{ $adjustment->reference ?: '—' }}</td>
                                            <td class="text-muted">{{ $adjustment->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="text-center pe-4">
                                                <form action="{{ route('fees.adjustments.destroy', $adjustment) }}" method="POST" class="d-inline" onsubmit="confirmDelete(event)">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="{{ __('Delete adjustment') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($adjustments, 'links'))
                            <div class="mt-3">
                                @include('partials.clean-pagination', ['paginator' => $adjustments])
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Create Adjustment Modal --}}
    <div class="modal fade" id="createAdjustmentModal" tabindex="-1" aria-labelledby="createAdjustmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="createAdjustmentModalLabel">
                        <i class="bi bi-sliders2 text-warning"></i> {{ __('Add Fee Adjustment') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('fees.adjustments.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="adjustment_plan" class="form-label fw-semibold text-slate-700 small">{{ __('Student Fee Plan') }} *</label>
                                <select id="adjustment_plan" name="student_fee_plan_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('— Select student plan —') }}</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('student_fee_plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->code ?: __('Plan') }} — {{ $plan->student?->name_en ?? '—' }} ({{ $plan->student?->reference_no ?? '—' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="adjustment_type" class="form-label fw-semibold text-slate-700 small">{{ __('Type') }} *</label>
                                <select id="adjustment_type" name="type" class="form-select rounded-3" required>
                                    <option value="">{{ __('— Select type —') }}</option>
                                    <option value="discount" {{ old('type') === 'discount' ? 'selected' : '' }}>{{ __('Discount') }}</option>
                                    <option value="scholarship" {{ old('type') === 'scholarship' ? 'selected' : '' }}>{{ __('Scholarship') }}</option>
                                    <option value="waiver" {{ old('type') === 'waiver' ? 'selected' : '' }}>{{ __('Waiver') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="adjustment_amount" class="form-label fw-semibold text-slate-700 small">{{ __('Amount (৳)') }} *</label>
                                <input id="adjustment_amount" type="number" name="amount" value="{{ old('amount') }}" class="form-control rounded-3" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="col-md-8">
                                <label for="adjustment_reason" class="form-label fw-semibold text-slate-700 small">{{ __('Reason') }} *</label>
                                <input id="adjustment_reason" type="text" name="reason" value="{{ old('reason') }}" class="form-control rounded-3" placeholder="{{ __('e.g. Sibling concession') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="adjustment_reference" class="form-label fw-semibold text-slate-700 small">{{ __('Reference') }}</label>
                                <input id="adjustment_reference" type="text" name="reference" value="{{ old('reference') }}" class="form-control rounded-3" placeholder="{{ __('Optional') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Adjustment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any())
                    var modal = document.getElementById('createAdjustmentModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
