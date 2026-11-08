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
                <a href="{{ route('schedules.create') }}" class="btn btn-solid-indigo">+ New Class Schedule</a>
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
                        <p class="text-gray-500 text-center py-8">No classes scheduled yet. <a href="{{ route('schedules.create') }}" class="text-indigo-600 hover:underline">Add the first one →</a></p>
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
        </div>
    </div>
</x-app-layout>