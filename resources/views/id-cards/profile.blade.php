<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $person['name'] }} — {{ $type === 'student' ? 'Student' : 'Employee' }} ID · {{ $institute->name }}</title>
    @if($institute->logo)
        <link rel="icon" href="{{ asset('storage/' . $institute->logo) }}">
    @endif
    <style>
        * { box-sizing: border-box; }
        :root {
            --accent: {{ $design['front']['accent'] }};
            --accent2: {{ $design['front']['accent2'] }};
            --ink: {{ $design['front']['text'] }};
            --card: {{ $design['front']['background'] }};
        }
        html, body { margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: #eef2f7;
            color: #0f172a;
            font-family: "Figtree", "Segoe UI", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            width: 100%;
            max-width: 460px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 12px;
            color: #64748b;
        }
        .toolbar span { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; }
        .toolbar button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            border-radius: 10px;
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .toolbar button:hover { background: #f8fafc; }

        .card {
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            border-radius: 24px;
            background: var(--card);
            box-shadow: 0 24px 50px -24px rgba(15, 23, 42, .4);
        }

        .hero {
            position: relative;
            padding: 26px 24px 54px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #ffffff;
        }
        .hero-top {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .hero-logo {
            width: 44px;
            height: 44px;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 12px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .4);
            padding: 4px;
        }
        .hero-logo img { width: 100%; height: 100%; object-fit: contain; }
        .hero-logo span { font-size: 13px; font-weight: 800; letter-spacing: .04em; }
        .hero-institute {
            min-width: 0;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .hero-institute small {
            display: block;
            margin-top: 2px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .1em;
            opacity: .8;
        }
        .verified {
            position: absolute;
            top: 26px;
            right: 24px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .45);
            background: rgba(255, 255, 255, .16);
            padding: 5px 10px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .verified i { font-style: normal; }

        .body { position: relative; padding: 0 24px 22px; }
        .avatar {
            width: 92px;
            height: 92px;
            margin-top: -46px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 20px;
            background: #f1f5f9;
            border: 4px solid var(--card);
            box-shadow: 0 10px 20px -12px rgba(15, 23, 42, .45);
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .avatar span { font-size: 30px; font-weight: 800; color: #64748b; letter-spacing: .04em; }

        .name { font-size: 23px; font-weight: 800; line-height: 1.15; color: var(--ink); }
        .name-bn { margin-top: 2px; font-size: 14px; color: #64748b; }
        .subtitle { margin-top: 8px; font-size: 13px; font-weight: 600; color: #64748b; }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 12px;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
        }
        .pill-accent {
            border-color: transparent;
            background: var(--accent);
            color: #ffffff;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #eef2f7;
        }
        .detail-label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .detail-value {
            display: block;
            margin-top: 2px;
            font-size: 13.5px;
            font-weight: 700;
            line-height: 1.35;
            color: var(--ink);
            word-break: break-word;
        }
        .detail-value a { color: inherit; text-decoration: none; }
        .detail-value a:hover { text-decoration: underline; }
        .detail-wide { grid-column: 1 / -1; }

        .footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-top: 1px solid #eef2f7;
            padding: 14px 24px 18px;
            font-size: 11px;
            color: #94a3b8;
        }
        .footer strong { color: #475569; }

        .note {
            max-width: 460px;
            margin-top: 14px;
            text-align: center;
            font-size: 11px;
            line-height: 1.5;
            color: #94a3b8;
        }

        @media (max-width: 380px) {
            .details { grid-template-columns: 1fr; }
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .toolbar, .note { display: none !important; }
            .card { box-shadow: none; border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span><i>◈</i> {{ __('Verified ID profile') }}</span>
        <button type="button" onclick="window.print()">{{ __('Print') }}</button>
    </div>

    <main class="card">
        <div class="hero">
            <div class="hero-top">
                <div class="hero-logo">
                    @if($institute->logo)
                        <img src="{{ asset('storage/' . $institute->logo) }}" alt="{{ $institute->name }}">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($institute->name, 0, 2)) }}</span>
                    @endif
                </div>
                <div class="hero-institute">
                    {{ $institute->name }}
                    <small>{{ $type === 'student' ? __('Student Identity') : __('Employee Identity') }}</small>
                </div>
            </div>
            <div class="verified"><i>✓</i> {{ __('Verified') }}</div>
        </div>

        <div class="body">
            <div class="avatar">
                @if($person['photo'])
                    <img src="{{ $person['photo'] }}" alt="{{ $person['name'] }}">
                @else
                    <span>{{ $person['initials'] }}</span>
                @endif
            </div>

            <h1 class="name">{{ $person['name'] }}</h1>
            @if($person['name_bn'])
                <p class="name-bn">{{ $person['name_bn'] }}</p>
            @endif
            <p class="subtitle">
                {{ $person['line1'] ?: ($type === 'student' ? __('Student') : __('Staff')) }}
                @if($person['line2'])
                    &middot; {{ $person['line2'] }}
                @endif
            </p>

            <div class="meta">
                <span class="pill pill-accent">{{ $person['id_no'] }}</span>
                @if($person['status'])
                    <span class="pill">{{ $person['status'] }}</span>
                @endif
                @if($person['valid'])
                    <span class="pill">{{ __('Valid until :date', ['date' => $person['valid']]) }}</span>
                @endif
            </div>

            <div class="details">
                <div>
                    <span class="detail-label">{{ $type === 'student' ? __('Student ID') : __('Employee ID') }}</span>
                    <span class="detail-value">{{ $person['id_no'] }}</span>
                </div>
                <div>
                    <span class="detail-label">{{ $type === 'student' ? __('Course') : __('Designation') }}</span>
                    <span class="detail-value">{{ $person['line1'] ?: '—' }}</span>
                </div>
                <div>
                    <span class="detail-label">{{ $type === 'student' ? __('Batch') : __('Institute') }}</span>
                    <span class="detail-value">{{ $person['line2'] ?: $institute->name }}</span>
                </div>
                <div>
                    <span class="detail-label">{{ __('Status') }}</span>
                    <span class="detail-value">{{ $person['status'] ?: '—' }}</span>
                </div>

                @if($person['blood'])
                    <div>
                        <span class="detail-label">{{ __('Blood group') }}</span>
                        <span class="detail-value">{{ $person['blood'] }}</span>
                    </div>
                @endif
                @if($person['valid'])
                    <div>
                        <span class="detail-label">{{ __('Valid until') }}</span>
                        <span class="detail-value">{{ $person['valid'] }}</span>
                    </div>
                @endif
                @if($person['phone'])
                    <div class="{{ $person['blood'] || $person['valid'] ? 'detail-wide' : '' }}">
                        <span class="detail-label">{{ __('Contact') }}</span>
                        <span class="detail-value">
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $person['phone']) }}">{{ $person['phone'] }}</a>
                        </span>
                    </div>
                @endif
                @if($institute->address)
                    <div class="detail-wide">
                        <span class="detail-label">{{ __('Institute address') }}</span>
                        <span class="detail-value">{{ $institute->address }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <span><strong>{{ __('Issued by') }}</strong> {{ $institute->name }}</span>
            <span>{{ __('Powered by TrainingPro') }}</span>
        </div>
    </main>

    <p class="note">
        {{ __('This profile is reached by scanning the QR code printed on an official ID card. If the details here do not match the physical card, please contact the institute office.') }}
    </p>
</body>
</html>
