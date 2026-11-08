<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('info'))
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded">
                    {{ session('info') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('attendance.create') }}" class="btn btn-solid-indigo">+ New Session</a>
                <a href="{{ route('attendance.monthly-report') }}" class="btn btn-outline-indigo">Monthly Report</a>
                <a href="{{ route('attendance.project-report') }}" class="btn btn-outline-violet">Project-wise Report</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="batch_id" value="Batch" />
                            <select id="batch_id" name="batch_id" class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Batches</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(request('batch_id') == $batch->id)>{{ $batch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="month" value="Month" />
                            <input type="month" id="month" name="month" value="{{ request('month') }}"
                                   class="block mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-gray">Reset</a>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Attendance Sessions</h3>

                    @if($sessions->isEmpty())
                        <p class="text-gray-500 text-center py-8">No attendance sessions yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Date</th>
                                        <th class="text-left p-3 font-medium">Session</th>
                                        <th class="text-left p-3 font-medium">Batch</th>
                                        <th class="text-left p-3 font-medium">Time</th>
                                        <th class="text-left p-3 font-medium">Marked</th>
                                        <th class="text-left p-3 font-medium">Status</th>
                                        <th class="text-left p-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessions as $session)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 whitespace-nowrap">{{ $session->session_date->format('d M Y') }}</td>
                                            <td class="p-3">
                                                <div class="font-medium">{{ $session->title }}</div>
                                                @if($session->title_bn)
                                                    <div class="text-gray-500 text-xs">{{ $session->title_bn }}</div>
                                                @endif
                                            </td>
                                            <td class="p-3">{{ $session->batch?->name ?? '—' }}</td>
                                            <td class="p-3 whitespace-nowrap">
                                                {{ $session->start_time ? \Illuminate\Support\Carbon::parse($session->start_time)->format('h:i A') : '—' }}
                                            </td>
                                            <td class="p-3">{{ $session->attendances_count }}</td>
                                            <td class="p-3">
                                                <span class="px-2 py-1 text-xs rounded {{ $session->isOpen() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                                    {{ $session->isOpen() ? 'Open' : 'Closed' }}
                                                </span>
                                            </td>
                                            <td class="p-3 flex gap-2">
                                                <a href="{{ route('attendance.show', $session) }}" class="btn btn-outline-indigo">Open</a>
                                                <form action="{{ route('attendance.destroy', $session) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Delete this session and all its attendance records?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-red">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">{{ $sessions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
