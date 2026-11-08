<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Attendance Session') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('attendance.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="batch_id" value="Batch *" />
                            <select id="batch_id" name="batch_id" required
                                    class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select batch</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(old('batch_id') == $batch->id)>
                                        {{ $batch->name }} — {{ $batch->course?->name }}
                                        ({{ \App\Models\Student::where('batch_id', $batch->id)->count() }} students)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('batch_id')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="title" value="Session Title (English) *" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                              :value="old('title')" placeholder="e.g. Web Development - Day 3" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="title_bn" value="Session Title (Bangla)" />
                                <x-text-input id="title_bn" class="block mt-1 w-full" type="text" name="title_bn" :value="old('title_bn')" />
                                <x-input-error :messages="$errors->get('title_bn')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                            <div>
                                <x-input-label for="session_date" value="Date *" />
                                <x-text-input id="session_date" class="block mt-1 w-full" type="date" name="session_date"
                                              :value="old('session_date', now()->toDateString())" required />
                                <x-input-error :messages="$errors->get('session_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="start_time" value="Start Time" />
                                <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time')" />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_time" value="End Time" />
                                <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time')" />
                                <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="late_after_minutes" value="Mark Late After (minutes)" />
                            <x-text-input id="late_after_minutes" class="block mt-1 w-full" type="number" name="late_after_minutes"
                                          :value="old('late_after_minutes', 10)" min="0" max="240" />
                            <p class="text-xs text-gray-500 mt-1">
                                Students scanning the QR after start time + this grace window are recorded as LATE.
                            </p>
                            <x-input-error :messages="$errors->get('late_after_minutes')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="remarks" value="Remarks" />
                            <textarea id="remarks" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      name="remarks" rows="2">{{ old('remarks') }}</textarea>
                            <x-input-error :messages="$errors->get('remarks')" class="mt-2" />
                        </div>

                        <div class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded-md text-sm text-indigo-700">
                            A unique QR code will be generated automatically. Students of the batch can scan it from their
                            mobile to mark their own attendance, and you can still override anyone manually.
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-3">
                            <a href="{{ route('attendance.index') }}" class="btn btn-outline-gray">Cancel</a>
                            <button type="submit" class="btn btn-solid-emerald">Create Session</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
