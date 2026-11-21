<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                    <i class="bi bi-person-badge text-primary"></i>
                    <span class="truncate">{{ __('Student Fee Plans') }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Assign fee templates, track adjustments and monitor collections per student.') }}</p>
            </div>
            <button type="button" class="btn btn-primary font-bold shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createStudentPlanModal">
                <i class="bi bi-plus-circle-fill me-1"></i> {{ __('Assign Fee Plan') }}
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
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-people me-1 text-indigo-500"></i>{{ __('Enrolled Fee Plans') }}</h4>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('Open a plan to manage installments and payments.') }}</span>
                    </div>

                    @if($plans->isEmpty())
                        <div class="p-10 text-center">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <i class="bi bi-person-plus text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('No student fee plans yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Assign the first fee plan to a student to begin tracking installments.') }}</p>
                            <button type="button" class="btn btn-primary rounded-3 mt-4" data-bs-toggle="modal" data-bs-target="#createStudentPlanModal">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Assign Fee Plan') }}
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small datatable" style="min-width: 1080px;" data-dt-page-length="25">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Code') }}</th>
                                        <th>{{ __('Student') }}</th>
                                        <th>{{ __('Plan') }}</th>
                                        <th class="text-end">{{ __('Gross') }}</th>
                                        <th class="text-end">{{ __('Adjustments') }}</th>
                                        <th class="text-end">{{ __('Net') }}</th>
                                        <th class="text-end">{{ __('Paid') }}</th>
                                        <th class="text-end">{{ __('Due') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-center pe-4">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plans as $plan)
                                        <tr>
                                            <td class="ps-4 font-monospace fw-semibold">{{ $plan->code ?: '—' }}</td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $plan->student?->name_en ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $plan->student?->reference_no ?? '—' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $plan->feePlan?->name ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $plan->plan_months }} {{ __('months') }} · {{ $plan->start_date?->format('d M Y') ?? '—' }}</div>
                                            </td>
                                            <td class="text-end fw-semibold text-slate-700">৳{{ number_format((float) $plan->gross_amount, 2) }}</td>
                                            <td class="text-end text-warning fw-semibold">
                                                ৳{{ number_format((float) ($plan->discount_amount + $plan->scholarship_amount + $plan->waiver_amount), 2) }}
                                            </td>
                                            <td class="text-end fw-black text-slate-800">৳{{ number_format((float) $plan->net_amount, 2) }}</td>
                                            <td class="text-end fw-bold text-success">৳{{ number_format((float) $plan->paid, 2) }}</td>
                                            <td class="text-end fw-bold {{ (float) $plan->due > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format((float) $plan->due, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $plan->status === 'active' ? 'bg-success' : ($plan->status === 'completed' ? 'bg-primary' : 'bg-secondary') }}">{{ ucfirst($plan->status) }}</span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <a href="{{ route('fees.students.show', $plan) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="{{ __('View plan') }}">
                                                    <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($plans, 'links'))
                            <div class="mt-3">
                                @include('partials.clean-pagination', ['paginator' => $plans])
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Student Fee Plan Modal --}}
    <div class="modal fade" id="createStudentPlanModal" tabindex="-1" aria-labelledby="createStudentPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="createStudentPlanModalLabel">
                        <i class="bi bi-person-badge text-info"></i> {{ __('Assign Fee Plan to Student') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('fees.students.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="student_plan_student" class="form-label fw-semibold text-slate-700 small">{{ __('Student') }} *</label>
                                <select id="student_plan_student" name="student_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('— Select student —') }}</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name_en }} — {{ $student->reference_no ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="student_plan_fee_plan" class="form-label fw-semibold text-slate-700 small">{{ __('Template Plan') }}</label>
                                <select id="student_plan_fee_plan" name="fee_plan_id" class="form-select rounded-3">
                                    <option value="">{{ __('No template plan') }}</option>
                                    @foreach($feePlans as $feePlan)
                                        <option value="{{ $feePlan->id }}" {{ old('fee_plan_id') == $feePlan->id ? 'selected' : '' }}>
                                            {{ $feePlan->name }}{{ $feePlan->code ? ' (' . $feePlan->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="student_plan_months" class="form-label fw-semibold text-slate-700 small">{{ __('Duration') }} *</label>
                                <select id="student_plan_months" name="plan_months" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select duration') }}</option>
                                    <option value="3" {{ old('plan_months') === '3' ? 'selected' : '' }}>3 {{ __('months') }}</option>
                                    <option value="6" {{ old('plan_months') === '6' ? 'selected' : '' }}>6 {{ __('months') }}</option>
                                    <option value="12" {{ old('plan_months') === '12' ? 'selected' : '' }}>12 {{ __('months') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="student_plan_gross" class="form-label fw-semibold text-slate-700 small">{{ __('Gross Amount (৳)') }} *</label>
                                <input id="student_plan_gross" type="number" name="gross_amount" value="{{ old('gross_amount') }}" class="form-control rounded-3" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label for="student_plan_start" class="form-label fw-semibold text-slate-700 small">{{ __('Start Date') }} *</label>
                                <input id="student_plan_start" type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="form-control rounded-3" required>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>
                            <div class="col-md-4">
                                <label for="student_plan_discount" class="form-label fw-semibold text-slate-700 small">{{ __('Discount (৳)') }}</label>
                                <input id="student_plan_discount" type="number" name="discount_amount" value="{{ old('discount_amount', 0) }}" class="form-control rounded-3" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="student_plan_scholarship" class="form-label fw-semibold text-slate-700 small">{{ __('Scholarship (৳)') }}</label>
                                <input id="student_plan_scholarship" type="number" name="scholarship_amount" value="{{ old('scholarship_amount', 0) }}" class="form-control rounded-3" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label for="student_plan_waiver" class="form-label fw-semibold text-slate-700 small">{{ __('Waiver (৳)') }}</label>
                                <input id="student_plan_waiver" type="number" name="waiver_amount" value="{{ old('waiver_amount', 0) }}" class="form-control rounded-3" min="0" step="0.01">
                            </div>

                            <div class="col-md-4">
                                <label for="student_plan_discount_reason" class="form-label fw-semibold text-slate-700 small">{{ __('Discount Reason') }}</label>
                                <input id="student_plan_discount_reason" type="text" name="discount_reason" value="{{ old('discount_reason') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label for="student_plan_scholarship_reason" class="form-label fw-semibold text-slate-700 small">{{ __('Scholarship Reason') }}</label>
                                <input id="student_plan_scholarship_reason" type="text" name="scholarship_reason" value="{{ old('scholarship_reason') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label for="student_plan_waiver_reason" class="form-label fw-semibold text-slate-700 small">{{ __('Waiver Reason') }}</label>
                                <input id="student_plan_waiver_reason" type="text" name="waiver_reason" value="{{ old('waiver_reason') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <label for="student_plan_notes" class="form-label fw-semibold text-slate-700 small">{{ __('Notes') }}</label>
                                <textarea id="student_plan_notes" name="notes" rows="2" class="form-control rounded-3" placeholder="{{ __('Optional notes for this enrollment...') }}">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Assign Plan') }}
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
                    var modal = document.getElementById('createStudentPlanModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
