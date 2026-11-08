<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $student->name_en }} — Student Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $student->name_en }}</h1>
            @if($student->name_bn)
                <p class="text-gray-500">{{ $student->name_bn }}</p>
            @endif
            <p class="text-sm text-gray-500 font-mono mt-1">{{ $student->reference_no }}</p>
        </div>
        <a href="{{ route('portal.index') }}"
           class="text-sm text-indigo-600 hover:underline whitespace-nowrap">Search another</a>
    </div>

    @if($unpublishedCount > 0)
        <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-800 p-3 mb-6 rounded text-sm">
            {{ $unpublishedCount }} result(s) are still being processed and will appear once published.
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white shadow-sm rounded-lg p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">CGPA</div>
            <div class="text-2xl font-bold text-indigo-600">{{ number_format((float) $cgpa, 2) }}</div>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">Attendance</div>
            <div class="text-2xl font-bold text-emerald-600">{{ number_format((float) $attendance['percentage'], 2) }}%</div>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">Present</div>
            <div class="text-2xl font-bold text-gray-700">{{ $attendance['present'] + $attendance['late'] }}</div>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">Absent</div>
            <div class="text-2xl font-bold text-red-500">{{ $attendance['absent'] }}</div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-800">Published Results</h2>
        </div>

        @if($results->isEmpty())
            <p class="p-8 text-center text-gray-500">No results have been published yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-white text-left">
                            <th class="p-4 font-medium">Exam</th>
                            <th class="p-4 font-medium">Date</th>
                            <th class="p-4 font-medium text-center">Marks</th>
                            <th class="p-4 font-medium text-center">%</th>
                            <th class="p-4 font-medium text-center">Grade</th>
                            <th class="p-4 font-medium text-center">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                            <tr class="border-b last:border-0 hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="font-medium text-gray-800">{{ $result->exam?->title }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $result->exam?->batch?->name ?? '' }}
                                        {{ $result->exam?->course?->name ? '· '.$result->exam->course->name : '' }}
                                    </div>
                                </td>
                                <td class="p-4 text-gray-500">{{ $result->exam?->exam_date?->format('d M Y') }}</td>
                                <td class="p-4 text-center">
                                    {{ number_format((float) $result->obtained_marks, 2) }}
                                    <span class="text-gray-400">/ {{ number_format((float) $result->total_marks, 2) }}</span>
                                </td>
                                <td class="p-4 text-center">{{ number_format((float) $result->percentage, 2) }}%</td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 font-medium">
                                        {{ $result->grade_letter }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    @if($result->is_absent)
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Absent</span>
                                    @elseif($result->is_pass)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Pass</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Fail</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
</body>
</html>