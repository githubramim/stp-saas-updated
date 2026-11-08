<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <i class="bi bi-bar-chart-line-fill text-indigo-600"></i>
                {{ __('Attendance Analytics & Reports') }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary font-bold rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Register
                </a>
                @php
                    $printUrl = route('attendances.print', [
                        'mode' => $mode,
                        'batch_id' => $selectedBatchId,
                        'project_id' => $selectedProjectId,
                        'date' => $selectedDate,
                        'student_id' => $selectedStudentId,
                    ]);
                @endphp
                <a href="{{ route('export.attendance', ['batch_id' => $selectedBatchId, 'date' => $selectedDate]) }}" class="btn btn-outline-success font-bold rounded-3">
                    <i class="bi bi-file-earmark-excel me-1"></i> 📥 Export CSV/Excel
                </a>
                <a href="{{ $printUrl }}" target="_blank" class="btn btn-primary font-bold rounded-3 shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Print Official Report (A4)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-light min-vh-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Mode Switcher Navigation Pills --}}
            <div class="card border-0 shadow-sm rounded-4 p-2 bg-white">
                <div class="nav nav-pills nav-fill gap-2" id="reportTabs">
                    <a class="nav-link rounded-3 font-bold py-2.5 {{ $mode === 'batch' ? 'active bg-indigo-600 text-white' : 'text-dark hover:bg-light' }}" href="{{ route('attendances.report', ['mode' => 'batch', 'batch_id' => $selectedBatchId]) }}">
                        <i class="bi bi-folder-fill me-1"></i> 1. Project & Batch-Wise Summary
                    </a>
                    <a class="nav-link rounded-3 font-bold py-2.5 {{ $mode === 'day' ? 'active bg-indigo-600 text-white' : 'text-dark hover:bg-light' }}" href="{{ route('attendances.report', ['mode' => 'day', 'date' => $selectedDate]) }}">
                        <i class="bi bi-calendar-day-fill me-1"></i> 2. Day-Wise Attendance Register
                    </a>
                    <a class="nav-link rounded-3 font-bold py-2.5 {{ $mode === 'student' ? 'active bg-indigo-600 text-white' : 'text-dark hover:bg-light' }}" href="{{ route('attendances.report', ['mode' => 'student', 'student_id' => $selectedStudentId]) }}">
                        <i class="bi bi-person-bounding-box me-1"></i> 3. Student-Wise Transcript
                    </a>
                </div>
            </div>

            {{-- Filter Box based on Mode --}}
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <form method="GET" action="{{ route('attendances.report') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="mode" value="{{ $mode }}">

                    @if($mode === 'day')
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark small">Select Date *</label>
                            <input type="date" name="date" value="{{ $selectedDate }}" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark small">Filter by Batch (Optional)</label>
                            <select name="batch_id" class="form-select rounded-3">
                                <option value="">— All Batches —</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}" {{ $selectedBatchId == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($mode === 'student')
                        <div class="col-md-10">
                            <label class="form-label fw-bold text-dark small">Select Student *</label>
                            <select name="student_id" class="form-select rounded-3 font-semibold" required>
                                <option value="">— Search / Select Student —</option>
                                @foreach($allStudents as $st)
                                    <option value="{{ $st->id }}" {{ $selectedStudentId == $st->id ? 'selected' : '' }}>
                                        {{ $st->name_en }} (Ref: {{ $st->reference_no }}) - Phone: {{ $st->contact }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-md-10">
                            <label class="form-label fw-bold text-dark small">Select Batch *</label>
                            <select name="batch_id" class="form-select rounded-3 font-semibold" required>
                                <option value="">— Choose Batch —</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}" {{ $selectedBatchId == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif @if($b->project) [Project: {{ $b->project->name }}] @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-indigo w-100 font-bold rounded-3 py-2">
                            <i class="bi bi-funnel-fill me-1"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>

            {{-- Mode 1: Batch / Project-Wise Report Output --}}
            @if($mode === 'batch')
                @if($targetBatch && !empty($reportData) && count($reportData) > 0)
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Batch Summary: {{ $targetBatch->name }}</h4>
                                <p class="text-muted text-xs mb-0">
                                    Course: <strong class="text-indigo-700">{{ $targetBatch->course?->name }}</strong>
                                    | Project: <strong class="text-purple-700">{{ $targetBatch->project?->name }}</strong>
                                </p>
                            </div>
                            <span class="badge bg-indigo-100 text-indigo-800 font-bold fs-6 px-3 py-1.5 rounded-pill">
                                {{ count($reportData) }} Enrolled Students
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Ref No</th>
                                            <th class="text-center">Total Classes</th>
                                            <th class="text-center">Present</th>
                                            <th class="text-center">Absent</th>
                                            <th class="text-center">Attendance %</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData as $index => $s)
                                            <tr class="{{ $s->attendance_percentage < 80 ? 'table-danger' : '' }}">
                                                <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-bold text-dark">
                                                    {{ $s->name_en }}
                                                    <div class="text-muted text-xs font-normal">{{ $s->contact }}</div>
                                                </td>
                                                <td class="font-mono text-xs text-primary">{{ $s->reference_no }}</td>
                                                <td class="text-center font-bold">{{ $s->total_classes }}</td>
                                                <td class="text-center text-success font-bold">{{ $s->present_classes }}</td>
                                                <td class="text-center text-danger font-bold">{{ $s->absent_classes }}</td>
                                                <td class="text-center">
                                                    <span class="fw-extrabold fs-6 {{ $s->attendance_percentage < 80 ? 'text-danger' : 'text-success' }}">
                                                        {{ $s->attendance_percentage }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($s->attendance_percentage < 80)
                                                        <span class="badge bg-danger text-white font-bold">⚠ Low Attendance</span>
                                                    @else
                                                        <span class="badge bg-success text-white font-bold">Satisfactory</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif($selectedBatchId)
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                        <div class="card-body text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No attendance statistics recorded for this batch.
                        </div>
                    </div>
                @endif

            {{-- Mode 2: Day-Wise Report Output --}}
            @elseif($mode === 'day')
                @if(!empty($reportData) && count($reportData) > 0)
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Day-Wise Attendance Register</h4>
                                <p class="text-muted text-xs mb-0">
                                    Date: <strong class="text-dark font-mono">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong>
                                </p>
                            </div>
                            <span class="badge bg-indigo-100 text-indigo-800 font-bold fs-6 px-3 py-1.5 rounded-pill">
                                {{ count($reportData) }} Records
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Batch</th>
                                            <th>Course & Project</th>
                                            <th class="text-center">Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData as $index => $att)
                                            <tr>
                                                <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-bold text-dark">{{ $att->student?->name_en }}</td>
                                                <td class="fw-semibold text-purple">{{ $att->batch?->name }}</td>
                                                <td class="text-muted text-xs">
                                                    <div>{{ $att->batch?->course?->name }}</div>
                                                    <span class="badge bg-light text-dark border">{{ $att->batch?->project?->name }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($att->is_present)
                                                        <span class="badge bg-success px-3 py-1 text-uppercase">Present</span>
                                                    @else
                                                        <span class="badge bg-danger px-3 py-1 text-uppercase">Absent</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted text-xs">{{ $att->remarks ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                        <div class="card-body text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            No attendance records found for {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}.
                        </div>
                    </div>
                @endif

            {{-- Mode 3: Student-Wise Report Output --}}
            @elseif($mode === 'student')
                @if($targetStudent && !empty($reportData))
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-indigo-50 border-indigo-100">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h4 class="fw-extrabold text-indigo-900 mb-1">{{ $targetStudent->name_en }}</h4>
                                <p class="text-indigo-700 text-xs mb-0">
                                    Ref: <strong class="font-mono">{{ $targetStudent->reference_no }}</strong>
                                    | Course: <strong>{{ $targetStudent->course?->name }}</strong>
                                    | Batch: <strong>{{ $targetStudent->batch?->name }}</strong>
                                    | Project: <strong>{{ $targetStudent->project?->name }}</strong>
                                </p>
                            </div>
                            <div class="d-flex gap-3 text-center">
                                <div class="bg-white p-3 rounded-3 shadow-xs">
                                    <div class="fw-black fs-4 text-indigo-700">{{ $reportData['total_classes'] }}</div>
                                    <div class="text-muted text-xs font-bold uppercase">Total Classes</div>
                                </div>
                                <div class="bg-white p-3 rounded-3 shadow-xs">
                                    <div class="fw-black fs-4 text-success">{{ $reportData['present_classes'] }}</div>
                                    <div class="text-muted text-xs font-bold uppercase">Present</div>
                                </div>
                                <div class="bg-white p-3 rounded-3 shadow-xs">
                                    <div class="fw-black fs-4 text-danger">{{ $reportData['absent_classes'] }}</div>
                                    <div class="text-muted text-xs font-bold uppercase">Absent</div>
                                </div>
                                <div class="bg-white p-3 rounded-3 shadow-xs">
                                    <div class="fw-black fs-4 {{ $reportData['percentage'] < 80 ? 'text-danger' : 'text-success' }}">
                                        {{ $reportData['percentage'] }}%
                                    </div>
                                    <div class="text-muted text-xs font-bold uppercase">Attendance Rate</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-4 border-bottom">
                            <h5 class="fw-bold text-dark mb-0">Date-by-Date Attendance Timeline</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th class="text-center">Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['attendances'] as $index => $att)
                                            <tr>
                                                <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-bold font-mono">{{ \Carbon\Carbon::parse($att->attendance_date)->format('M d, Y (l)') }}</td>
                                                <td class="text-center">
                                                    @if($att->is_present)
                                                        <span class="badge bg-success px-3 py-1 text-uppercase">Present</span>
                                                    @else
                                                        <span class="badge bg-danger px-3 py-1 text-uppercase">Absent</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted text-xs">{{ $att->remarks ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif($selectedStudentId)
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                        <div class="card-body text-muted">
                            <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                            No attendance history recorded for the selected student.
                        </div>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
