<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="STP SaaS — one portal for admissions, attendance, exams, certificates, ID cards and payroll for technical training institutes. Apply online in minutes.">
    <title>{{ config('app.name', 'STP SaaS') }} — Train. Certify. Get hired.</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes floaty {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-24px) scale(1.05); }
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .55; transform: scale(.82); }
        }
        .anim-blob { animation: floaty 9s ease-in-out infinite; }
        .anim-rise { animation: rise .7s ease-out both; }
        .anim-marquee { animation: marquee 30s linear infinite; }
        .anim-pulse-dot { animation: pulse-dot 1.6s ease-in-out infinite; }
        .anim-rise-2 { animation-delay: .12s; }
        .anim-rise-3 { animation-delay: .24s; }
        .anim-rise-4 { animation-delay: .36s; }
        @media (prefers-reduced-motion: reduce) {
            .anim-blob, .anim-rise, .anim-marquee, .anim-pulse-dot { animation: none; }
        }
    </style>
</head>
<body class="font-sans bg-slate-950 text-slate-100 antialiased overflow-x-hidden">

    {{-- ======================= NAV ======================= --}}
    <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/80 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="/" class="flex items-center gap-2.5">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-600 text-sm font-black text-white shadow-lg shadow-indigo-500/30">
                    ST
                </span>
                <span class="text-base font-extrabold tracking-tight text-white">
                    {{ config('app.name', 'STP SaaS') }}
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Training Portal</span>
                </span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-300 lg:flex">
                <a href="#features" class="transition hover:text-white">Features</a>
                <a href="#how" class="transition hover:text-white">How it works</a>
                <a href="#faq" class="transition hover:text-white">FAQ</a>
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-500 to-fuchsia-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:brightness-110">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="hidden rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 transition hover:bg-white/10 hover:text-white sm:inline-flex">
                        <i class="bi bi-box-arrow-in-right mr-1.5"></i>Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="hidden rounded-xl border border-white/20 px-4 py-2.5 text-sm font-bold text-white transition hover:border-white/40 hover:bg-white/5 sm:inline-flex">
                        <i class="bi bi-person-plus-fill mr-1.5"></i>Register
                    </a>
                    <a href="{{ route('apply') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-500 to-fuchsia-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:brightness-110">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Apply Now
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ======================= HERO ======================= --}}
    <section class="relative overflow-hidden">
        <div class="anim-blob pointer-events-none absolute -top-40 left-1/2 -z-0 h-[28rem] w-[52rem] -translate-x-1/2 rounded-full bg-indigo-600/30 blur-3xl"></div>
        <div class="anim-blob pointer-events-none absolute top-40 -left-40 -z-0 h-96 w-96 rounded-full bg-fuchsia-600/20 blur-3xl" style="animation-delay:-3s"></div>
        <div class="anim-blob pointer-events-none absolute top-24 -right-40 -z-0 h-96 w-96 rounded-full bg-cyan-500/20 blur-3xl" style="animation-delay:-6s"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-14 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:pb-24 lg:pt-20">
            <div>
                <span class="anim-rise inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-3.5 py-1.5 text-xs font-bold text-amber-300">
                    <span class="anim-pulse-dot h-2 w-2 rounded-full bg-amber-400"></span>
                    ভর্তি চলছে! &nbsp;·&nbsp; Limited seats for the next batch
                </span>

                <h1 class="anim-rise anim-rise-2 mt-5 text-4xl font-black leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Train hard.
                    <span class="block bg-gradient-to-r from-indigo-400 via-fuchsia-400 to-amber-300 bg-clip-text text-transparent">
                        Certify fast.
                    </span>
                    Get hired. 🚀
                </h1>

                <p class="anim-rise anim-rise-3 mt-5 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                    One portal for <strong class="font-bold text-white">admissions, attendance, exams, certificates,
                    ID cards and payroll</strong> — everything a technical training institute needs, in a single click.
                    Students apply online in under 3 minutes.
                </p>

                <div class="anim-rise anim-rise-4 mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('apply') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-fuchsia-600 px-6 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-500/40 transition hover:scale-[1.03] hover:brightness-110">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Apply for Admission
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-bold text-white backdrop-blur transition hover:border-white/40 hover:bg-white/10">
                        <i class="bi bi-person-plus-fill"></i>
                        Register Free
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-3.5 text-sm font-bold text-slate-300 transition hover:text-white">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Login
                    </a>
                </div>

                <div class="anim-rise anim-rise-4 mt-9 grid max-w-lg grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach([
                        ['institutes' => $stats['institutes'], 'label' => 'Institutes', 'icon' => 'bi-buildings-fill', 'tone' => 'text-cyan-300'],
                        ['courses' => $stats['courses'], 'label' => 'Courses', 'icon' => 'bi-journal-bookmark-fill', 'tone' => 'text-fuchsia-300'],
                        ['batches' => $stats['batches'], 'label' => 'Live batches', 'icon' => 'bi-people-fill', 'tone' => 'text-indigo-300'],
                        ['students' => $stats['students'], 'label' => 'Trainees', 'icon' => 'bi-mortarboard-fill', 'tone' => 'text-amber-300'],
                    ] as $stat)
                        @php($value = collect($stat)->except('label', 'icon', 'tone')->first())
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-3.5 py-3 backdrop-blur">
                            <p class="flex items-center gap-1.5 text-xl font-black text-white">
                                <i class="bi {{ $stat['icon'] }} {{ $stat['tone'] }} text-sm"></i>
                                {{ number_format($value) }}+
                            </p>
                            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Fake dashboard preview --}}
            <div class="anim-rise anim-rise-3 relative">
                <div class="pointer-events-none absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-indigo-500/20 to-fuchsia-500/20 blur-2xl"></div>
                <div class="relative rounded-3xl border border-white/10 bg-slate-900/80 p-5 shadow-2xl backdrop-blur">
                    <div class="flex items-center justify-between border-b border-white/10 pb-4">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <span class="rounded-full bg-white/5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Dashboard</span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-gradient-to-br from-indigo-500/20 to-indigo-500/5 p-4 ring-1 ring-indigo-400/20">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-300">Today's attendance</p>
                            <p class="mt-1 text-2xl font-black text-white">94%<span class="text-xs font-bold text-emerald-400"> ↑</span></p>
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-white/10">
                                <div class="h-full w-[94%] rounded-full bg-gradient-to-r from-indigo-400 to-fuchsia-400"></div>
                            </div>
                        </div>
                        <div class="rounded-2xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 p-4 ring-1 ring-emerald-400/20">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-300">Certificates issued</p>
                            <p class="mt-1 text-2xl font-black text-white">1,240<span class="text-xs font-bold text-emerald-400"> ↑</span></p>
                            <p class="mt-2 flex items-center gap-1 text-[10px] font-semibold text-emerald-200/70">
                                <i class="bi bi-award-fill"></i> QR verified
                            </p>
                        </div>
                        <div class="col-span-2 rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Admissions this week</p>
                                <span class="rounded-full bg-emerald-400/15 px-2 py-0.5 text-[10px] font-bold text-emerald-300">+38 new</span>
                            </div>
                            <div class="mt-3 flex h-16 items-end gap-1.5">
                                @foreach([38, 55, 42, 70, 58, 84, 96] as $bar)
                                    <div class="flex-1 rounded-t-md bg-gradient-to-t from-indigo-500/40 to-fuchsia-400/80" style="height: {{ $bar }}%"></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">ID cards printed</p>
                            <p class="mt-1 flex items-center gap-1.5 text-lg font-black text-white"><i class="bi bi-person-vcard-fill text-cyan-300"></i> 326</p>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Stipends paid</p>
                            <p class="mt-1 flex items-center gap-1.5 text-lg font-black text-white"><i class="bi bi-cash-coin text-amber-300"></i> ৳2.4L</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-5 -left-4 hidden items-center gap-2 rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 shadow-xl sm:flex">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-300"><i class="bi bi-check-circle-fill"></i></span>
                    <div>
                        <p class="text-xs font-bold text-white">Admission approved</p>
                        <p class="text-[10px] text-slate-400">Certificate generated automatically</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Course marquee --}}
        @if($courses->isNotEmpty())
            <div class="relative border-y border-white/10 bg-white/[0.03] py-3.5">
                <div class="flex overflow-hidden [mask-image:linear-gradient(90deg,transparent,black_10%,black_90%,transparent)]">
                    <div class="anim-marquee flex shrink-0 items-center gap-3 pr-3">
                        @foreach($courses->merge($courses) as $course)
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5 text-xs font-bold text-slate-300">
                                <i class="bi bi-journal-bookmark-fill text-fuchsia-300"></i>
                                {{ $course }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </section>

    {{-- ======================= FEATURES ======================= --}}
    <section id="features" class="bg-white py-20 text-slate-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-600">Everything included</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
                    One login. <span class="bg-gradient-to-r from-indigo-600 to-fuchsia-600 bg-clip-text text-transparent">Zero paperwork.</span>
                </h2>
                <p class="mt-3 text-slate-500">
                    Stop juggling spreadsheets and registers. STP SaaS runs the whole training lifecycle for you.
                </p>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['bi-person-hearts', 'from-indigo-500 to-blue-500', 'Smart Admissions CRM', 'Online apply form, leads, follow-ups and shortlisting — every applicant tracked from first click to final selection.'],
                    ['bi-qr-code-scan', 'from-cyan-500 to-teal-500', 'QR Attendance', 'Students scan a QR code to mark attendance. Live percentages per batch, per student, per day.'],
                    ['bi-file-earmark-check-fill', 'from-violet-500 to-purple-600', 'Exams & Results', 'Create exams, capture marks, publish results and rank students — no more marksheet chaos.'],
                    ['bi-award-fill', 'from-amber-500 to-orange-600', 'Certificates & ID Cards', 'Beautiful certificates and photo ID cards with QR-verified public profiles, printed in batches.'],
                    ['bi-cash-coin', 'from-emerald-500 to-green-600', 'Accounts & Payroll', 'Fees, expenses, salary sheets, stipends and instant P&L so you always know your numbers.'],
                    ['bi-chat-left-dots-fill', 'from-rose-500 to-pink-600', 'SMS & Notices', 'Notify students and guardians about results, notices and schedules automatically.'],
                ] as [$icon, $gradient, $title, $text])
                    <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10">
                        <div class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br {{ $gradient }} opacity-10 blur-2xl transition group-hover:opacity-25"></div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $gradient }} text-xl text-white shadow-lg">
                            <i class="bi {{ $icon }}"></i>
                        </span>
                        <h3 class="mt-4 text-lg font-black">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======================= HOW IT WORKS ======================= --}}
    <section id="how" class="bg-slate-50 py-20 text-slate-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-600">3 steps, that's it</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">From applicant to certified pro</h2>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach([
                    ['01', 'bi-rocket-takeoff-fill', 'from-indigo-500 to-blue-500', 'Apply online', 'Fill the public admission form, upload your photo and pick your course. Takes under 3 minutes.'],
                    ['02', 'bi-stars', 'from-violet-500 to-purple-600', 'Get shortlisted', 'The institute reviews applications, shortlists and admits you — you get your student ID instantly.'],
                    ['03', 'bi-mortarboard-fill', 'from-amber-500 to-orange-600', 'Train & certify', 'Attend with QR scans, sit exams, and walk out with a QR-verifiable certificate.'],
                ] as [$no, $icon, $gradient, $title, $text])
                    <div class="relative rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                        <span class="absolute right-6 top-5 text-5xl font-black text-slate-100 select-none">{{ $no }}</span>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $gradient }} text-xl text-white shadow-lg">
                            <i class="bi {{ $icon }}"></i>
                        </span>
                        <h3 class="mt-4 text-lg font-black">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('apply') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-fuchsia-600 px-7 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-500/30 transition hover:scale-[1.03] hover:brightness-110">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Start my application — it's free
                </a>
            </div>
        </div>
    </section>

    {{-- ======================= TESTIMONIALS ======================= --}}
    <section class="bg-white py-20 text-slate-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-600">Loved by institutes</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Institutes run on it every day</h2>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach([
                    ['AK', 'from-indigo-500 to-blue-500', 'Principal', 'Technical Training Institute', 'We used to lose two days a month to paperwork. Now admissions, attendance and certificates are one click each.'],
                    ['NJ', 'from-fuchsia-500 to-pink-600', 'Trainer', 'Electrical & Construction Skills', 'QR attendance ended the "I was present" arguments. Students see their own percentage in the portal.'],
                    ['RH', 'from-amber-500 to-orange-600', 'Student', 'Batch EIM-12', 'I applied from my phone, got shortlisted in a day, and my certificate has a QR code employers can scan.'],
                ] as [$initials, $gradient, $role, $org, $quote])
                    <figure class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                        <div class="text-amber-400">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <blockquote class="mt-3 text-sm leading-relaxed text-slate-600">“{{ $quote }}”</blockquote>
                        <figcaption class="mt-4 flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br {{ $gradient }} text-sm font-black text-white">{{ $initials }}</span>
                            <div>
                                <p class="text-sm font-black">{{ $role }}</p>
                                <p class="text-xs text-slate-400">{{ $org }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======================= FAQ ======================= --}}
    <section id="faq" class="bg-slate-50 py-20 text-slate-900">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <div class="text-center">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-600">Questions?</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Frequently asked</h2>
            </div>

            <div class="mt-10 space-y-3">
                @foreach([
                    ['Who is STP SaaS for?', 'Technical and vocational training institutes that want admissions, attendance, exams, certificates, ID cards and accounts in one place — plus a student portal and public QR-verified certificates.'],
                    ['How do students apply?', 'They open the public Apply page, pick their institute and course, upload a photo, and submit. Institute staff then shortlist and admit them from the CRM dashboard.'],
                    ['Are certificates verifiable?', 'Yes. Every certificate and ID card carries a QR code that opens a public verification profile with the holder\'s photo, course and issue details.'],
                    ['Does it work on mobile?', 'Yes — the apply form, student portal and QR attendance all work on any modern phone browser.'],
                ] as [$q, $a])
                    <details class="group rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm open:shadow-md">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-black">
                            {{ $q }}
                            <i class="bi bi-plus-lg shrink-0 text-indigo-500 transition group-open:rotate-45"></i>
                        </summary>
                        <p class="mt-2.5 text-sm leading-relaxed text-slate-500">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======================= FINAL CTA ======================= --}}
    <section class="relative overflow-hidden bg-slate-950 py-20">
        <div class="anim-blob pointer-events-none absolute left-1/2 top-0 h-96 w-[40rem] -translate-x-1/2 rounded-full bg-fuchsia-600/25 blur-3xl"></div>
        <div class="relative mx-auto max-w-3xl px-4 text-center sm:px-6">
            <h2 class="text-3xl font-black leading-tight text-white sm:text-5xl">
                Seats for the next batch are <span class="bg-gradient-to-r from-amber-300 to-fuchsia-400 bg-clip-text text-transparent">filling fast.</span>
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-slate-300">
                Apply in minutes, or create an institute account and run your entire training operation from one dashboard.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('apply') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-fuchsia-600 px-7 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-500/40 transition hover:scale-[1.03] hover:brightness-110">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                    Apply Now
                </a>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-7 py-3.5 text-sm font-bold text-white backdrop-blur transition hover:border-white/40 hover:bg-white/10">
                    <i class="bi bi-person-plus-fill"></i>
                    Register Free
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-5 py-3.5 text-sm font-bold text-slate-300 transition hover:text-white">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login
                </a>
            </div>
        </div>
    </section>

    {{-- ======================= FOOTER ======================= --}}
    <footer class="border-t border-white/10 bg-slate-950 py-10 text-slate-400">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-5 px-4 sm:flex-row sm:px-6">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-fuchsia-600 text-xs font-black text-white">ST</span>
                <span class="text-sm font-extrabold text-white">{{ config('app.name', 'STP SaaS') }}</span>
            </div>
            <nav class="flex items-center gap-6 text-sm font-semibold">
                <a href="{{ route('apply') }}" class="transition hover:text-white">Apply</a>
                <a href="{{ route('register') }}" class="transition hover:text-white">Register</a>
                <a href="{{ route('login') }}" class="transition hover:text-white">Login</a>
            </nav>
            <p class="text-xs">© {{ date('Y') }} {{ config('app.name', 'STP SaaS') }} · All rights reserved</p>
        </div>
    </footer>

</body>
</html>
