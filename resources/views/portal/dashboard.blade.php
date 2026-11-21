<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $student->name }} - Student Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-900 text-slate-100 min-h-screen">
    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-950/80 backdrop-blur sticky top-0 z-50 py-4 px-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-xl text-white shadow-lg shadow-indigo-500/30">
                    STP
                </div>
                <div>
                    <span class="font-bold text-lg tracking-tight block leading-none">Student Portal</span>
                    <span class="text-xs text-slate-400 font-mono">{{ $student->reference_no }}</span>
                </div>
            </div>
            <a href="{{ route('portal.index') }}" class="text-xs text-slate-400 hover:text-white transition bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                ← Exit Portal
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Student Profile Card -->
        <div class="bg-slate-800/80 rounded-2xl border border-slate-700/80 p-6 md:p-8 backdrop-blur shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-2xl font-bold text-indigo-400">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $student->name }}</h1>
                        <p class="text-sm text-slate-400 mt-0.5">Contact: {{ $student->contact }} | Institute: {{ $student->institute?->name }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-3 py-1 text-xs font-semibold uppercase tracking-wider rounded-lg border
                        {{ $student->status === 'admitted' || $student->status === 'active' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : '' }}
                        {{ $student->status === 'applied' ? 'bg-amber-500/10 border-amber-500/30 text-amber-400' : '' }}">
                        Status: {{ strtoupper($student->status) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-700/60">
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Assigned Course</span>
                    <span class="font-semibold text-indigo-300">{{ $student->course?->name ?? 'Not Enrolled' }}</span>
                </div>
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Assigned Batch</span>
                    <span class="font-semibold text-indigo-300">{{ $student->batch?->name ?? 'Pending Assignment' }}</span>
                </div>
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Assigned Trainer</span>
                    <span class="font-semibold text-indigo-300">{{ $student->batch?->trainer?->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Fee summary & dues (BRD module #8) -->
        <div class="bg-slate-800/80 rounded-2xl border border-slate-700/80 p-6 md:p-8 backdrop-blur shadow-xl">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">💳 Fee Summary & Dues</h2>
                <span class="text-xs text-slate-400">SMS &amp; portal reminders are active</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Total Payable</span>
                    <span class="font-semibold text-sky-300">৳{{ number_format((float) ($feeSummary['net'] ?? 0), 2) }}</span>
                </div>
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Paid</span>
                    <span class="font-semibold text-emerald-400">৳{{ number_format((float) ($feeSummary['paid'] ?? 0), 2) }}</span>
                </div>
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Outstanding</span>
                    <span class="font-semibold text-rose-400">৳{{ number_format((float) ($feeSummary['due'] ?? 0), 2) }}</span>
                </div>
                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <span class="text-xs font-medium text-slate-400 block mb-1">Overdue</span>
                    <span class="font-semibold text-amber-400">৳{{ number_format((float) ($feeSummary['overdue'] ?? 0), 2) }}</span>
                </div>
            </div>

            @if($nextInstallment)
                <div class="mt-4 p-3 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-sm">
                    Next installment #{{ $nextInstallment->sequence }} of ৳{{ number_format((float) $nextInstallment->balance, 2) }}
                    is due on {{ $nextInstallment->due_date?->format('d M Y') ?? 'soon' }}.
                </div>
            @endif

            @if($feeInstallments->isNotEmpty())
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-slate-400 border-b border-slate-700">
                            <tr>
                                <th class="py-2">#</th>
                                <th class="py-2">Due Date</th>
                                <th class="py-2 text-right">Amount</th>
                                <th class="py-2 text-right">Paid</th>
                                <th class="py-2 text-right">Balance</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeInstallments->take(6) as $installment)
                                <tr class="border-b border-slate-800/70">
                                    <td class="py-2 text-slate-300">{{ $installment->sequence }}</td>
                                    <td class="py-2 text-slate-400">{{ $installment->due_date?->format('d M Y') ?? '—' }}</td>
                                    <td class="py-2 text-right text-slate-300">৳{{ number_format((float) $installment->amount, 2) }}</td>
                                    <td class="py-2 text-right text-emerald-400">৳{{ number_format((float) $installment->paid_amount, 2) }}</td>
                                    <td class="py-2 text-right text-rose-400">৳{{ number_format((float) $installment->balance, 2) }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded-lg text-xs bg-slate-700 text-slate-200 uppercase">{{ $installment->status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500">No fee plan has been assigned yet.</p>
            @endif

            @if($feeReminders->isNotEmpty())
                <div class="mt-5 border-t border-slate-700 pt-4">
                    <h3 class="text-sm font-semibold text-slate-300 mb-2">Recent Reminders</h3>
                    <div class="space-y-2">
                        @foreach($feeReminders as $reminder)
                            <div class="text-xs text-slate-400 bg-slate-900/60 rounded-lg p-2">
                                <span class="font-semibold uppercase text-indigo-400">{{ $reminder->channel }}</span>
                                · {{ $reminder->due_date?->format('d M Y') ?? '—' }} · {{ \Illuminate\Support\Str::limit($reminder->message, 140) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Downloadable Study Materials -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        📚 Course Study Materials & Library
                    </h2>
                    <span class="text-xs text-slate-400">{{ $studyMaterials->count() }} Files Available</span>
                </div>

                @if($studyMaterials->isEmpty())
                    <div class="bg-slate-800/40 rounded-2xl border border-slate-800 p-8 text-center text-slate-500">
                        No study materials uploaded for this course yet.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($studyMaterials as $material)
                            <div class="bg-slate-800/80 rounded-xl border border-slate-700/60 p-4 hover:border-indigo-500/50 transition flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold text-slate-200 text-sm">{{ $material->title }}</h3>
                                    <p class="text-xs text-slate-400 mt-1">
                                        Module: {{ $material->module_name ?? 'General' }} | Type: {{ strtoupper($material->file_type) }}
                                    </p>
                                </div>
                                <a href="{{ route('portal.material.download', $material) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 rounded-lg text-xs font-medium transition">
                                    ⬇️ Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Submit Trainer Evaluation Form -->
            <div class="space-y-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    ⭐ Trainer & Course Evaluation
                </h2>

                <div class="bg-slate-800/80 rounded-2xl border border-slate-700/80 p-6 backdrop-blur">
                    @if(!$student->batch || !$student->batch->trainer_id)
                        <p class="text-xs text-slate-400">Evaluation will open once you are assigned to an active batch and trainer.</p>
                    @else
                        <form action="{{ route('portal.evaluation.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                                    Rating (1 to 5 Stars) <span class="text-indigo-400">*</span>
                                </label>
                                <select name="rating_score" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                                    <option value="5">⭐⭐⭐⭐⭐ 5 - Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ 4 - Good</option>
                                    <option value="3">⭐⭐⭐ 3 - Average</option>
                                    <option value="2">⭐⭐ 2 - Poor</option>
                                    <option value="1">⭐ 1 - Very Bad</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                                    Review Comments / Feedback
                                </label>
                                <textarea name="review_comments" rows="4" placeholder="Write your feedback regarding trainer teaching quality and course materials..."
                                          class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" maxlength="1000"></textarea>
                            </div>

                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="is_anonymous" value="1" id="is_anonymous" checked class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                <label for="is_anonymous" class="text-xs text-slate-300">Submit feedback anonymously</label>
                            </div>

                            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition uppercase tracking-wider">
                                Submit Feedback
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>
