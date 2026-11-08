{{-- Shared schedule form fields. Expects: $batches, $trainers, $schedule (nullable) --}}
<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-md text-sm text-amber-700">
    @if($project)
        This class will be scheduled under project: <strong>{{ $project->name }}</strong>
    @else
        Select a batch from the current project.
    @endif
</div>

@if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-700">
        <strong>Please fix the following errors:</strong>
        <ul class="list-disc list-inside mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="batch_id" value="Batch *" />
        <select id="batch_id" name="batch_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">— Select Batch —</option>
            @foreach($batches as $batch)
                <option value="{{ $batch->id }}" @selected(old('batch_id', $schedule?->batch_id) == $batch->id)>
                    {{ $batch->name }} ({{ $batch->course?->name }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('batch_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="day_of_week" value="Week Day *" />
        <select id="day_of_week" name="day_of_week" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">— Select Day —</option>
            @foreach(\App\Models\Schedule::DAYS as $index => $day)
                <option value="{{ $index }}" @selected((int) old('day_of_week', $schedule?->day_of_week) === $index)>{{ $day }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('day_of_week')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mt-4">
    <div>
        <x-input-label for="start_time" value="Start Time *" />
        <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time', $schedule?->start_time)" required />
        <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="end_time" value="End Time *" />
        <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time', $schedule?->end_time)" required />
        <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mt-4">
    <div>
        <x-input-label for="room" value="Room / Venue" />
        <x-text-input id="room" class="block mt-1 w-full" type="text" name="room" :value="old('room', $schedule?->room)" placeholder="e.g. Room 201, Lab 1" />
        <x-input-error :messages="$errors->get('room')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="trainer_id" value="Trainer" />
        <select id="trainer_id" name="trainer_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— No Trainer Assigned —</option>
            @foreach($trainers as $trainer)
                <option value="{{ $trainer->id }}" @selected(old('trainer_id', $schedule?->trainer_id) == $trainer->id)>
                    {{ $trainer->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('trainer_id')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="subject" value="Subject / Topic" />
    <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject', $schedule?->subject)" placeholder="e.g. PHP Fundamentals" />
    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="remarks" value="Remarks" />
    <textarea id="remarks" name="remarks" rows="2" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional notes">{{ old('remarks', $schedule?->remarks) }}</textarea>
    <x-input-error :messages="$errors->get('remarks')" class="mt-2" />
</div>