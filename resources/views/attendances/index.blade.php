<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <i class="bi bi-calendar-check-fill text-indigo-600"></i>
                {{ __('Daily Student Attendance') }}
            </h2>
            <a href="{{ route('attendances.report') }}" class="btn btn-outline-indigo font-bold rounded-3">
                <i class="bi bi-bar-chart-line-fill me-1"></i> Attendance Report
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-light min-vh-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filter & Batch Selection Card --}}
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <form method="GET" action="{{ route('attendances.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-7">
                        <label class="form-label fw-bold text-dark small">Select Batch *</label>
                        <select name="batch_id" class="form-select rounded-3 font-semibold" required>
                            <option value="">— Select Batch to Take Attendance —</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ $selectedBatchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif @if($b->project) [Project: {{ $b->project->name }}] @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-bold text-dark small">Attendance Date *</label>
                        <input type="date" name="date" value="{{ $selectedDate }}" class="form-control rounded-3" required>
                    </div>

                    <div class="col-md-2 col-lg-2">
                        <button type="submit" class="btn btn-indigo w-100 font-bold rounded-3 py-2">
                            <i class="bi bi-search me-1"></i> Load Students
                        </button>
                    </div>
                </form>
            </div>

            {{-- Attendance Marking Table --}}
            @if($students->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center border-bottom">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">
                                Attendance Register: {{ $batch?->name }}
                            </h4>
                            <p class="text-muted text-xs mb-0">
                                Date: <span class="font-mono text-dark fw-bold">{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</span>
                                | Course: <span class="fw-bold text-indigo-700">{{ $batch?->course?->name }}</span>
                                | Project: <span class="fw-bold text-purple-700">{{ $batch?->project?->name }}</span>
                            </p>
                        </div>
                        <span class="badge bg-indigo-100 text-indigo-800 font-bold fs-6 px-3 py-1.5 rounded-pill">
                            Total Students: {{ $students->count() }}
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('attendances.store') }}">
                            @csrf
                            <input type="hidden" name="batch_id" value="{{ $selectedBatchId }}">
                            <input type="hidden" name="attendance_date" value="{{ $selectedDate }}">

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Ref / ID</th>
                                            <th>Contact Phone</th>
                                            <th class="text-center">Attendance Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $student)
                                            @php
                                                $attendance = $attendances->get($student->id);
                                                $isPresent = $attendance ? $attendance->is_present : true;
                                            @endphp
                                            <tr>
                                                <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-bold text-dark">
                                                    {{ $student->name_en }}
                                                    @if($student->name_bn)
                                                        <div class="text-muted small font-normal">{{ $student->name_bn }}</div>
                                                    @endif
                                                </td>
                                                <td class="font-mono text-xs text-primary">{{ $student->reference_no }}</td>
                                                <td class="font-mono text-dark">{{ $student->contact }}</td>
                                                <td class="text-center">
                                                    <input type="hidden" name="students[]" value="{{ $student->id }}">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input class="form-check-input" type="checkbox" name="present[{{ $student->id }}]" value="1" id="att_check_{{ $student->id }}" {{ $isPresent ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                                        <label class="form-check-label font-bold text-xs ms-1" for="att_check_{{ $student->id }}">
                                                            {{ $isPresent ? 'Present' : 'Absent' }}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" name="remarks[{{ $student->id }}]" value="{{ $attendance?->remarks }}" placeholder="Optional notes..." class="form-control form-control-sm rounded-3 text-xs">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success btn-lg font-bold px-5 rounded-3 shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i> Save Attendance Register
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif($selectedBatchId)
                <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                    <div class="card-body text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                        <h5 class="fw-bold text-dark">No Admitted Students Found</h5>
                        <p class="small mb-0">There are no active/admitted students assigned to batch <strong>{{ $batch?->name }}</strong> yet.</p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
