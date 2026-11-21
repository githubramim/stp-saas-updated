<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                    <i class="bi bi-card-list text-primary"></i>
                    <span class="truncate">{{ __('Fee Plans') }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Reusable fee templates for courses, batches or all students.') }}</p>
            </div>
            <button type="button" class="btn btn-primary font-bold shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                <i class="bi bi-plus-circle-fill me-1"></i> {{ __('New Fee Plan') }}
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
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-list-ul me-1 text-indigo-500"></i>{{ __('Plan Library') }}</h4>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('General plans apply to every enrolled student.') }}</span>
                    </div>

                    @if($plans->isEmpty())
                        <div class="p-10 text-center">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <i class="bi bi-card-list text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('No fee plans yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Create a fee template to start assigning plans to students.') }}</p>
                            <button type="button" class="btn btn-primary rounded-3 mt-4" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('New Fee Plan') }}
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small datatable" style="min-width: 820px;" data-dt-page-length="25">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Name / Code') }}</th>
                                        <th>{{ __('Scope') }}</th>
                                        <th>{{ __('Plan') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="pe-4">{{ __('Created') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plans as $plan)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">{{ $plan->name }}</div>
                                                <div class="text-muted font-monospace" style="font-size: 11px;">{{ $plan->code ?: '—' }}</div>
                                            </td>
                                            <td>
                                                @if($plan->course)
                                                    <span class="badge bg-light text-dark border"><i class="bi bi-book me-1 text-primary"></i>{{ __('Course') }}: {{ $plan->course->name }}</span>
                                                @elseif($plan->batch)
                                                    <span class="badge bg-light text-dark border"><i class="bi bi-people me-1 text-info"></i>{{ __('Batch') }}: {{ $plan->batch->name }}</span>
                                                @else
                                                    <span class="badge bg-light text-dark border"><i class="bi bi-globe me-1 text-slate-500"></i>{{ __('General') }}</span>
                                                @endif
                                            </td>
                                            <td class="fw-semibold text-slate-700">{{ $plan->duration_months }} {{ __('months') }}</td>
                                            <td class="text-end fw-black text-slate-800">৳{{ number_format((float) $plan->total_amount, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $plan->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($plan->status) }}</span>
                                            </td>
                                            <td class="pe-4 text-muted">{{ $plan->created_at?->format('d M Y') ?? '—' }}</td>
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

    {{-- Create Fee Plan Modal --}}
    <div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="createPlanModalLabel">
                        <i class="bi bi-card-list text-info"></i> {{ __('Create Fee Plan') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('fees.plans.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="fee_plan_name" class="form-label fw-semibold text-slate-700 small">{{ __('Plan Name') }} *</label>
                                <input id="fee_plan_name" type="text" name="name" value="{{ old('name') }}" class="form-control rounded-3" placeholder="{{ __('e.g. Diploma Full Fee Plan') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fee_plan_code" class="form-label fw-semibold text-slate-700 small">{{ __('Code') }}</label>
                                <input id="fee_plan_code" type="text" name="code" value="{{ old('code') }}" class="form-control rounded-3" placeholder="{{ __('e.g. DIP-12M') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="fee_plan_course" class="form-label fw-semibold text-slate-700 small">{{ __('Course') }}</label>
                                <select id="fee_plan_course" name="course_id" class="form-select rounded-3">
                                    <option value="">{{ __('General (all students)') }}</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fee_plan_batch" class="form-label fw-semibold text-slate-700 small">{{ __('Batch') }}</label>
                                <select id="fee_plan_batch" name="batch_id" class="form-select rounded-3">
                                    <option value="">{{ __('No specific batch') }}</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fee_plan_duration" class="form-label fw-semibold text-slate-700 small">{{ __('Duration') }} *</label>
                                <select id="fee_plan_duration" name="duration_months" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select duration') }}</option>
                                    <option value="3" {{ old('duration_months') === '3' ? 'selected' : '' }}>3 {{ __('months') }}</option>
                                    <option value="6" {{ old('duration_months') === '6' ? 'selected' : '' }}>6 {{ __('months') }}</option>
                                    <option value="12" {{ old('duration_months') === '12' ? 'selected' : '' }}>12 {{ __('months') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fee_plan_amount" class="form-label fw-semibold text-slate-700 small">{{ __('Total Amount (৳)') }} *</label>
                                <input id="fee_plan_amount" type="number" name="total_amount" value="{{ old('total_amount') }}" class="form-control rounded-3" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="col-12">
                                <label for="fee_plan_description" class="form-label fw-semibold text-slate-700 small">{{ __('Description') }}</label>
                                <textarea id="fee_plan_description" name="description" rows="2" class="form-control rounded-3" placeholder="{{ __('Optional details about this plan...') }}">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="fee_plan_status" class="form-label fw-semibold text-slate-700 small">{{ __('Status') }} *</label>
                                <select id="fee_plan_status" name="status" class="form-select rounded-3" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Plan') }}
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
                    var modal = document.getElementById('createPlanModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
