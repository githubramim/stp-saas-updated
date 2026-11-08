<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Routine — {{ $project?->name ?? 'TrainingPro' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #222;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .toolbar button {
            background: #1e40af;
            color: #fff;
            border: 0;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 { font-size: 16px; color: #1e40af; }
        .header p { font-size: 10px; color: #555; margin-top: 2px; }
        .conflict-note {
            background: #fef2f2;
            border: 1px solid #f87171;
            color: #b91c1c;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 10px;
            margin-bottom: 10px;
        }
        .week {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        .day {
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }
        .day-header {
            text-align: center;
            font-weight: 700;
            padding: 6px 4px;
            background: #e0e7ff;
            color: #3730a3;
            font-size: 11px;
        }
        .day.friday .day-header { background: #fee2e2; color: #b91c1c; }
        .day-body { padding: 6px; min-height: 180px; }
        .card {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 6px;
            page-break-inside: avoid;
        }
        .card.clash { border-color: #ef4444; background: #fef2f2; }
        .card .time { font-weight: 700; font-size: 11px; }
        .card .line { margin-top: 2px; }
        .card .muted { color: #6b7280; font-size: 10px; }
        .pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            margin-top: 4px;
            margin-right: 4px;
        }
        .pill.room { background: #d1fae5; color: #065f46; }
        .pill.trainer { background: #e0f2fe; color: #075985; }
        .pill.clash-tag { background: #fecaca; color: #991b1b; }
        .empty { color: #d1d5db; text-align: center; padding-top: 40px; }
        .footer {
            margin-top: 14px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
        @media print {
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span></span>
        <button onclick="window.print()">🖨 Print / Save PDF</button>
    </div>

    <div class="header">
        <div>
            <h1>Class Routine</h1>
            <p>{{ $project?->name }} — {{ Auth::user()->institute?->name }}</p>
        </div>
        <div style="font-size:10px; color:#555;">Generated on {{ now()->format('d M Y, h:i A') }}</div>
    </div>

    @if($conflicts)
        <div class="conflict-note">
            ⚠️ <strong>Clock clashes detected.</strong> Affected classes are highlighted in red — they overlap on the same
            room, trainer or batch.
        </div>
    @endif

    <div class="week">
        @foreach(\App\Models\Schedule::DAYS as $dayIndex => $dayName)
            <div class="day {{ $dayIndex === 6 ? 'friday' : '' }}">
                <div class="day-header">
                    {{ $dayName }}
                    @if($dayIndex === 6)<span style="font-weight:400;">(Holiday)</span>@endif
                </div>
                <div class="day-body">
                    @forelse($week[$dayIndex] as $schedule)
                        @php $isClash = in_array($schedule->id, $conflicts); @endphp
                        <div class="card {{ $isClash ? 'clash' : '' }}">
                            <div class="time">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('h:i A') }} – {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('h:i A') }}</div>
                            <div class="line">{{ $schedule->subject ?: $schedule->batch?->name }}</div>
                            <div class="muted">{{ $schedule->batch?->name }}{{ $schedule->batch?->course ? ' · '.$schedule->batch->course->name : '' }}</div>
                            <div>
                                @if($schedule->room)
                                    <span class="pill room">🏠 {{ $schedule->room }}</span>
                                @endif
                                @if($schedule->trainer)
                                    <span class="pill trainer">👤 {{ $schedule->trainer->name }}</span>
                                @endif
                                @if($isClash)
                                    <span class="pill clash-tag">⚠️ Clash</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty">—</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">Generated by TrainingPro • {{ config('app.name') }}</div>
</body>
</html>