<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ID Cards — {{ $institute->name }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 9mm; }
        html, body { margin: 0; padding: 0; }
        body {
            background: #e2e8f0;
            color: #0f172a;
            font-family: "Figtree", "Segoe UI", Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 20px;
            background: #0f172a;
            color: #f8fafc;
            box-shadow: 0 6px 20px rgba(15, 23, 42, .18);
        }
        .toolbar a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .toolbar a:hover { color: #ffffff; }
        .toolbar .meta { font-size: 13px; color: #cbd5e1; }
        .toolbar .meta strong { color: #ffffff; }
        .toolbar button {
            border: 0;
            border-radius: 10px;
            background: #4f46e5;
            color: #ffffff;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .toolbar button:hover { background: #4338ca; }

        .sheet {
            display: grid;
            gap: 5mm;
            justify-content: center;
            padding: 8mm 4mm 12mm;
        }

        .person {
            display: grid;
            grid-template-columns: 85.6mm 85.6mm;
            gap: 4mm;
            break-inside: avoid;
            page-break-inside: avoid;
        }
    </style>
    @include('id-cards.partials.card-style')
    <style>
        @media print {
            body { background: #ffffff; }
            .toolbar { display: none !important; }
            .sheet { display: block; padding: 0; }
            .person { margin-bottom: 5mm; }
            .idc { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('id-cards.index', ['type' => $type]) }}">&larr; Back to ID cards</a>
        <div class="meta">
            <strong>{{ $people->count() }}</strong> {{ $type === 'student' ? 'student' : 'employee' }}{{ $people->count() === 1 ? '' : 's' }}
            &middot; {{ $people->count() * 2 }} sides &middot; A4
        </div>
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="sheet">
        @foreach($people as $person)
            <div class="person">
                @include('id-cards.partials.front', [
                    'person' => $person,
                    'design' => $design['front'],
                    'institute' => $institute,
                    'type' => $type,
                ])
                @include('id-cards.partials.back', [
                    'person' => $person,
                    'design' => $design['back'],
                    'contact' => $contact,
                    'institute' => $institute,
                ])
            </div>
        @endforeach
    </div>
</body>
</html>
