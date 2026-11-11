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
                <button type="button" class="btn btn-solid-indigo" data-bs-toggle="modal" data-bs-target="#createAttendanceModal">+ New Session</button>
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
                            <table class="w-full text-sm datatable"
                                   data-dt-paging="false" data-dt-searching="false" data-dt-info="false">
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
    {{-- Create Attendance Session Modal --}}
    <div class="modal fade" id="createAttendanceModal" tabindex="-1" aria-labelledby="createAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createAttendanceModalLabel">
                        <i class="bi bi-calendar-plus text-rose-400"></i> {{ __('New Attendance Session') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="create_attendance_batch" class="form-label fw-semibold text-slate-700 small">{{ __('Batch') }} *</label>
                                <select id="create_attendance_batch" name="batch_id" required class="form-select rounded-3">
                                    <option value="">— {{ __('Select Batch') }} —</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected(old('batch_id') == $batch->id)>
                                            {{ $batch->name }} @if($batch->course) — {{ $batch->course->name }} @endif
                                            ({{ \App\Models\Student::where('batch_id', $batch->id)->count() }} {{ __('students') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_attendance_title" class="form-label fw-semibold text-slate-700 small">{{ __('Session Title (English)') }} *</label>
                                <input id="create_attendance_title" type="text" name="title" value="{{ old('title') }}" required
                                       class="form-control rounded-3" placeholder="{{ __('e.g. Web Development - Day 3') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="create_attendance_title_bn" class="form-label fw-semibold text-slate-700 small">{{ __('Session Title (Bangla)') }}</label>
                                <input id="create_attendance_title_bn" type="text" name="title_bn" value="{{ old('title_bn') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label for="create_attendance_date" class="form-label fw-semibold text-slate-700 small">{{ __('Date') }} *</label>
                                <input id="create_attendance_date" type="date" name="session_date" value="{{ old('session_date', now()->toDateString()) }}" required class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label for="create_attendance_start" class="form-label fw-semibold text-slate-700 small">{{ __('Start Time') }}</label>
                                <input id="create_attendance_start" type="time" name="start_time" value="{{ old('start_time') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label for="create_attendance_end" class="form-label fw-semibold text-slate-700 small">{{ __('End Time') }}</label>
                                <input id="create_attendance_end" type="time" name="end_time" value="{{ old('end_time') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label for="create_attendance_late" class="form-label fw-semibold text-slate-700 small">{{ __('Mark Late After (minutes)') }}</label>
                                <input id="create_attendance_late" type="number" name="late_after_minutes" value="{{ old('late_after_minutes', 10) }}" min="0" max="240" class="form-control rounded-3">
                            </div>
                            <div class="col-md-8">
                                <label for="create_attendance_remarks" class="form-label fw-semibold text-slate-700 small">{{ __('Remarks') }}</label>
                                <input id="create_attendance_remarks" type="text" name="remarks" value="{{ old('remarks') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-12">
                                <div class="rounded-3 border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-700">
                                    <i class="bi bi-qr-code me-1"></i>
                                    {{ __('A unique QR code will be generated automatically. Students can scan it to mark their own attendance.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Create Session') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() || request('create'))
                    var modal = document.getElementById('createAttendanceModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
