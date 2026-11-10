<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Projects & Schemes') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Manage every project under your institute.') }}</p>
            </div>
            <button type="button" class="btn-solid-indigo" data-bs-toggle="modal" data-bs-target="#createProjectModal" aria-label="{{ __('New Project') }}">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">{{ __('New Project') }}</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @if($projects->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <i class="bi bi-folder2-open text-2xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-800">{{ __('No projects yet') }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                        {{ __('Create your first project to organise courses, batches, admissions and students.') }}
                    </p>
                    <button type="button" class="btn-solid-indigo mt-5" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('Create New Project') }}</span>
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($projects as $p)
                        <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-slate-800">{{ $p->name }}</h3>
                                    @if($p->code)
                                        <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">{{ $p->code }}</span>
                                    @endif
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $p->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </div>

                            <div class="grid flex-1 grid-cols-3 divide-x divide-slate-100 text-center">
                                <div class="p-3">
                                    <div class="text-lg font-bold text-slate-800">{{ $p->courses()->count() }}</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Courses') }}</div>
                                </div>
                                <div class="p-3">
                                    <div class="text-lg font-bold text-slate-800">{{ $p->batches()->count() }}</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Batches') }}</div>
                                </div>
                                <div class="p-3">
                                    <div class="text-lg font-bold text-slate-800">{{ $p->students()->count() }}</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Students') }}</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/60 p-4">
                                <a href="{{ route('projects.dashboard', $p) }}" class="btn-outline-indigo">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>{{ __('Dashboard') }}</span>
                                </a>
                                <a href="{{ route('projects.edit', $p) }}" class="btn-outline-gray">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>{{ __('Edit') }}</span>
                                </a>
                                <form method="POST" action="{{ route('projects.destroy', $p) }}" class="ms-auto" onsubmit="return confirm('Delete this project?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline-rose">
                                        <i class="bi bi-trash3"></i>
                                        <span>{{ __('Delete') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    {{-- Create Project Modal --}}
    <div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createProjectModalLabel">
                        <i class="bi bi-folder-plus text-indigo-400"></i> {{ __('New Project') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_project_name" class="form-label fw-semibold text-slate-700 small">{{ __('Project Name (English)') }} *</label>
                                <input id="create_project_name" type="text" name="name" value="{{ old('name') }}" required
                                       class="form-control rounded-3" placeholder="{{ __('e.g. Skills for Tomorrow') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_name_bn" class="form-label fw-semibold text-slate-700 small">{{ __('Project Name (Bangla)') }}</label>
                                <input id="create_project_name_bn" type="text" name="name_bn" value="{{ old('name_bn') }}"
                                       class="form-control rounded-3" placeholder="{{ __('Optional') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_code" class="form-label fw-semibold text-slate-700 small">{{ __('Project Code') }}</label>
                                <input id="create_project_code" type="text" name="code" value="{{ old('code') }}"
                                       class="form-control rounded-3 font-monospace" placeholder="{{ __('e.g. ASSES') }}">
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 rounded-3 bg-indigo-50 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-indigo-700">
                                    <i class="bi bi-bank"></i> {{ __('Donor, Budget & MoU (Project-Based Training)') }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_donor_name" class="form-label fw-semibold text-slate-700 small">{{ __('Donor Name') }}</label>
                                <input id="create_project_donor_name" type="text" name="donor_name" value="{{ old('donor_name') }}"
                                       class="form-control rounded-3" placeholder="{{ __('e.g. World Bank / SEIP') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_donor_type" class="form-label fw-semibold text-slate-700 small">{{ __('Donor Type') }}</label>
                                <select id="create_project_donor_type" name="donor_type" class="form-select rounded-3">
                                    <option value="">{{ __('Select type') }}</option>
                                    @foreach(['government' => 'Government', 'ngo' => 'NGO / Development Partner', 'corporate' => 'Corporate', 'other' => 'Other'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('donor_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="create_project_budget" class="form-label fw-semibold text-slate-700 small">{{ __('Budget (৳)') }}</label>
                                <input id="create_project_budget" type="number" step="0.01" min="0" name="budget_amount" value="{{ old('budget_amount') }}"
                                       class="form-control rounded-3" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label for="create_project_target" class="form-label fw-semibold text-slate-700 small">{{ __('Target Trainees') }}</label>
                                <input id="create_project_target" type="number" min="0" name="target_trainees" value="{{ old('target_trainees') }}"
                                       class="form-control rounded-3" placeholder="0">
                            </div>
                            <div class="col-md-4">
                                <label for="create_project_mou_number" class="form-label fw-semibold text-slate-700 small">{{ __('MoU Number') }}</label>
                                <input id="create_project_mou_number" type="text" name="mou_number" value="{{ old('mou_number') }}"
                                       class="form-control rounded-3" placeholder="{{ __('MoU reference') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_start_date" class="form-label fw-semibold text-slate-700 small">{{ __('Start Date') }}</label>
                                <input id="create_project_start_date" type="date" name="start_date" value="{{ old('start_date') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_end_date" class="form-label fw-semibold text-slate-700 small">{{ __('End Date') }}</label>
                                <input id="create_project_end_date" type="date" name="end_date" value="{{ old('end_date') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_coordinator" class="form-label fw-semibold text-slate-700 small">{{ __('Coordinator Name') }}</label>
                                <input id="create_project_coordinator" type="text" name="coordinator_name" value="{{ old('coordinator_name') }}"
                                       class="form-control rounded-3" placeholder="{{ __('Project coordinator') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_project_coordinator_contact" class="form-label fw-semibold text-slate-700 small">{{ __('Coordinator Contact') }}</label>
                                <input id="create_project_coordinator_contact" type="text" name="coordinator_contact" value="{{ old('coordinator_contact') }}"
                                       class="form-control rounded-3" placeholder="{{ __('Phone / email') }}">
                            </div>
                            <div class="col-12">
                                <label for="create_project_mou_file" class="form-label fw-semibold text-slate-700 small">{{ __('MoU Document') }}</label>
                                <input id="create_project_mou_file" type="file" name="mou_file" accept=".pdf,.jpg,.jpeg,.png"
                                       class="form-control rounded-3">
                                <div class="form-text text-[11px]">{{ __('PDF or image, up to 10 MB.') }}</div>
                            </div>
                            <div class="col-12">
                                <label for="create_project_description" class="form-label fw-semibold text-slate-700 small">{{ __('Description') }}</label>
                                <textarea id="create_project_description" name="description" rows="3"
                                          class="form-control rounded-3" placeholder="{{ __('Short description of this project...') }}">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Project') }}
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
                    var modal = document.getElementById('createProjectModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
