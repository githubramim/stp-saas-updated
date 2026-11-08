<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Exams & Results') }}
            @if($project)
                <span class="text-sm font-normal text-gray-400 ml-2">— {{ $project->name }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
            @endif

            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('exams.create') }}" class="btn btn-solid-indigo">+ New Exam</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('exams.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="batch_id" value="Batch" />
                            <select id="batch_id" name="batch_id" class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Batches</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(request('batch_id') == $batch->id)>{{ $batch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('exams.index') }}" class="btn btn-outline-gray">Reset</a>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">All Exams</h3>

                    @if($exams->isEmpty())
                        <p class="text-gray-500 text-center py-8">No exams yet. Create your first exam!</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Exam</th>
                                        <th class="text-left p-3 font-medium">Batch</th>
                                        <th class="text-left p-3 font-medium">Date</th>
                                        <th class="text-left p-3 font-medium">Type</th>
                                        <th class="text-center p-3 font-medium">Marks</th>
                                        <th class="text-center p-3 font-medium">Questions</th>
                                        <th class="text-center p-3 font-medium">Graded</th>
                                        <th class="text-left p-3 font-medium">Status</th>
                                        <th class="text-left p-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exams as $exam)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3">
                                                <div class="font-medium">{{ $exam->title }}</div>
                                                @if($exam->title_bn)
                                                    <div class="text-gray-500 text-xs">{{ $exam->title_bn }}</div>
                                                @endif
                                            </td>
                                            <td class="p-3">{{ $exam->batch?->name ?? '—' }}</td>
                                            <td class="p-3 whitespace-nowrap">{{ $exam->exam_date->format('d M Y') }}</td>
                                            <td class="p-3"><span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700 uppercase">{{ $exam->exam_type }}</span></td>
                                            <td class="p-3 text-center">{{ number_format((float) $exam->total_marks, 2) }}</td>
                                            <td class="p-3 text-center">{{ $exam->questions_count }}</td>
                                            <td class="p-3 text-center">{{ $exam->results_count }}</td>
                                            <td class="p-3">
                                                <span class="px-2 py-1 text-xs rounded {{ $exam->isPublished() ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ ucfirst($exam->status) }}
                                                </span>
                                            </td>
                                            <td class="p-3 flex flex-wrap gap-2">
                                                <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-indigo">View</a>
                                                <a href="{{ route('exams.marks.edit', $exam) }}" class="btn btn-outline-amber">Marks</a>
                                                <a href="{{ route('exams.results', $exam) }}" class="btn btn-outline-violet">Results</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">{{ $exams->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
