<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Class Routine') }}
            @if($project)
                <span class="text-sm font-normal text-gray-400 ml-2">— {{ $project->name }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2 mb-6">
                <button type="button" class="btn btn-solid-indigo" data-bs-toggle="modal" data-bs-target="#createScheduleModal">+ New Class Schedule</button>
                <a href="{{ route('schedules.print', request()->query()) }}" class="btn btn-outline-indigo" target="_blank">🖨 Print Routine</a>
            </div>

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('schedules.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="batch_id" value="Batch" />
                            <select id="batch_id" name="batch_id" class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Batches</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(request('batch_id') == $batch->id)>{{ $batch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="trainer_id" value="Trainer" />
                            <select id="trainer_id" name="trainer_id" class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Trainers</option>
                                @foreach($trainers as $trainer)
                                    <option value="{{ $trainer->id }}" @selected(request('trainer_id') == $trainer->id)>{{ $trainer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="room" value="Room" />
                            <select id="room" name="room" class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Rooms</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room }}" @selected(request('room') == $room)>{{ $room }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('schedules.index') }}" class="btn btn-outline-gray">Reset</a>
                    </form>
                </div>
            </div>

            {{-- Conflict alert --}}
            @if($conflicts)
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded flex items-start gap-3">
                    <span class="text-xl">⚠️</span>
                    <div>
                        <strong>Clock clash detected!</strong> Highlighted classes share the same time slot with the same
                        room, trainer or batch. Please reschedule them.
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($schedules->isEmpty())
                        <p class="text-gray-500 text-center py-8">No classes scheduled yet. <button type="button" data-bs-toggle="modal" data-bs-target="#createScheduleModal" class="text-indigo-600 hover:underline">Add the first one</button></p>
                    @else
                        <div class="overflow-x-auto">
                            <div class="grid grid-cols-7 gap-3 min-w-[980px]">
                                @foreach(\App\Models\Schedule::DAYS as $dayIndex => $dayName)
                                    <div class="rounded-lg border border-gray-200 bg-gray-50/60">
                                        <div class="text-center font-semibold text-sm py-2 border-b border-gray-200 {{ $dayIndex === 6 ? 'bg-red-100 text-red-700' : 'bg-indigo-100 text-indigo-700' }} rounded-t-lg">
                                            {{ $dayName }}
                                            @if($dayIndex === 6)<span class="ml-1 text-xs">(Holiday)</span>@endif
                                        </div>
                                        <div class="p-2 space-y-2 min-h-[120px]">
                                            @forelse($week[$dayIndex] as $schedule)
                                                @php $isClash = in_array($schedule->id, $conflicts); @endphp
                                                <div class="rounded-md border {{ $isClash ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-white' }} p-2 text-xs shadow-sm relative">
                                                    @if($isClash)
                                                        <span class="absolute top-1 right-1 text-red-500 font-bold" title="Clock clash">⚠️</span>
                                                    @endif
                                                    <div class="font-semibold text-gray-800">
                                                        {{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('h:i A') }} – {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                                    </div>
                                                    <div class="text-gray-700">{{ $schedule->subject ?: $schedule->batch?->name }}</div>
                                                    <div class="text-gray-400">{{ $schedule->batch?->name }}{{ $schedule->batch?->course ? ' · '.$schedule->batch->course->name : '' }}</div>
                                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                                        @if($schedule->room)
                                                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">🏠 {{ $schedule->room }}</span>
                                                        @endif
                                                        @if($schedule->trainer)
                                                            <span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-700">👤 {{ $schedule->trainer->name }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex gap-2 mt-2">
                                                        <a href="{{ route('schedules.edit', $schedule) }}" class="text-indigo-600 hover:underline">Edit</a>
                                                        <form action="{{ route('schedules.destroy', $schedule) }}" method="POST" class="inline"
                                                              onsubmit="return confirm('Remove this class from the routine?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center text-gray-300 text-xs pt-6">—</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
    {{-- Create Schedule Modal --}}
    <div class="modal fade" id="createScheduleModal" tabindex="-1" aria-labelledby="createScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createScheduleModalLabel">
                        <i class="bi bi-clock-history text-cyan-400"></i> {{ __('Add Class Schedule') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('schedules.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="mb-3 rounded-3 border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                            @if($project)
                                {{ __('This class will be scheduled under:') }} <strong>{{ $project->name }}</strong>
                            @else
                                {{ __('Select a batch from the current project.') }}
                            @endif
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_schedule_batch" class="form-label fw-semibold text-slate-700 small">{{ __('Batch') }} *</label>
                                <select id="create_schedule_batch" name="batch_id" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Batch') }} —</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected(old('batch_id') == $batch->id)>
                                            {{ $batch->name }} @if($batch->course) ({{ $batch->course->name }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_schedule_day" class="form-label fw-semibold text-slate-700 small">{{ __('Week Day') }} *</label>
                                <select id="create_schedule_day" name="day_of_week" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Day') }} —</option>
                                    @foreach(\App\Models\Schedule::DAYS as $index => $day)
                                        <option value="{{ $index }}" @selected((string) old('day_of_week') === (string) $index)>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_schedule_start" class="form-label fw-semibold text-slate-700 small">{{ __('Start Time') }} *</label>
                                <input id="create_schedule_start" type="time" name="start_time" value="{{ old('start_time') }}" required class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_schedule_end" class="form-label fw-semibold text-slate-700 small">{{ __('End Time') }} *</label>
                                <input id="create_schedule_end" type="time" name="end_time" value="{{ old('end_time') }}" required class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="create_schedule_room" class="form-label fw-semibold text-slate-700 small">{{ __('Room / Venue') }}</label>
                                <input id="create_schedule_room" type="text" name="room" value="{{ old('room') }}" class="form-control rounded-3" placeholder="{{ __('e.g. Room 201, Lab 1') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_schedule_trainer" class="form-label fw-semibold text-slate-700 small">{{ __('Trainer') }}</label>
                                <select id="create_schedule_trainer" name="trainer_id" class="form-select rounded-3">
                                    <option value="">— {{ __('No Trainer Assigned') }} —</option>
                                    @foreach($trainers as $trainer)
                                        <option value="{{ $trainer->id }}" @selected(old('trainer_id') == $trainer->id)>{{ $trainer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="create_schedule_subject" class="form-label fw-semibold text-slate-700 small">{{ __('Subject / Topic') }}</label>
                                <input id="create_schedule_subject" type="text" name="subject" value="{{ old('subject') }}" class="form-control rounded-3" placeholder="{{ __('e.g. PHP Fundamentals') }}">
                            </div>
                            <div class="col-12">
                                <label for="create_schedule_remarks" class="form-label fw-semibold text-slate-700 small">{{ __('Remarks') }}</label>
                                <textarea id="create_schedule_remarks" name="remarks" rows="2" class="form-control rounded-3">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Schedule') }}
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
                    var modal = document.getElementById('createScheduleModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>