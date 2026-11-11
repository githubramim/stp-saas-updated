<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Courses / Trades') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Programmes offered by your institute.') }}</p>
            </div>
            <button type="button" class="btn-solid-indigo" data-bs-toggle="modal" data-bs-target="#createCourseModal" aria-label="{{ __('Add Course') }}">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">{{ __('Add Course') }}</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @if($courses->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-50 text-teal-600">
                        <i class="bi bi-journal-text text-2xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-800">{{ __('No courses yet') }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ __('Add your first course or trade to start building batches.') }}</p>
                    <button type="button" class="btn-solid-indigo mt-5" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('Add Course') }}</span>
                    </button>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] text-sm datatable" data-dt-page-length="20">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/80 text-left text-[11px] uppercase tracking-wide text-slate-400">
                                    <th class="px-5 py-3 font-semibold">{{ __('Course') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('TAM Occupation') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Level') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Nominal Hours') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Fee') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Seats') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-right font-semibold">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($courses as $c)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-slate-800">{{ $c->name }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $c->course_code ?: ($c->duration ?: '—') }}</div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="text-slate-700">{{ $c->tam_occupation ?: '—' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $c->tam_sector }}</div>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($c->ntvqf_level)
                                                <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">L{{ $c->ntvqf_level }}</span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($c->duration_hours)
                                                <div class="font-semibold text-slate-700">{{ number_format($c->duration_hours) }} h</div>
                                                <div class="text-[11px] text-slate-400">{{ $c->duration }}</div>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 font-medium text-slate-700">৳{{ number_format($c->fee, 0) }}</td>
                                        <td class="px-5 py-4 text-slate-500">{{ $c->seat_capacity }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $c->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ ucfirst($c->status) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('courses.edit', $c) }}" class="btn-outline-gray">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>{{ __('Edit') }}</span>
                                                </a>
                                                <form method="POST" action="{{ route('courses.destroy', $c) }}" onsubmit="return confirm('Delete this course?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-outline-rose">
                                                        <i class="bi bi-trash3"></i>
                                                        <span>{{ __('Delete') }}</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
    {{-- Create Course Modal --}}
    <div class="modal fade" id="createCourseModal" tabindex="-1" aria-labelledby="createCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createCourseModalLabel">
                        <i class="bi bi-journal-plus text-teal-400"></i> {{ __('Add Course') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('courses.store') }}">
                    @csrf
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        @if($project)
                            <div class="mb-3 rounded-3 border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm text-indigo-700">
                                {{ __('This course will be created under:') }} <strong>{{ $project->name }}</strong>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_course_name" class="form-label fw-semibold text-slate-700 small">{{ __('Course Name (English)') }} *</label>
                                <input id="create_course_name" type="text" name="name" value="{{ old('name') }}" required
                                       class="form-control rounded-3" placeholder="{{ __('e.g. Web Development') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_course_name_bn" class="form-label fw-semibold text-slate-700 small">{{ __('Course Name (Bangla)') }}</label>
                                <input id="create_course_name_bn" type="text" name="name_bn" value="{{ old('name_bn') }}"
                                       class="form-control rounded-3" placeholder="{{ __('Optional') }}">
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 rounded-3 bg-indigo-50 px-3 py-2">
                                    <span class="d-flex align-items-center gap-2 text-[11px] font-bold uppercase tracking-wide text-indigo-700">
                                        <i class="bi bi-table"></i> {{ __('TAM List — Occupational Standard') }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-indigo-500">
                                        {{ __('Nominal hour auto-fills from Occupation + Level') }}
                                    </span>
                                </div>
                                @unless($tamAvailable)
                                    <div class="mt-2 rounded-3 border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-700">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        {{ __('TAM List spreadsheet not found. Place it at docs/TAM List.xlsx or set TAM_LIST_PATH.') }}
                                    </div>
                                @endunless
                            </div>
                            <div class="col-md-6">
                                <label for="create_course_tam_occupation" class="form-label fw-semibold text-slate-700 small">{{ __('Occupation Name') }}</label>
                                <select id="create_course_tam_occupation" name="tam_occupation" class="form-select rounded-3">
                                    <option value="">{{ __('— Select occupation —') }}</option>
                                    @foreach($tamOccupations as $occupation)
                                        <option value="{{ $occupation }}" {{ old('tam_occupation') === $occupation ? 'selected' : '' }}>{{ $occupation }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="create_course_level" class="form-label fw-semibold text-slate-700 small">{{ __('NTVQF Level') }}</label>
                                <select id="create_course_level" name="ntvqf_level" class="form-select rounded-3">
                                    <option value="">{{ __('— Level —') }}</option>
                                    @foreach($tamLevels as $level)
                                        <option value="{{ $level }}" {{ (string) old('ntvqf_level') === (string) $level ? 'selected' : '' }}>{{ __('Level') }} {{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="create_course_sector" class="form-label fw-semibold text-slate-700 small">{{ __('Industrial Sector') }}</label>
                                <select id="create_course_sector" name="tam_sector" class="form-select rounded-3">
                                    <option value="">{{ __('— Auto —') }}</option>
                                    @foreach($tamSectors as $sector)
                                        <option value="{{ $sector }}" {{ old('tam_sector') === $sector ? 'selected' : '' }}>{{ $sector }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="create_course_hours" class="form-label fw-semibold text-slate-700 small">{{ __('Nominal Hour') }}</label>
                                <input id="create_course_hours" type="number" name="duration_hours" min="0" value="{{ old('duration_hours') }}"
                                       class="form-control rounded-3 bg-emerald-50" placeholder="{{ __('Auto') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="create_course_code" class="form-label fw-semibold text-slate-700 small">{{ __('Course Code') }}</label>
                                <input id="create_course_code" type="text" name="course_code" value="{{ old('course_code') }}"
                                       class="form-control rounded-3 font-monospace" placeholder="{{ __('e.g. CRS-001') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_course_duration" class="form-label fw-semibold text-slate-700 small">{{ __('Duration (display)') }}</label>
                                <input id="create_course_duration" type="text" name="duration" value="{{ old('duration') }}"
                                       class="form-control rounded-3" placeholder="{{ __('e.g. 360 hours') }}">
                            </div>
                            <div class="col-12">
                                <div id="create_course_hint" class="form-text text-[11px] text-emerald-600"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="create_course_fee" class="form-label fw-semibold text-slate-700 small">{{ __('Fee (BDT)') }} *</label>
                                <input id="create_course_fee" type="number" name="fee" value="{{ old('fee', 0) }}" min="0" required
                                       class="form-control rounded-3">
                            </div>
                            <div class="col-md-3">
                                <label for="create_course_seats" class="form-label fw-semibold text-slate-700 small">{{ __('Seat Capacity') }} *</label>
                                <input id="create_course_seats" type="number" name="seat_capacity" value="{{ old('seat_capacity', 0) }}" min="0" required
                                       class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <label for="create_course_description" class="form-label fw-semibold text-slate-700 small">{{ __('Description') }}</label>
                                <textarea id="create_course_description" name="description" rows="3"
                                          class="form-control rounded-3" placeholder="{{ __('Course overview...') }}">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Course') }}
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
                    var modal = document.getElementById('createCourseModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
        @include('courses.partials.tam-script', ['prefix' => 'create_course', 'nameFieldId' => 'create_course_name'])
    @endpush
</x-app-layout>
