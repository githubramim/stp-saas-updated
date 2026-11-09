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
                <form method="POST" action="{{ route('projects.store') }}">
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
