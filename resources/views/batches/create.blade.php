<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Batch') }}
            @if($project)
                <span class="text-sm font-normal text-gray-400 ml-2">— {{ $project->name }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('batches.store') }}">
                        @csrf

                        @if($project)
                            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-md text-sm text-amber-700">
                                This batch will be created under project: <strong>{{ $project->name }}</strong>
                            </div>
                        @endif

                        <div>
                            <x-input-label for="course_id" value="Course *" />
                            <select id="course_id" name="course_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">— Select Course —</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }} ({{ $course->seat_capacity }} seats)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="name" value="Batch Name (English) *" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required placeholder="e.g. Batch 01, 2026-01" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="name_bn" value="Batch Name (Bangla)" />
                            <x-text-input id="name_bn" class="block mt-1 w-full" type="text" name="name_bn" :value="old('name_bn')" />
                            <x-input-error :messages="$errors->get('name_bn')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="shift" value="Shift" />
                                <select id="shift" name="shift" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— Select —</option>
                                    <option value="Morning" {{ old('shift') == 'Morning' ? 'selected' : '' }}>Morning</option>
                                    <option value="Day" {{ old('shift') == 'Day' ? 'selected' : '' }}>Day</option>
                                    <option value="Evening" {{ old('shift') == 'Evening' ? 'selected' : '' }}>Evening</option>
                                </select>
                                <x-input-error :messages="$errors->get('shift')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="seat_capacity" value="Seat Capacity *" />
                                <x-text-input id="seat_capacity" class="block mt-1 w-full" type="number" name="seat_capacity" :value="old('seat_capacity', 0)" min="0" required />
                                <x-input-error :messages="$errors->get('seat_capacity')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="start_date" value="Start Date" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" value="End Date" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-3">
                            <a href="{{ route('batches.index') }}" class="btn btn-outline-gray">Cancel</a>
                            <button type="submit" class="btn btn-solid-emerald">Save Batch</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
