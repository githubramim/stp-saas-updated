<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Card — {{ $student->name_en }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #f1f5f9;
            font-family: "Segoe UI", system-ui, -apple-system, "Noto Sans Bengali", sans-serif;
            color: #111827;
            font-size: 13px;
            line-height: 1.5;
        }
        .sheet {
            max-width: 860px;
            margin: 0 auto;
            background: #fff;
            padding: 36px 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,.15);
        }
        .no-print { text-align: right; margin-bottom: 16px; }
        button {
            background: #4f46e5; color: #fff; border: 0; cursor: pointer;
            padding: 8px 16px; border-radius: 6px; font-size: 13px;
        }
        header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 14px; }
        header h1 { margin: 0 0 4px; font-size: 20px; letter-spacing: .5px; text-transform: uppercase; }
        header p { margin: 2px 0; font-size: 12px; color: #374151; }
        .title {
            margin: 18px 0; text-align: center; font-size: 15px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #9ca3af; padding: 6px 8px; }
        th { background: #f3f4f6; font-weight: 600; text-align: left; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .kv td { border: 0; padding: 3px 0; }
        .kv td:first-child { width: 130px; color: #4b5563; }
        .kv td:last-child { font-weight: 600; }
        .center { text-align: center; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { border: 1px solid #9ca3af; padding: 10px; text-align: center; }
        .stat .label { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; }
        .stat .value { font-size: 18px; font-weight: 700; margin-top: 2px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .badge.pass { background: #dcfce7; color: #166534; }
        .badge.fail { background: #fee2e2; color: #991b1b; }
        .badge.absent { background: #e5e7eb; color: #374151; }
        footer { margin-top: 26px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; }
        footer div { border-top: 1px solid #111827; padding-top: 6px; font-size: 12px; }
        .muted { color: #6b7280; font-size: 11px; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { box-shadow: none; max-width: none; padding: 0; }
            .no-print { display: none; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="sheet">
        <header>
            <h1>{{ $institute?->name ?? 'Institute' }}</h1>
            @if($institute?->address)
                <p>{{ $institute->address }}</p>
            @endif
            @if($institute?->phone)
                <p>Phone: {{ $institute->phone }}</p>
            @endif
        </header>

        <div class="title">Report Card</div>

        <div class="grid-2">
            <table class="kv">
                <tr><td>Name</td><td>{{ $student->name_en }}</td></tr>
                @if($student->name_bn)
                    <tr><td>নাম</td><td>{{ $student->name_bn }}</td></tr>
                @endif
                <tr><td>Registration No</td><td>{{ $student->reference_no }}</td></tr>
                <tr><td>Course</td><td>{{ $exam->course?->name ?? '—' }}</td></tr>
            </table>
            <table class="kv">
                <tr><td>Batch</td><td>{{ $exam->batch?->name ?? '—' }}</td></tr>
                <tr><td>Exam</td><td>{{ $exam->title }}</td></tr>
                <tr><td>Exam Date</td><td>{{ $exam->exam_date->format('d M Y') }}</td></tr>
                <tr><td>Roll / Seat</td><td>{{ $student->roll_no ?? '—' }}</td></tr>
            </table>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="label">Total Marks</div>
                <div class="value">{{ number_format((float) $result->total_marks, 2) }}</div>
            </div>
            <div class="stat">
                <div class="label">Obtained</div>
                <div class="value">{{ number_format((float) $result->obtained_marks, 2) }}</div>
            </div>
            <div class="stat">
                <div class="label">Percentage</div>
                <div class="value">{{ number_format((float) $result->percentage, 2) }}%</div>
            </div>
            <div class="stat">
                <div class="label">Grade</div>
                <div class="value">{{ $result->grade_letter }}</div>
                <div class="muted">Point {{ number_format((float) $result->grade_point, 2) }}</div>
            </div>
        </div>

        <table class="center">
            <tr>
                <th>Merit Position</th>
                <th>Pass Marks</th>
                <th>Result</th>
                <th>CGPA</th>
                <th>Attendance</th>
            </tr>
            <tr>
                <td>{{ $result->meritPosition() ?: '—' }}</td>
                <td>{{ number_format((float) $exam->pass_marks, 2) }}</td>
                <td>
                    @if($result->is_absent)
                        <span class="badge absent">Absent</span>
                    @elseif($result->is_pass)
                        <span class="badge pass">Pass</span>
                    @else
                        <span class="badge fail">Fail</span>
                    @endif
                </td>
                <td>{{ number_format((float) $cgpa, 2) }}</td>
                <td>{{ number_format((float) $attendance['percentage'], 2) }}%</td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 60%">Attendance Summary</th>
                    <th>Present</th>
                    <th>Late</th>
                    <th>Absent</th>
                    <th>Total</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                <tr class="center">
                    <td style="text-align: left">{{ $exam->batch?->name ?? 'All batches' }}</td>
                    <td>{{ $attendance['present'] }}</td>
                    <td>{{ $attendance['late'] }}</td>
                    <td>{{ $attendance['absent'] }}</td>
                    <td>{{ $attendance['total'] }}</td>
                    <td>{{ number_format((float) $attendance['percentage'], 2) }}%</td>
                </tr>
            </tbody>
        </table>

        @if($result->remarks)
            <p><strong>Remarks:</strong> {{ $result->remarks }}</p>
        @endif

        <footer>
            <div>Student Signature</div>
            <div>Examiner Signature</div>
            <div>Principal Signature</div>
        </footer>
    </div>
</body>
</html>