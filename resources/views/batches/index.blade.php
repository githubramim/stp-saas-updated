<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Batches') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Class batches, shifts and seat capacity.') }}</p>
            </div>
            <button type="button" class="btn-solid-indigo" data-bs-toggle="modal" data-bs-target="#createBatchModal" aria-label="{{ __('Add Batch') }}">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">{{ __('Add Batch') }}</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @if($batches->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                        <i class="bi bi-people text-2xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-800">{{ __('No batches yet') }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ __('Create a batch under a course to begin admitting students.') }}</p>
                    <button type="button" class="btn-solid-indigo mt-5" data-bs-toggle="modal" data-bs-target="#createBatchModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('Add Batch') }}</span>
                    </button>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[860px] text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/80 text-left text-[11px] uppercase tracking-wide text-slate-400">
                                    <th class="px-5 py-3 font-semibold">{{ __('Batch') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Course') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Shift') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Duration') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Seats') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-right font-semibold">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($batches as $b)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $b->name }}</td>
                                        <td class="px-5 py-4 text-slate-500">{{ $b->course?->name ?? '—' }}</td>
                                        <td class="px-5 py-4 text-slate-500">{{ $b->shift ?? '—' }}</td>
                                        <td class="px-5 py-4 text-xs text-slate-500">
                                            {{ $b->start_date?->format('d M Y') }}
                                            @if($b->end_date)
                                                <span class="text-slate-300">→</span> {{ $b->end_date->format('d M Y') }}
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-slate-500">{{ $b->seat_capacity }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $b->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                                {{ ucfirst($b->status) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('batches.edit', $b) }}" class="btn-outline-gray">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>{{ __('Edit') }}</span>
                                                </a>
                                                <form method="POST" action="{{ route('batches.destroy', $b) }}" onsubmit="return confirm('Delete this batch?')">
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
    {{-- Create Batch Modal --}}
    <div class="modal fade" id="createBatchModal" tabindex="-1" aria-labelledby="createBatchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createBatchModalLabel">
                        <i class="bi bi-people text-violet-400"></i> {{ __('Add Batch') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('batches.store') }}">
                    @csrf
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        @if($project)
                            <div class="mb-3 rounded-3 border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                                {{ __('This batch will be created under:') }} <strong>{{ $project->name }}</strong>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_batch_course" class="form-label fw-semibold text-slate-700 small">{{ __('Course') }} *</label>
                                <select id="create_batch_course" name="course_id" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Course') }} —</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                                            {{ $course->name }} ({{ $course->seat_capacity }} {{ __('seats') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_batch_name" class="form-label fw-semibold text-slate-700 small">{{ __('Batch Name (English)') }} *</label>
                                <input id="create_batch_name" type="text" name="name" value="{{ old('name') }}" required
                                       class="form-control rounded-3" placeholder="{{ __('e.g. Batch 01, 2026-01') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_batch_name_bn" class="form-label fw-semibold text-slate-700 small">{{ __('Batch Name (Bangla)') }}</label>
                                <input id="create_batch_name_bn" type="text" name="name_bn" value="{{ old('name_bn') }}"
                                       class="form-control rounded-3" placeholder="{{ __('Optional') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="create_batch_shift" class="form-label fw-semibold text-slate-700 small">{{ __('Shift') }}</label>
                                <select id="create_batch_shift" name="shift" class="form-select rounded-3">
                                    <option value="">— {{ __('Select') }} —</option>
                                    @foreach(['Morning', 'Day', 'Evening'] as $shift)
                                        <option value="{{ $shift }}" @selected(old('shift') === $shift)>{{ $shift }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="create_batch_seats" class="form-label fw-semibold text-slate-700 small">{{ __('Seat Capacity') }} *</label>
                                <input id="create_batch_seats" type="number" name="seat_capacity" value="{{ old('seat_capacity', 0) }}" min="0" required
                                       class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_batch_start" class="form-label fw-semibold text-slate-700 small">{{ __('Start Date') }}</label>
                                <input id="create_batch_start" type="date" name="start_date" value="{{ old('start_date') }}"
                                       class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_batch_end" class="form-label fw-semibold text-slate-700 small">{{ __('End Date') }}</label>
                                <input id="create_batch_end" type="date" name="end_date" value="{{ old('end_date') }}"
                                       class="form-control rounded-3">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Batch') }}
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
                    var modal = document.getElementById('createBatchModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
