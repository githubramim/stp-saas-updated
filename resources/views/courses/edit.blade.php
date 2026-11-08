<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Course') }}
            @if($project)
                <span class="text-sm font-normal text-gray-400 ml-2">— {{ $project->name }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('courses.update', $course) }}">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="name" value="Course Name (English) *" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $course->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="name_bn" value="Course Name (Bangla)" />
                            <x-text-input id="name_bn" class="block mt-1 w-full" type="text" name="name_bn" :value="old('name_bn', $course->name_bn)" />
                            <x-input-error :messages="$errors->get('name_bn')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="duration" value="Duration" />
                            <x-text-input id="duration" class="block mt-1 w-full" type="text" name="duration" :value="old('duration', $course->duration)" placeholder="e.g. 6 months" />
                            <x-input-error :messages="$errors->get('duration')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="fee" value="Fee (BDT) *" />
                                <x-text-input id="fee" class="block mt-1 w-full" type="number" name="fee" :value="old('fee', $course->fee)" min="0" required />
                                <x-input-error :messages="$errors->get('fee')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="seat_capacity" value="Seat Capacity *" />
                                <x-text-input id="seat_capacity" class="block mt-1 w-full" type="number" name="seat_capacity" :value="old('seat_capacity', $course->seat_capacity)" min="0" required />
                                <x-input-error :messages="$errors->get('seat_capacity')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" name="description" rows="3">{{ old('description', $course->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-3">
                            <a href="{{ route('courses.index') }}" class="btn btn-outline-gray">Cancel</a>
                            <button type="submit" class="btn btn-solid-amber">Update Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>