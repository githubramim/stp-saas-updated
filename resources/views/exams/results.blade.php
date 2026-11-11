<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Merit List') }} — {{ $exam->title }}
            <span class="text-sm font-normal text-gray-400 ml-2">— {{ $exam->batch?->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
            @endif

            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('exams.marks.edit', $exam) }}" class="btn btn-outline-amber">Edit Marks</a>
                <a href="{{ route('exams.results.csv', $exam) }}" class="btn btn-outline-emerald">Export CSV</a>
                <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-gray">Exam Detail</a>
                <a href="{{ route('exams.index') }}" class="btn btn-outline-gray">Back</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-gray-400">
                    <div class="text-xs text-gray-500 uppercase">Students</div>
                    <div class="text-2xl font-semibold text-gray-700">{{ $summary['total'] }}</div>
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
                    <div class="text-xs text-gray-500 uppercase">Pass Rate</div>
                    <div class="text-2xl font-semibold text-sky-600">{{ number_format($summary['pass_rate'], 1) }}%</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Results</h3>
                        <span class="px-2 py-1 text-xs rounded {{ $exam->isPublished() ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ ucfirst($exam->status) }}
                        </span>
                    </div>

                    @if($results->isEmpty())
                        <p class="text-gray-500 text-center py-8">
                            No marks entered yet.
                            <a href="{{ route('exams.marks.edit', $exam) }}" class="text-indigo-600 underline">Enter marks</a> to get started.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm datatable" data-dt-page-length="25">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-center p-3 font-medium">Merit</th>
                                        <th class="text-left p-3 font-medium">Registration No</th>
                                        <th class="text-left p-3 font-medium">Student</th>
                                        <th class="text-center p-3 font-medium">Obtained</th>
                                        <th class="text-center p-3 font-medium">Total</th>
                                        <th class="text-center p-3 font-medium">%</th>
                                        <th class="text-center p-3 font-medium">Grade</th>
                                        <th class="text-center p-3 font-medium">Result</th>
                                        <th class="text-left p-3 font-medium">Report Card</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $result)
                                        <tr class="border-b hover:bg-gray-50 {{ $result->is_absent ? 'opacity-60' : '' }}">
                                            <td class="p-3 text-center font-semibold">
                                                @if($result->is_absent)
                                                    —
                                                @else
                                                    {{ $result->meritPosition() }}
                                                @endif
                                            </td>
                                            <td class="p-3 font-mono text-xs">{{ $result->student->reference_no }}</td>
                                            <td class="p-3">
                                                <div class="font-medium">{{ $result->student->name_en }}</div>
                                                @if($result->student->name_bn)
                                                    <div class="text-gray-500 text-xs">{{ $result->student->name_bn }}</div>
                                                @endif
                                            </td>
                                            <td class="p-3 text-center font-medium">{{ number_format((float) $result->obtained_marks, 2) }}</td>
                                            <td class="p-3 text-center text-gray-500">{{ number_format((float) $result->total_marks, 2) }}</td>
                                            <td class="p-3 text-center">{{ number_format((float) $result->percentage, 2) }}%</td>
                                            <td class="p-3 text-center">
                                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 font-medium">
                                                    {{ $result->grade_letter }}
                                                    <span class="text-gray-500">({{ number_format((float) $result->grade_point, 2) }})</span>
                                                </span>
                                            </td>
                                            <td class="p-3 text-center">
                                                @if($result->is_absent)
                                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Absent</span>
                                                @elseif($result->is_pass)
                                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Pass</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Fail</span>
                                                @endif
                                            </td>
                                            <td class="p-3">
                                                <a href="{{ route('exams.report-card', [$exam, $result->student]) }}"
                                                   class="btn btn-outline-indigo" target="_blank">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
