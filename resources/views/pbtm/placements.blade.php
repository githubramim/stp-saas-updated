<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">
                    <i class="bi bi-briefcase me-1 text-amber-500"></i>
                    {{ __('Post-Training Job Placement') }}
                </h2>
                <p class="text-xs text-slate-400">{{ __('Record employer details and track employment outcomes after training completion.') }}</p>
            </div>
            <button type="button" class="btn btn-primary rounded-3 font-bold" data-bs-toggle="modal" data-bs-target="#createPlacementModal">
                <i class="bi bi-plus-circle me-1"></i> {{ __('New Placement') }}
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @include('pbtm.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif

            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Currently Placed') }}</div>
                    <div class="mt-1 text-2xl font-black text-emerald-600">{{ number_format($totals['placed']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Dropped') }}</div>
                    <div class="mt-1 text-2xl font-black text-rose-600">{{ number_format($totals['dropped']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Average Salary') }}</div>
                    <div class="mt-1 text-2xl font-black text-slate-800">৳{{ number_format($totals['avg_salary'], 0) }}</div>
                </div>
            </div>

            <div class="mb-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div>
                        <label for="placement_status" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                        <select id="placement_status" name="status" class="form-select form-select-sm rounded-xl">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm rounded-xl font-bold"><i class="bi bi-funnel me-1"></i> {{ __('Filter') }}</button>
                    <a href="{{ route('pbtm.placements', array_filter(['project_id' => $project?->id])) }}" class="btn btn-light btn-sm rounded-xl border">{{ __('Reset') }}</a>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                @if($placements->isEmpty())
                    <div class="p-10 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <i class="bi bi-briefcase text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('No job placements yet') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Add employment outcomes for graduated trainees to report project impact.') }}</p>
                        <button type="button" class="btn btn-primary rounded-3 mt-4" data-bs-toggle="modal" data-bs-target="#createPlacementModal">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('New Placement') }}
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small" style="min-width: 980px;">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Trainee') }}</th>
                                    <th>{{ __('Employer') }}</th>
                                    <th>{{ __('Job / Type') }}</th>
                                    <th class="text-end">{{ __('Salary') }}</th>
                                    <th>{{ __('Placement Date') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($placements as $placement)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-slate-800">{{ $placement->student?->name_en ?? '—' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $placement->project?->name ?? $placement->student?->reference_no }}</div>
                                        </td>
                                        <td class="font-semibold text-slate-700">{{ $placement->employer_name }}</td>
                                        <td>
                                            <div class="text-slate-700">{{ $placement->job_title ?: '—' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $employmentTypes[$placement->employment_type] ?? '—' }}</div>
                                        </td>
                                        <td class="text-end font-bold">৳{{ number_format((float) $placement->monthly_salary, 0) }}</td>
                                        <td class="text-slate-600">{{ $placement->placement_date?->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            <div class="text-slate-700">{{ $placement->contact_person ?: '—' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $placement->contact_number }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $placement->status === 'dropped' ? 'bg-danger' : 'bg-success' }}">
                                                {{ $statuses[$placement->status] ?? ucfirst($placement->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('pbtm.placements.destroy', $placement) }}" onsubmit="return confirm('Delete this placement record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">{{ __('Delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="createPlacementModal" tabindex="-1" aria-labelledby="createPlacementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="createPlacementModalLabel">
                        <i class="bi bi-briefcase text-amber-400"></i> {{ __('New Job Placement') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('pbtm.placements.store') }}">
                    @csrf
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="placement_student" class="form-label fw-semibold text-slate-700 small">{{ __('Trainee') }} *</label>
                                <select id="placement_student" name="student_id" class="form-select rounded-3" required>
                                    <option value="">— {{ __('Select trainee') }} —</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name_en }} · {{ $student->reference_no }} · {{ $student->project?->name ?? __('No project') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="placement_employer" class="form-label fw-semibold text-slate-700 small">{{ __('Employer Name') }} *</label>
                                <input id="placement_employer" type="text" name="employer_name" value="{{ old('employer_name') }}" class="form-control rounded-3" required placeholder="{{ __('Company / organisation') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="placement_job_title" class="form-label fw-semibold text-slate-700 small">{{ __('Job Title') }}</label>
                                <input id="placement_job_title" type="text" name="job_title" value="{{ old('job_title') }}" class="form-control rounded-3" placeholder="{{ __('e.g. Machine Operator') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="placement_type" class="form-label fw-semibold text-slate-700 small">{{ __('Employment Type') }}</label>
                                <select id="placement_type" name="employment_type" class="form-select rounded-3">
                                    <option value="">{{ __('Select type') }}</option>
                                    @foreach($employmentTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('employment_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="placement_salary" class="form-label fw-semibold text-slate-700 small">{{ __('Monthly Salary (৳)') }}</label>
                                <input id="placement_salary" type="number" step="0.01" min="0" name="monthly_salary" value="{{ old('monthly_salary') }}" class="form-control rounded-3" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label for="placement_date" class="form-label fw-semibold text-slate-700 small">{{ __('Placement Date') }}</label>
                                <input id="placement_date" type="date" name="placement_date" value="{{ old('placement_date', now()->toDateString()) }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="placement_contact_person" class="form-label fw-semibold text-slate-700 small">{{ __('Contact Person') }}</label>
                                <input id="placement_contact_person" type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-control rounded-3" placeholder="{{ __('HR / supervisor name') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="placement_contact_number" class="form-label fw-semibold text-slate-700 small">{{ __('Contact Number') }}</label>
                                <input id="placement_contact_number" type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-control rounded-3" placeholder="{{ __('Phone') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="placement_status_input" class="form-label fw-semibold text-slate-700 small">{{ __('Status') }} *</label>
                                <select id="placement_status_input" name="status" class="form-select rounded-3" required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', 'placed') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="placement_remarks" class="form-label fw-semibold text-slate-700 small">{{ __('Remarks') }}</label>
                                <textarea id="placement_remarks" name="remarks" rows="2" class="form-control rounded-3" placeholder="{{ __('Optional notes') }}">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Placement') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() || request('create'))
                    var modal = document.getElementById('createPlacementModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
