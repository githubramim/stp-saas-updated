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

                        <div class="mt-6 rounded-xl bg-indigo-50 px-4 py-2 text-xs font-bold uppercase tracking-wide text-indigo-700">
                            <i class="bi bi-table me-1"></i> TAM List — Occupational Standard
                        </div>

                        @unless($tamAvailable)
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                                TAM List spreadsheet not found. Place it at docs/TAM List.xlsx or set TAM_LIST_PATH.
                            </div>
                        @endunless

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <x-input-label for="edit_course_tam_occupation" value="Occupation Name" />
                                <select id="edit_course_tam_occupation" name="tam_occupation"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— Select occupation —</option>
                                    @foreach($tamOccupations as $occupation)
                                        <option value="{{ $occupation }}" {{ old('tam_occupation', $course->tam_occupation) === $occupation ? 'selected' : '' }}>{{ $occupation }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tam_occupation')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="edit_course_level" value="NTVQF Level" />
                                <select id="edit_course_level" name="ntvqf_level"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— Level —</option>
                                    @foreach($tamLevels as $level)
                                        <option value="{{ $level }}" {{ (string) old('ntvqf_level', $course->ntvqf_level) === (string) $level ? 'selected' : '' }}>Level {{ $level }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('ntvqf_level')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="edit_course_sector" value="Industrial Sector" />
                                <select id="edit_course_sector" name="tam_sector"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— Auto —</option>
                                    @foreach($tamSectors as $sector)
                                        <option value="{{ $sector }}" {{ old('tam_sector', $course->tam_sector) === $sector ? 'selected' : '' }}>{{ $sector }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tam_sector')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="edit_course_hours" value="Nominal Hour" />
                                <x-text-input id="edit_course_hours" class="block mt-1 w-full bg-emerald-50" type="number" min="0" name="duration_hours" :value="old('duration_hours', $course->duration_hours)" placeholder="Auto" />
                                <x-input-error :messages="$errors->get('duration_hours')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="edit_course_code" value="Course Code" />
                                <x-text-input id="edit_course_code" class="block mt-1 w-full" type="text" name="course_code" :value="old('course_code', $course->course_code)" />
                                <x-input-error :messages="$errors->get('course_code')" class="mt-2" />
                            </div>
                        </div>
                        <div id="edit_course_hint" class="mt-2 text-[11px] text-emerald-600"></div>

                        <div class="flex items-center justify-end mt-6 gap-3">
                            <a href="{{ route('courses.index') }}" class="btn btn-outline-gray">Cancel</a>
                            <button type="submit" class="btn btn-solid-amber">Update Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('courses.partials.tam-script', ['prefix' => 'edit_course', 'nameFieldId' => 'name'])
    @endpush
</x-app-layout>