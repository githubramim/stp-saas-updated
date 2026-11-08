<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $session->title }}
            <span class="text-sm font-normal text-gray-400 ml-2">
                — {{ $session->batch?->name }} · {{ $session->session_date->format('d M Y') }}
            </span>
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ bulk: 'present' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded">{{ session('info') }}</div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="text-xs text-gray-500 uppercase">Present</div>
                    <div class="text-2xl font-semibold text-green-600">{{ $summary['present'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-500">
                    <div class="text-xs text-gray-500 uppercase">Late</div>
                    <div class="text-2xl font-semibold text-amber-600">{{ $summary['late'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
                    <div class="text-xs text-gray-500 uppercase">Absent</div>
                    <div class="text-2xl font-semibold text-red-600">{{ $summary['absent'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-gray-400">
                    <div class="text-xs text-gray-500 uppercase">Marked</div>
                    <div class="text-2xl font-semibold text-gray-700">{{ $summary['marked'] }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <div class="text-xs text-gray-500 uppercase">Roster</div>
                    <div class="text-2xl font-semibold text-indigo-600">{{ $summary['total'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-1">Student QR Check-in</h3>

                        @if($session->isOpen() && $session->qr_token)
                            @php($scanUrl = $session->qrScanUrl())
                            <div class="text-center mt-4">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=8&data={{ urlencode($scanUrl) }}"
                                     alt="Attendance QR code" class="mx-auto border rounded-lg" />
                            </div>
                            <div class="mt-3 text-xs text-gray-500 break-all bg-gray-50 p-2 rounded">{{ $scanUrl }}</div>
                            <div class="mt-2 text-xs text-gray-500">
                                @if($session->isQrActive())
                                    <span class="text-green-600 font-medium">● QR is live</span>
                                    @if($session->qr_expires_at)
                                        · expires {{ $session->qr_expires_at->format('d M, h:i A') }}
                                    @endif
                                @else
                                    <span class="text-red-600 font-medium">● QR expired / inactive</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('attendance.qr.regenerate', $session) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="btn btn-outline-gray w-full">Regenerate QR</button>
                            </form>
                        @elseif(! $session->isOpen())
                            <p class="text-sm text-gray-500 mt-4 p-3 bg-gray-50 rounded">
                                This session is closed, so the QR is no longer accepting scans.
                            </p>
                        @else
                            <p class="text-sm text-gray-500 mt-4 p-3 bg-gray-50 rounded">No QR token on this session.</p>
                        @endif

                        <dl class="mt-6 pt-4 border-t text-sm space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Start</dt>
                                <dd class="font-medium">{{ $session->start_time ? \Illuminate\Support\Carbon::parse($session->start_time)->format('h:i A') : '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">End</dt>
                                <dd class="font-medium">{{ $session->end_time ? \Illuminate\Support\Carbon::parse($session->end_time)->format('h:i A') : '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Late after</dt>
                                <dd class="font-medium">{{ $session->late_after_minutes }} min</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Status</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs rounded {{ $session->isOpen() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $session->isOpen() ? 'Open' : 'Closed' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 pt-4 border-t space-y-2">
                            @if($session->isOpen())
                                <form method="POST" action="{{ route('attendance.close', $session) }}"
                                      onsubmit="return confirm('Close this session? Unmarked students will be recorded as absent.')">
                                    @csrf
                                    <button type="submit" class="btn btn-solid-red w-full">Close Session</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('attendance.reopen', $session) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-emerald w-full">Reopen Session</button>
                                </form>
                            @endif
                            <a href="{{ route('attendance.index') }}" class="btn btn-outline-gray w-full">Back to List</a>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
                            <h3 class="text-lg font-semibold">Manual Marking</h3>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-500">Set all to:</span>
                                <button type="button" class="btn btn-outline-gray" @click="bulk = 'present'">Present</button>
                                <button type="button" class="btn btn-outline-amber" @click="bulk = 'late'">Late</button>
                                <button type="button" class="btn btn-outline-red" @click="bulk = 'absent'">Absent</button>
                            </div>
                        </div>

                        @if($students->isEmpty())
                            <p class="text-gray-500 text-center py-8">No active students in this batch yet.</p>
                        @else
                            <form method="POST" action="{{ route('attendance.mark', $session) }}">
                                @csrf
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b bg-gray-50">
                                                <th class="text-left p-3 font-medium">#</th>
                                                <th class="text-left p-3 font-medium">Student</th>
                                                <th class="text-left p-3 font-medium">Registration No</th>
                                                <th class="text-center p-3 font-medium">Present</th>
                                                <th class="text-center p-3 font-medium">Late</th>
                                                <th class="text-center p-3 font-medium">Absent</th>
                                                <th class="text-left p-3 font-medium">Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $student)
                                                @php($record = $marked->get($student->id))
                                                <tr class="border-b hover:bg-gray-50">
                                                    <td class="p-3 text-gray-400">{{ $loop->iteration }}</td>
                                                    <td class="p-3">
                                                        <div class="font-medium">{{ $student->name_en }}</div>
                                                        @if($student->name_bn)
                                                            <div class="text-gray-500 text-xs">{{ $student->name_bn }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="p-3 font-mono text-xs">{{ $student->reference_no }}</td>
                                                    @foreach(['present', 'late', 'absent'] as $status)
                                                        <td class="p-3 text-center">
                                                            <input type="radio"
                                                                   name="attendance[{{ $student->id }}]"
                                                                   value="{{ $status }}"
                                                                   x-bind:checked="bulk === '{{ $status }}'"
                                                                   @checked(! $record && $status === 'present')
                                                                   @checked($record && $record->status === $status)
                                                                   class="text-indigo-600 focus:ring-indigo-500" />
                                                        </td>
                                                    @endforeach
                                                    <td class="p-3 text-xs uppercase text-gray-500">{{ $record?->method ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="btn btn-solid-emerald">Save Attendance</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
