<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monthly Attendance Report') }}
            <span class="text-sm font-normal text-gray-400 ml-2">— {{ $month->format('F Y') }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('attendance.monthly-report') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="batch_id" value="Batch" />
                            <select id="batch_id" name="batch_id" class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select batch</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}" @selected($batch && $batch->id === $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="month" value="Month" />
                            <input type="month" id="month" name="month" value="{{ $month->format('Y-m') }}"
                                   class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <x-primary-button>{{ __('Generate') }}</x-primary-button>
                        @if($batch)
                            <a href="{{ route('attendance.monthly-report.csv', ['batch_id' => $batch->id, 'month' => $month->format('Y-m')]) }}"
                               class="btn btn-outline-emerald">Export CSV</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
                        <h3 class="text-lg font-semibold">
                            {{ $batch?->name ?? 'Select a batch' }}
                            @if($batch)
                                <span class="text-sm font-normal text-gray-400">({{ $batch->course?->name }})</span>
                            @endif
                        </h3>
                        <div class="text-sm text-gray-500">Sessions held: <strong>{{ $sessionCount }}</strong></div>
                    </div>

                    @if(! $batch)
                        <p class="text-gray-500 text-center py-8">Choose a batch to generate the report.</p>
                    @elseif($rows->isEmpty())
                        <p class="text-gray-500 text-center py-8">No students in this batch yet.</p>
                    @elseif($sessionCount === 0)
                        <p class="text-gray-500 text-center py-8">No attendance sessions were held in {{ $month->format('F Y') }}.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Registration No</th>
                                        <th class="text-left p-3 font-medium">Student</th>
                                        <th class="text-center p-3 font-medium">Present</th>
                                        <th class="text-center p-3 font-medium">Late</th>
                                        <th class="text-center p-3 font-medium">Absent</th>
                                        <th class="text-center p-3 font-medium">Unmarked</th>
                                        <th class="text-center p-3 font-medium">Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 font-mono text-xs">{{ $row['student']->reference_no }}</td>
                                            <td class="p-3">
                                                <div class="font-medium">{{ $row['student']->name_en }}</div>
                                                @if($row['student']->name_bn)
                                                    <div class="text-gray-500 text-xs">{{ $row['student']->name_bn }}</div>
                                                @endif
                                            </td>
                                            <td class="p-3 text-center text-green-600 font-medium">{{ $row['present'] }}</td>
                                            <td class="p-3 text-center text-amber-600 font-medium">{{ $row['late'] }}</td>
                                            <td class="p-3 text-center text-red-600 font-medium">{{ $row['absent'] }}</td>
                                            <td class="p-3 text-center text-gray-400">{{ $row['unmarked'] }}</td>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
