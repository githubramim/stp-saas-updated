<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Project-wise Attendance Report') }}
            <span class="text-sm font-normal text-gray-400 ml-2">— {{ $month->format('F Y') }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('attendance.project-report') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="month" value="Month" />
                            <input type="month" id="month" name="month" value="{{ $month->format('Y-m') }}"
                                   class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <x-primary-button>{{ __('Generate') }}</x-primary-button>
                        <a href="{{ route('attendance.project-report.csv', ['month' => $month->format('Y-m')]) }}"
                           class="btn btn-outline-emerald">Export CSV</a>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Attendance by Batch (current project)</h3>

                    @if($batches->isEmpty())
                        <p class="text-gray-500 text-center py-8">No batches in this project yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Batch</th>
                                        <th class="text-left p-3 font-medium">Course</th>
                                        <th class="text-center p-3 font-medium">Students</th>
                                        <th class="text-center p-3 font-medium">Sessions</th>
                                        <th class="text-center p-3 font-medium">Present</th>
                                        <th class="text-center p-3 font-medium">Late</th>
                                        <th class="text-center p-3 font-medium">Absent</th>
                                        <th class="text-center p-3 font-medium">Average %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batches as $row)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 font-medium">{{ $row['batch']->name }}</td>
                                            <td class="p-3">{{ $row['batch']->course?->name ?? '—' }}</td>
                                            <td class="p-3 text-center">{{ $row['students'] }}</td>
                                            <td class="p-3 text-center">{{ $row['sessions'] }}</td>
                                            <td class="p-3 text-center text-green-600 font-medium">{{ $row['present'] }}</td>
                                            <td class="p-3 text-center text-amber-600 font-medium">{{ $row['late'] }}</td>
                                            <td class="p-3 text-center text-red-600 font-medium">{{ $row['absent'] }}</td>
                                            <td class="p-3 text-center">
                                                <span class="px-2 py-1 text-xs rounded font-medium
                                                    {{ $row['percentage'] >= 75 ? 'bg-green-100 text-green-700' : ($row['percentage'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                                    {{ number_format($row['percentage'], 2) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
