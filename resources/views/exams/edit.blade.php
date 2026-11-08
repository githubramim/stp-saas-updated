<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Exam') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8"
             x-data="examBuilder({ questions: {{ \Illuminate\Support\Js::from($exam->questions) }} })">
            <form method="POST" action="{{ route('exams.update', $exam) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-6">Exam Details</h3>

                        <div>
                            <x-input-label for="batch_id" value="Batch *" />
                            <select id="batch_id" name="batch_id" required
                                    class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select batch</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(old('batch_id', $exam->batch_id) == $batch->id)>
                                        {{ $batch->name }} — {{ $batch->course?->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('batch_id')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="title" value="Exam Title (English) *" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                              :value="old('title', $exam->title)" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="title_bn" value="Exam Title (Bangla)" />
                                <x-text-input id="title_bn" class="block mt-1 w-full" type="text" name="title_bn" :value="old('title_bn', $exam->title_bn)" />
                                <x-input-error :messages="$errors->get('title_bn')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                            <div>
                                <x-input-label for="exam_date" value="Exam Date *" />
                                <x-text-input id="exam_date" class="block mt-1 w-full" type="date" name="exam_date"
                                              :value="old('exam_date', $exam->exam_date->toDateString())" required />
                                <x-input-error :messages="$errors->get('exam_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="duration_minutes" value="Duration (minutes)" />
                                <x-text-input id="duration_minutes" class="block mt-1 w-full" type="number" name="duration_minutes"
                                              :value="old('duration_minutes', $exam->duration_minutes)" min="1" max="600" />
                                <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="pass_marks" value="Pass Marks" />
                                <x-text-input id="pass_marks" class="block mt-1 w-full" type="number" name="pass_marks"
                                              :value="old('pass_marks', (float) $exam->pass_marks)" min="0" step="0.25" />
                                <x-input-error :messages="$errors->get('pass_marks')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="instructions" value="Instructions" />
                            <textarea id="instructions" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      name="instructions" rows="3">{{ old('instructions', $exam->instructions) }}</textarea>
                            <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Questions</h3>
                            <button type="button" @click="addQuestion()" class="btn btn-outline-indigo">+ Add Question</button>
                        </div>

                        <x-input-error :messages="$errors->get('questions')" class="mb-4" />

                        <template x-for="(q, i) in questions" :key="i">
                            <div class="border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="font-medium text-sm text-gray-700">Question <span x-text="i + 1"></span></span>
                                    <div class="flex items-center gap-2">
                                        <select x-model="q.question_type" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="written">Written</option>
                                            <option value="practical">Practical</option>
                                            <option value="mcq">MCQ</option>
                                        </select>
                                        <button type="button" @click="removeQuestion(i)" x-show="questions.length > 1"
                                                class="btn btn-outline-red">Remove</button>
                                    </div>
                                </div>

                                <textarea :name="'questions[' + i + '][question]'" x-model="q.question" rows="2" required
                                          class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>

                                <div x-show="q.question_type === 'mcq'" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-for="opt in ['a','b','c','d']" :key="opt">
                                        <div class="flex items-center gap-2">
                                            <span class="uppercase text-xs font-semibold text-gray-500 w-4" x-text="opt"></span>
                                            <input type="text" :name="'questions[' + i + '][option_' + opt + ']'" x-model="q['option_' + opt]"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        </div>
                                    </template>
                                    <div class="flex items-center gap-2 sm:col-span-2">
                                        <span class="text-xs font-semibold text-gray-500">Correct answer</span>
                                        <select :name="'questions[' + i + '][correct_option]'" x-model="q.correct_option"
                                                class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="a">A</option>
                                            <option value="b">B</option>
                                            <option value="c">C</option>
                                            <option value="d">D</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-500">Marks</span>
                                        <input type="number" step="0.25" min="0.25" :name="'questions[' + i + '][marks]'" x-model="q.marks"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                    <div x-show="q.question_type === 'mcq'" class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-500">Negative marks</span>
                                        <input type="number" step="0.25" min="0" :name="'questions[' + i + '][negative_marks]'" x-model="q.negative_marks"
                                               class="w-24 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                    <input type="hidden" :name="'questions[' + i + '][question_type]'" :value="q.question_type" />
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-between items-center pt-2">
                            <div class="text-sm text-gray-600">Total marks: <strong x-text="total().toFixed(2)"></strong></div>
                            <div class="flex gap-3">
                                <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-gray">Cancel</a>
                                <button type="submit" class="btn btn-solid-emerald">Update Exam</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('examBuilder', ({ questions }) => ({
                questions: questions.map((q) => ({
                    question: q.question,
                    question_type: q.question_type,
                    option_a: q.option_a ?? '',
                    option_b: q.option_b ?? '',
                    option_c: q.option_c ?? '',
                    option_d: q.option_d ?? '',
                    correct_option: q.correct_option ?? 'a',
                    marks: parseFloat(q.marks) || 0,
                    negative_marks: parseFloat(q.negative_marks) || 0,
                })),
                addQuestion() {
                    this.questions.push({
                        question: '', question_type: 'written',
                        option_a: '', option_b: '', option_c: '', option_d: '',
                        correct_option: 'a', marks: 10, negative_marks: 0.25,
                    });
                },
                removeQuestion(index) {
                    this.questions.splice(index, 1);
                },
                total() {
                    return this.questions.reduce((sum, q) => sum + (parseFloat(q.marks) || 0), 0);
                },
            }));
        });
    </script>
</x-app-layout>
