<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enter Marks') }} — {{ $exam->title }}
            <span class="text-sm font-normal text-gray-400 ml-2">— {{ $exam->batch?->name }}</span>
        </h2>
    </x-slot>

    @php
        $mcqPayload = $mcq->map(fn ($q) => [
            'id' => $q->id,
            'marks' => (float) $q->marks,
            'negative' => (float) $q->negative_marks,
            'correct' => $q->correct_option,
        ])->values();

        $rowsPayload = $students->map(function ($student) use ($results, $mcq) {
            $result = $results->get($student->id);
            $answers = [];
            $mcqAwarded = 0.0;

            foreach ($result?->answers ?? [] as $answer) {
                $answers[(string) $answer->exam_question_id] = $answer->given_answer ?? '';

                if ($mcq->contains('id', $answer->exam_question_id)) {
                    $mcqAwarded += (float) $answer->marks_awarded;
                }
            }

            $graded = $result && ! $result->is_absent;

            return [
                'studentId' => $student->id,
                'answers' => $answers,
                'manual' => $graded ? (string) round(max((float) $result->obtained_marks - max($mcqAwarded, 0), 0), 2) : '',
                'absent' => (bool) ($result?->is_absent ?? true),
            ];
        })->values();
    @endphp

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8"
             x-data="marksSheet({
                qs: {{ \Illuminate\Support\Js::from($mcqPayload) }},
                rows: {{ \Illuminate\Support\Js::from($rowsPayload) }},
                manualTotal: {{ (float) $manualTotal }}
             })">
            <form method="POST" action="{{ route('exams.marks.update', $exam) }}">
                @csrf

                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold">Marksheet</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Total marks <strong>{{ number_format((float) $exam->total_marks, 2) }}</strong>,
                            pass marks <strong>{{ number_format((float) $exam->pass_marks, 2) }}</strong>.
                            @if($mcq->isNotEmpty())
                                MCQ total <strong>{{ number_format((float) $exam->mcqTotalMarks(), 2) }}</strong> is auto-graded;
                                enter up to <strong>{{ number_format((float) $manualTotal, 2) }}</strong> for the written/practical portion.
                            @else
                                Enter the total marks awarded per student.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        @if($students->isEmpty())
                            <p class="text-gray-500 text-center py-8">No students in this batch yet.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b bg-gray-50">
                                            <th class="text-left p-3 font-medium">#</th>
                                            <th class="text-left p-3 font-medium">Student</th>
                                            @foreach($mcq as $question)
                                                <th class="text-center p-3 font-medium" title="{{ $question->question }}">
                                                    Q{{ $loop->iteration }}
                                                    <div class="text-gray-400 font-normal">{{ number_format((float) $question->marks, 2) }}m</div>
                                                </th>
                                            @endforeach
                                            <th class="text-center p-3 font-medium">
                                                Manual
                                                <div class="text-gray-400 font-normal">/{{ number_format((float) $manualTotal, 2) }}m</div>
                                            </th>
                            <th class="text-center p-3 font-medium">Total</th>
                                            <th class="text-center p-3 font-medium">Absent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            @php($i = $loop->index)
                                            <tr class="border-b hover:bg-gray-50" :class="rows[{{ $i }}].absent && 'opacity-50'">
                                                <td class="p-3 text-gray-400">{{ $loop->iteration }}</td>
                                                <td class="p-3">
                                                    <div class="font-medium">{{ $student->name_en }}</div>
                                                    <div class="text-gray-500 text-xs font-mono">{{ $student->reference_no }}</div>
                                                </td>

                                                @foreach($mcq as $question)
                                                    <td class="p-3 text-center">
                                                        <select name="answers[{{ $student->id }}][{{ $question->id }}]"
                                                                x-model="rows[{{ $i }}].answers['{{ $question->id }}']"
                                                                class="rounded-md border-gray-300 shadow-sm text-xs py-1 focus:border-indigo-500 focus:ring-indigo-500">
                                                            <option value="">—</option>
                                                            @foreach(['a', 'b', 'c', 'd'] as $opt)
                                                                <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                @endforeach

                                                <td class="p-3">
                                                    <input type="number" step="0.25" min="0" :max="manualTotal"
                                                           name="manual[{{ $student->id }}]"
                                                           x-model="rows[{{ $i }}].manual"
                                                           class="w-20 rounded-md border-gray-300 shadow-sm text-sm text-center focus:border-indigo-500 focus:ring-indigo-500" />
                                                </td>

                                                <td class="p-3 text-center font-semibold" x-text="rowTotal({{ $i }}).toFixed(2)"></td>

                                                <td class="p-3 text-center">
                                                    <input type="checkbox" x-model="rows[{{ $i }}].absent"
                                                           class="text-red-600 focus:ring-red-500" />
                                                    <input type="hidden" name="absent[{{ $student->id }}]"
                                                           :value="rows[{{ $i }}].absent ? 1 : ''" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex flex-wrap justify-between items-center mt-6 gap-3">
                                <p class="text-sm text-gray-500">
                                    Ticking <strong>Absent</strong> overrides any marks for that student.
                                    <button type="button" @click="rows.forEach(r => r.absent = false)" class="text-indigo-600 underline ml-1">
                                        Unmark all absent
                                    </button>
                                </p>
                                <div class="flex gap-3">
                                    <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-gray">Cancel</a>
                                    <button type="submit" class="btn btn-solid-emerald">Save Marks</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('marksSheet', ({ qs, rows, manualTotal }) => ({
                qs,
                rows,
                manualTotal,
                rowTotal(index) {
                    const row = this.rows[index];
                    if (!row || row.absent) return 0;

                    let total = 0;

                    this.qs.forEach((q) => {
                        const given = row.answers[String(q.id)] || '';
                        if (!given) return;
                        total += given.toLowerCase() === String(q.correct).toLowerCase()
                            ? q.marks
                            : -q.negative;
                    });

                    const manual = parseFloat(row.manual);
                    if (!isNaN(manual) && manual > 0) {
                        total += Math.min(manual, this.manualTotal);
                    }

                    return Math.max(total, 0);
                },
            }));
        });
    </script>
</x-app-layout>
