<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - {{ $institute->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #fff;
            color: #111;
            padding: 20px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page { size: A4 portrait; margin: 15mm; }
        }
        .report-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .signature-box {
            margin-top: 50px;
            padding-top: 10px;
            border-top: 1px dashed #666;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="no-print mb-4 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-lg fw-bold px-4">
            🖨️ Print Report (A4)
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg fw-bold px-4 ms-2">
            Close
        </button>
    </div>

    {{-- Official Institute Header --}}
    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-uppercase mb-1">{{ $institute->name }}</h2>
            <div class="text-muted small">
                Code: <strong>{{ $institute->code }}</strong>
                @if($institute->eiin_no) | EIIN: <strong>{{ $institute->eiin_no }}</strong> @endif
                @if($institute->email) | Email: {{ $institute->email }} @endif
                @if($institute->mobile) | Phone: {{ $institute->mobile }} @endif
            </div>
            <div class="text-muted small">{{ $institute->address }}</div>
        </div>
        @if($institute->logo)
            <img src="{{ asset('storage/' . $institute->logo) }}" height="70" class="rounded">
        @endif
    </div>

    {{-- Report Title & Details --}}
    <div class="mb-4 bg-light p-3 rounded border">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0 text-uppercase">
                    @if($mode === 'day')
                        📅 Day-Wise Attendance Register
                    @elseif($mode === 'student')
                        👤 Student Attendance Transcript
                    @else
                        📁 Project & Batch Attendance Summary
                    @endif
                </h4>
                <div class="small text-muted mt-1">
                    @if($targetBatch)
                        Batch: <strong>{{ $targetBatch->name }}</strong> | Course: <strong>{{ $targetBatch->course?->name }}</strong> | Project: <strong>{{ $targetBatch->project?->name }}</strong>
                    @elseif($targetProject)
                        Project: <strong>{{ $targetProject->name }}</strong>
                    @endif
                    @if($mode === 'day')
                        | Date: <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong>
                    @endif
                </div>
            </div>
            <div class="text-end small">
                <div>Printed Date: <strong>{{ now()->format('Y-m-d H:i') }}</strong></div>
                <div>Status: <span class="badge bg-dark">Official Record</span></div>
            </div>
        </div>
    </div>

    {{-- Mode 1: Batch Summary Printable --}}
    @if($mode === 'batch' && !empty($reportData))
        <table class="table table-bordered table-sm align-middle text-sm mb-4">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Ref No</th>
                    <th>Student Name</th>
                    <th>Contact Phone</th>
                    <th style="width: 90px;">Total Classes</th>
                    <th style="width: 80px;">Present</th>
                    <th style="width: 80px;">Absent</th>
                    <th style="width: 90px;">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $s)
                    <tr>
                        <td class="text-center font-monospace">{{ $index + 1 }}</td>
                        <td class="font-monospace">{{ $s->reference_no }}</td>
                        <td class="fw-bold">{{ $s->name_en }}</td>
                        <td>{{ $s->contact }}</td>
                        <td class="text-center font-bold">{{ $s->total_classes }}</td>
                        <td class="text-center text-success font-bold">{{ $s->present_classes }}</td>
                        <td class="text-center text-danger font-bold">{{ $s->absent_classes }}</td>
                        <td class="text-center fw-bold {{ $s->attendance_percentage < 80 ? 'text-danger' : 'text-success' }}">
                            {{ $s->attendance_percentage }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- Mode 2: Day-Wise Register Printable --}}
    @elseif($mode === 'day' && !empty($reportData))
        <table class="table table-bordered table-sm align-middle text-sm mb-4">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Student Name</th>
                    <th>Batch</th>
                    <th>Course & Project</th>
                    <th class="text-center" style="width: 100px;">Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $att)
                    <tr>
                        <td class="text-center font-monospace">{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $att->student?->name_en }}</td>
                        <td>{{ $att->batch?->name }}</td>
                        <td class="small">{{ $att->batch?->course?->name }} ({{ $att->batch?->project?->name }})</td>
                        <td class="text-center font-bold {{ $att->is_present ? 'text-success' : 'text-danger' }}">
                            {{ $att->is_present ? 'PRESENT' : 'ABSENT' }}
                        </td>
                        <td class="small text-muted">{{ $att->remarks ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- Mode 3: Student-Wise Printable --}}
    @elseif($mode === 'student' && !empty($reportData))
        <div class="row g-3 mb-4">
            <div class="col-6">
                <strong>Student Name:</strong> {{ $reportData['student']->name_en }}<br>
                <strong>Ref Number:</strong> {{ $reportData['student']->reference_no }}<br>
                <strong>Contact:</strong> {{ $reportData['student']->contact }}
            </div>
            <div class="col-6 text-end">
                <strong>Course:</strong> {{ $reportData['student']->course?->name }}<br>
                <strong>Batch:</strong> {{ $reportData['student']->batch?->name }}<br>
                <strong>Attendance Rate:</strong> <span class="fw-bold text-success">{{ $reportData['percentage'] }}%</span>
            </div>
        </div>

        <table class="table table-bordered table-sm align-middle text-sm mb-4">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Date</th>
                    <th class="text-center" style="width: 100px;">Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['attendances'] as $index => $att)
                    <tr>
                        <td class="text-center font-monospace">{{ $index + 1 }}</td>
                        <td class="fw-bold font-monospace">{{ \Carbon\Carbon::parse($att->attendance_date)->format('M d, Y (l)') }}</td>
                        <td class="text-center font-bold {{ $att->is_present ? 'text-success' : 'text-danger' }}">
                            {{ $att->is_present ? 'PRESENT' : 'ABSENT' }}
                        </td>
                        <td class="small text-muted">{{ $att->remarks ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Bottom Signature Block --}}
    <div class="row text-center" style="margin-top: 80px;">
        <div class="col-4">
            <div class="signature-box">Trainer / Instructor Signature</div>
        </div>
        <div class="col-4">
            <div class="signature-box">Project Coordinator Signature</div>
        </div>
        <div class="col-4">
            <div class="signature-box">Head of Institute Signature</div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
