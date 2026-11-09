<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                <i class="bi bi-award text-orange-500"></i>
                <span class="truncate">{{ __('New Certificate') }}</span>
            </h2>
            <a href="{{ route('certificates.index') }}" class="btn-outline-gray shrink-0">
                <i class="bi bi-arrow-left"></i>
                {{ __('Back to Certificates') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('certificates.store') }}"
                  class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                @csrf

                <div>
                    <label for="title" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Certificate Title') }} <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" value="{{ old('title', 'Certificate of Completion') }}" required
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="{{ __('Certificate of Completion') }}">
                    @error('title') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="student_id" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Student') }}</label>
                        <select id="student_id" name="student_id"
                                class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('— Select Student —') }}</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                    {{ $student->name_en }} @if($student->reference_no) ({{ $student->reference_no }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('student_id') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="exam_id" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Exam') }}</label>
                        <select id="exam_id" name="exam_id"
                                class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('— Select Exam —') }}</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" @selected(old('exam_id') == $exam->id)>
                                    {{ $exam->title ?? $exam->name ?? ('Exam #' . $exam->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('exam_id') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="issued_date" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Issued Date') }}</label>
                    <input id="issued_date" name="issued_date" type="date" value="{{ old('issued_date', now()->toDateString()) }}"
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('issued_date') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-start gap-3 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-xs text-indigo-700">
                    <i class="bi bi-info-circle mt-0.5 text-base"></i>
                    <p>{{ __('A unique certificate number and verification code are generated automatically when you save.') }}</p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('certificates.index') }}" class="btn-outline-gray">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn-solid-indigo">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ __('Create Certificate') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
