<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Exams & Results') }}</h2>
                <p class="text-xs text-slate-400">
                    @if($project)
                        {{ __('Exam schedules and results for') }} {{ $project->name }}
                    @else
                        {{ __('Exam schedules, questions and results.') }}
                    @endif
                </p>
            </div>
            <button type="button" class="btn-solid-indigo shrink-0" data-bs-toggle="modal" data-bs-target="#createExamModal">
                <i class="bi bi-plus-circle-fill"></i>
                <span>{{ __('New Exam') }}</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('exams.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[200px] flex-1 sm:flex-none">
                        <label for="exam_filter_batch" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Batch') }}</label>
                        <select id="exam_filter_batch" name="batch_id"
                                class="w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All Batches') }}</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" @selected(request('batch_id') == $batch->id)>{{ $batch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-solid-indigo">
                        <i class="bi bi-funnel"></i>
                        <span>{{ __('Filter') }}</span>
                    </button>
                    <a href="{{ route('exams.index') }}" class="btn-outline-gray">{{ __('Reset') }}</a>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">{{ __('All Exams') }}</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $exams->total() }}</span>
                </div>

                @if($exams->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <i class="bi bi-clipboard2-check text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-600">{{ __('No exams yet.') }}</p>
                        <button type="button" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700"
                                data-bs-toggle="modal" data-bs-target="#createExamModal">
                            {{ __('Create your first exam') }} &rarr;
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-sm">
                            <thead>
                                <tr class="border-b bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="p-3 font-semibold">{{ __('Exam') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Batch') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Date') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Type') }}</th>
                                    <th class="p-3 text-center font-semibold">{{ __('Marks') }}</th>
                                    <th class="p-3 text-center font-semibold">{{ __('Questions') }}</th>
                                    <th class="p-3 text-center font-semibold">{{ __('Graded') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Status') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exams as $exam)
                                    <tr class="border-b border-slate-100 transition hover:bg-slate-50/70">
                                        <td class="p-3">
                                            <div class="font-semibold text-slate-700">{{ $exam->title }}</div>
                                            @if($exam->title_bn)
                                                <div class="text-xs text-slate-400">{{ $exam->title_bn }}</div>
                                            @endif
                                        </td>
                                        <td class="p-3 text-slate-600">{{ $exam->batch?->name ?? '—' }}</td>
                                        <td class="whitespace-nowrap p-3 text-slate-600">{{ $exam->exam_date->format('d M Y') }}</td>
                                        <td class="p-3">
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold uppercase text-slate-600">{{ $exam->exam_type }}</span>
                                        </td>
                                        <td class="p-3 text-center text-slate-600">{{ number_format((float) $exam->total_marks, 2) }}</td>
                                        <td class="p-3 text-center text-slate-600">{{ $exam->questions_count }}</td>
                                        <td class="p-3 text-center text-slate-600">{{ $exam->results_count }}</td>
                                        <td class="p-3">
                                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $exam->isPublished() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ ucfirst($exam->status) }}
                                            </span>
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('exams.show', $exam) }}" class="btn-outline-indigo">{{ __('View') }}</a>
                                                <a href="{{ route('exams.marks.edit', $exam) }}" class="btn-outline-amber">{{ __('Marks') }}</a>
                                                <a href="{{ route('exams.results', $exam) }}" class="btn-outline-violet">{{ __('Results') }}</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 px-5 py-4">{{ $exams->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Exam Modal --}}
    <div class="modal fade" id="createExamModal" tabindex="-1" aria-labelledby="createExamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createExamModalLabel">
                        <i class="bi bi-clipboard2-plus text-indigo-400"></i> {{ __('Create Exam') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('exams.store') }}">
                    @csrf
                    <div class="modal-body p-4"
                         x-data="examBuilder({ questions: [{ type: 'written', marks: 10, negative: '0.25', text: '', a: '', b: '', c: '', d: '', correct: 'a' }] })">
                        @if($errors->any())
                            <div class="mb-3 rounded-3 border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                <strong>{{ __('Please fix the following errors:') }}</strong>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h6 class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('Exam Details') }}</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_exam_batch" class="form-label fw-semibold text-slate-700 small">{{ __('Batch') }} *</label>
                                <select id="create_exam_batch" name="batch_id" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Batch') }} —</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected(old('batch_id') == $batch->id)>
                                            {{ $batch->name }} — {{ $batch->course?->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_exam_title" class="form-label fw-semibold text-slate-700 small">{{ __('Exam Title (English)') }} *</label>
                                <input id="create_exam_title" type="text" name="title" value="{{ old('title') }}" required
                                       class="form-control rounded-3" placeholder="{{ __('e.g. Mid-term Examination') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_exam_title_bn" class="form-label fw-semibold text-slate-700 small">{{ __('Exam Title (Bangla)') }}</label>
                                <input id="create_exam_title_bn" type="text" name="title_bn" value="{{ old('title_bn') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-2">
                                <label for="create_exam_date" class="form-label fw-semibold text-slate-700 small">{{ __('Exam Date') }} *</label>
                                <input id="create_exam_date" type="date" name="exam_date" value="{{ old('exam_date', now()->toDateString()) }}" required class="form-control rounded-3">
                            </div>
                            <div class="col-md-2">
                                <label for="create_exam_duration" class="form-label fw-semibold text-slate-700 small">{{ __('Duration (min)') }}</label>
                                <input id="create_exam_duration" type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1" max="600" class="form-control rounded-3">
                            </div>
                            <div class="col-md-2">
                                <label for="create_exam_pass" class="form-label fw-semibold text-slate-700 small">{{ __('Pass Marks') }}</label>
                                <input id="create_exam_pass" type="number" name="pass_marks" value="{{ old('pass_marks') }}" min="0" step="0.25"
                                       placeholder="{{ __('Auto (40%)') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <label for="create_exam_instructions" class="form-label fw-semibold text-slate-700 small">{{ __('Instructions') }}</label>
                                <textarea id="create_exam_instructions" name="instructions" rows="2" class="form-control rounded-3">{{ old('instructions') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                            <h6 class="mb-0 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('Questions') }}</h6>
                            <button type="button" @click="addQuestion()" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Add Question') }}
                            </button>
                        </div>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ __('Total marks are calculated automatically. MCQ questions are auto-graded (with negative marking); written and practical questions use a manual total.') }}
                        </p>

                        <template x-for="(q, i) in questions" :key="i">
                            <div class="mt-3 rounded-3 border border-slate-200 bg-slate-50 p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-slate-600">{{ __('Question') }} <span x-text="i + 1"></span></span>
                                    <div class="d-flex align-items-center gap-2">
                                        <select x-model="q.type" class="form-select form-select-sm rounded-3" style="width:auto;">
                                            <option value="written">{{ __('Written') }}</option>
                                            <option value="practical">{{ __('Practical') }}</option>
                                            <option value="mcq">{{ __('MCQ') }}</option>
                                        </select>
                                        <button type="button" @click="removeQuestion(i)" x-show="questions.length > 1"
                                                class="btn btn-sm btn-outline-danger rounded-3">{{ __('Remove') }}</button>
                                    </div>
                                </div>

                                <textarea :name="'questions[' + i + '][question]'" x-model="q.text" rows="2" required
                                          placeholder="{{ __('Enter the question') }}"
                                          class="form-control form-control-sm rounded-3"></textarea>

                                <div x-show="q.type === 'mcq'" class="row g-2 mt-1">
                                    <template x-for="opt in ['a','b','c','d']" :key="opt">
                                        <div class="col-md-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text text-uppercase fw-bold" x-text="opt"></span>
                                                <input type="text" :name="'questions[' + i + '][option_' + opt + ']'" x-model="q[opt]"
                                                       :placeholder="'{{ __('Option') }} ' + opt.toUpperCase()"
                                                       class="form-control rounded-3">
                                            </div>
                                        </div>
                                    </template>
                                    <div class="col-12 d-flex align-items-center gap-2">
                                        <span class="small fw-semibold text-slate-500">{{ __('Correct answer') }}</span>
                                        <select :name="'questions[' + i + '][correct_option]'" x-model="q.correct"
                                                class="form-select form-select-sm rounded-3" style="width:auto;">
                                            <option value="a">A</option>
                                            <option value="b">B</option>
                                            <option value="c">C</option>
                                            <option value="d">D</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-2 d-flex flex-wrap align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold text-slate-500">{{ __('Marks') }}</span>
                                        <input type="number" step="0.25" min="0.25" :name="'questions[' + i + '][marks]'" x-model="q.marks"
                                               class="form-control form-control-sm rounded-3" style="width:6rem;">
                                    </div>
                                    <div x-show="q.type === 'mcq'" class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold text-slate-500">{{ __('Negative marks') }}</span>
                                        <input type="number" step="0.25" min="0" :name="'questions[' + i + '][negative_marks]'" x-model="q.negative"
                                               class="form-control form-control-sm rounded-3" style="width:6rem;">
                                    </div>
                                    <input type="hidden" :name="'questions[' + i + '][question_type]'" :value="q.type">
                                </div>
                            </div>
                        </template>

                        <div class="mt-4 rounded-3 border border-indigo-100 bg-indigo-50 px-3 py-2 small text-indigo-700">
                            {{ __('Total marks:') }} <strong x-text="total().toFixed(2)"></strong>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save as Draft') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('examBuilder', ({ questions }) => ({
                    questions,
                    addQuestion() {
                        this.questions.push({ type: 'written', marks: 10, negative: '0.25', text: '', a: '', b: '', c: '', d: '', correct: 'a' });
                    },
                    removeQuestion(index) {
                        this.questions.splice(index, 1);
                    },
                    total() {
                        return this.questions.reduce((sum, q) => sum + (parseFloat(q.marks) || 0), 0);
                    },
                }));
            });

            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() || request('create'))
                    var examModal = document.getElementById('createExamModal');
                    if (examModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(examModal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
