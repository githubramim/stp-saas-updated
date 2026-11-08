<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $exam->title }}
            <span class="text-sm font-normal text-gray-400 ml-2">
                — {{ $exam->batch?->name }} · {{ $exam->exam_date->format('d M Y') }}
            </span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('exams.marks.edit', $exam) }}" class="btn btn-solid-indigo">Enter / Edit Marks</a>
                <a href="{{ route('exams.results', $exam) }}" class="btn btn-outline-violet">Merit List</a>
                <a href="{{ route('exams.results.csv', $exam) }}" class="btn btn-outline-emerald">Export CSV</a>

                @if($exam->isPublished())
                    <form method="POST" action="{{ route('exams.revert', $exam) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-amber">Revert to Draft</button>
                    </form>
                @else
                    <a href="{{ route('exams.edit', $exam) }}" class="btn btn-outline-gray">Edit</a>
                    <form method="POST" action="{{ route('exams.publish', $exam) }}">
                        @csrf
                        <button type="submit" class="btn btn-solid-emerald">Publish Results</button>
                    </form>
                    <form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('Delete this exam?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-red">Delete</button>
                    </form>
                @endif

                <a href="{{ route('exams.index') }}" class="btn btn-outline-gray">Back</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-gray-400">
                    <div class="text-xs text-gray-500 uppercase">Students</div>
                    <div class="text-2xl font-semibold text-gray-700">{{ $summary['students'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <div class="text-xs text-gray-500 uppercase">Graded</div>
                    <div class="text-2xl font-semibold text-indigo-600">{{ $summary['graded'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 uppercase">Passed</div>
                    <div class="text-2xl font-semibold text-green-600">{{ $summary['passed'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
                    <div class="text-xs text-gray-500 uppercase">Failed</div>
                    <div class="text-2xl font-semibold text-red-600">{{ $summary['failed'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-500">
                    <div class="text-xs text-gray-500 uppercase">Absent</div>
                    <div class="text-2xl font-semibold text-amber-600">{{ $summary['absent'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-sky-500">
                    <div class="text-xs text-gray-500 uppercase">Average %</div>
                    <div class="text-2xl font-semibold text-sky-600">{{ number_format($summary['average'], 2) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Question Paper ({{ $exam->questions->count() }})</h3>

                        @if($exam->questions->isEmpty())
                            <p class="text-gray-500 text-center py-8">No questions added.</p>
                        @else
                            <ol class="space-y-5">
                                @foreach($exam->questions as $index => $question)
                                    <li class="border-b pb-4 last:border-0">
                                        <div class="flex justify-between gap-4">
                                            <div class="font-medium text-gray-800">{{ $index + 1 }}. {{ $question->question }}</div>
                                            <div class="text-xs text-gray-500 whitespace-nowrap">
                                                {{ number_format((float) $question->marks, 2) }} marks
                                                @if($question->isMcq() && $question->hasNegativeMarking())
                                                    · −{{ number_format((float) $question->negative_marks, 2) }} wrong
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700 uppercase">{{ $question->question_type }}</span>
                                        </div>

                                        @if($question->isMcq())
                                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-1 text-sm">
                                                @foreach($question->options() as $key => $text)
                                                    <div class="{{ $question->correct_option === $key ? 'text-green-700 font-medium' : 'text-gray-600' }}">
                                                        <span class="uppercase font-semibold">{{ $key }}</span>. {{ $text }}
                                                        @if($question->correct_option === $key)
                                                            <span class="text-xs">(correct)</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Summary</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="font-medium uppercase">{{ $exam->exam_type }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Course</dt><dd class="font-medium">{{ $exam->course?->name ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Total marks</dt><dd class="font-medium">{{ number_format((float) $exam->total_marks, 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Pass marks</dt><dd class="font-medium">{{ number_format((float) $exam->pass_marks, 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Duration</dt><dd class="font-medium">{{ $exam->duration_minutes ? $exam->duration_minutes.' min' : '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Highest score</dt><dd class="font-medium">{{ number_format((float) $summary['highest'], 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Status</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs rounded {{ $exam->isPublished() ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        @if($exam->instructions)
                            <div class="mt-4 pt-4 border-t">
                                <h4 class="text-sm font-semibold mb-1">Instructions</h4>
                                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $exam->instructions }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
