<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $institute->name }} — Dashboard
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            {{-- Welcome --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl shadow-lg p-6 mb-8 text-white">
                <h1 class="text-2xl font-bold">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-indigo-200 mt-1 text-sm">Master overview of all projects and activities across all institutes.</p>
            </div>

            {{-- Quick Actions Toolbar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Quick Actions</h3>
                    <span class="text-xs text-gray-400 font-medium">Institute Code: {{ $institute->code }}</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <a href="{{ route('batches.index', ['create' => 1]) }}" class="group bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-xl p-4 transition block">
                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-600 flex items-center justify-center mb-2">
                            <i class="bi bi-collection-play text-xl"></i>
                        </div>
                        <div class="text-sm font-semibold text-gray-800">Add Batch</div>
                        <div class="text-xs text-gray-500">Create new batch</div>
                    </a>
                    <a href="{{ route('admissions.applications') }}" class="group bg-amber-50 hover:bg-amber-100 border border-amber-100 rounded-xl p-4 transition block">
                        <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center mb-2">
                            <i class="bi bi-file-earmark-person text-xl"></i>
                        </div>
                        <div class="text-sm font-semibold text-gray-800">Review Applications</div>
                        <div class="text-xs text-gray-500">Approve / reject intake</div>
                    </a>
                    <a href="{{ route('transactions.index', ['create' => 1]) }}" class="group bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-xl p-4 transition block">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center mb-2">
                            <i class="bi bi-wallet2 text-xl"></i>
                        </div>
                        <div class="text-sm font-semibold text-gray-800">Record Income / Expense</div>
                        <div class="text-xs text-gray-500">Fee & expense entry</div>
                    </a>
                    <a href="{{ route('notifications.index', ['create' => 1]) }}" class="group bg-violet-50 hover:bg-violet-100 border border-violet-100 rounded-xl p-4 transition block">
                        <div class="w-10 h-10 rounded-lg bg-violet-500/10 text-violet-600 flex items-center justify-center mb-2">
                            <i class="bi bi-send text-xl"></i>
                        </div>
                        <div class="text-sm font-semibold text-gray-800">Send SMS Notice</div>
                        <div class="text-xs text-gray-500">Bulk / single alerts</div>
                    </a>
                    <a href="{{ route('resources.index', ['create' => 1]) }}" class="group bg-rose-50 hover:bg-rose-100 border border-rose-100 rounded-xl p-4 transition block">
                        <div class="w-10 h-10 rounded-lg bg-rose-500/10 text-rose-600 flex items-center justify-center mb-2">
                            <i class="bi bi-plus-circle text-xl"></i>
                        </div>
                        <div class="text-sm font-semibold text-gray-800">Add Resource</div>
                        <div class="text-xs text-gray-500">Lab equipment entry</div>
                    </a>
                </div>
            </div>

            {{-- Financial Metric Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-sm text-gray-500 uppercase tracking-wide">Current Month Income</div>
                    <div class="text-2xl font-bold text-green-600 mt-2">৳{{ number_format($monthlyIncome, 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">Income collected this month</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-sm text-gray-500 uppercase tracking-wide">Monthly Expense</div>
                    <div class="text-2xl font-bold text-red-500 mt-2">৳{{ number_format($monthlyExpense, 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">Expenses incurred this month</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-sm text-gray-500 uppercase tracking-wide">Net Cashflow</div>
                    <div class="text-2xl font-bold {{ $netCashflow >= 0 ? 'text-green-600' : 'text-red-500' }} mt-2">৳{{ number_format($netCashflow, 2) }}</div>
                    <div class="text-xs text-gray-400 mt-1">Net income vs expense (this month)</div>
                </div>
            </div>

            {{-- Visual Analytics: Financial Performance & Course Distribution --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Financial Performance — Last 6 Months</h3>
                    <div class="h-64"><canvas id="financialChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Course-wise Student Distribution</h3>
                    @if($courseDistribution->sum('student_count') > 0)
                        <div class="h-64"><canvas id="courseDistributionChart"></canvas></div>
                    @else
                        <div class="flex h-64 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                            <span class="mb-2 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                                <i class="bi bi-pie-chart text-xl"></i>
                            </span>
                            <p class="text-sm font-medium text-slate-600">No enrollment data yet</p>
                            <p class="mt-0.5 text-xs text-slate-400">Admitted students will appear here by course.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Damaged / Maintenance Lab Resource Alerts --}}
            @if($damagedResourcesCount > 0)
                <div class="bg-amber-50 border border-amber-200 rounded-xl shadow-sm p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-amber-800">⚠️ Damaged / Maintenance Lab Resources</h3>
                        <a href="{{ route('resources.index') }}" class="btn btn-outline-amber">View All</a>
                    </div>
                    <ul class="space-y-2">
                        @foreach($damagedResources as $resource)
                            <li class="flex items-center justify-between bg-white rounded-lg px-4 py-3 border border-amber-100 text-sm">
                                <div>
                                    <span class="font-semibold text-gray-800">{{ $resource->resource_name }}</span>
                                    <span class="text-gray-500"> — {{ $resource->location_lab }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $resource->condition === 'damaged' ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($resource->condition) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Overview Stats --}}
            @php
                $statCards = [
                    ['label' => 'Institute', 'value' => $institute->code, 'icon' => 'bi-building', 'tone' => 'bg-indigo-50 text-indigo-600'],
                    ['label' => 'Courses', 'value' => $courseCount, 'icon' => 'bi-journal-text', 'tone' => 'bg-teal-50 text-teal-600'],
                    ['label' => 'Batches', 'value' => $batchCount, 'icon' => 'bi-people', 'tone' => 'bg-violet-50 text-violet-600'],
                    ['label' => 'Applied', 'value' => $appliedCount, 'icon' => 'bi-file-earmark-person', 'tone' => 'bg-amber-50 text-amber-600'],
                    ['label' => 'Admitted', 'value' => $admittedCount, 'icon' => 'bi-person-check', 'tone' => 'bg-emerald-50 text-emerald-600'],
                    ['label' => 'Rejected', 'value' => $rejectedCount, 'icon' => 'bi-person-x', 'tone' => 'bg-rose-50 text-rose-600'],
                    ['label' => 'Students', 'value' => $studentCount, 'icon' => 'bi-mortarboard', 'tone' => 'bg-sky-50 text-sky-600'],
                    ['label' => 'Trainers', 'value' => $trainerCount, 'icon' => 'bi-person-workspace', 'tone' => 'bg-orange-50 text-orange-600'],
                ];
            @endphp

            <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 xl:grid-cols-4 2xl:grid-cols-8">
                @foreach($statCards as $card)
                    <div class="flex min-w-0 items-center gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $card['tone'] }}">
                            <i class="bi {{ $card['icon'] }} text-lg"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[11px] font-medium uppercase tracking-wide text-gray-400">{{ $card['label'] }}</span>
                            <span class="block truncate text-lg font-bold text-gray-800">{{ $card['value'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Attendance & Results --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800">Today's Attendance</h3>
                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-indigo">Manage</a>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Sessions</div>
                            <div class="text-xl font-bold text-gray-800">{{ $todaySessions }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Present</div>
                            <div class="text-xl font-bold text-emerald-600">{{ $todayPresent }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Absent</div>
                            <div class="text-xl font-bold text-red-500">{{ $todayAbsent }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Rate</div>
                            <div class="text-xl font-bold text-indigo-600">
                                {{ $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 1).'%' : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800">Exams &amp; Results</h3>
                        <a href="{{ route('exams.index') }}" class="btn btn-outline-indigo">Manage</a>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Exams</div>
                            <div class="text-xl font-bold text-gray-800">{{ $examCount }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Published</div>
                            <div class="text-xl font-bold text-emerald-600">{{ $publishedExamCount }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide">Awaiting</div>
                            <div class="text-xl font-bold text-amber-600">{{ $awaitingPublication }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Low Attendance Warnings (<80%) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Low Attendance Warnings (&lt; 80%)</h3>
                    <a href="{{ route('attendance.index') }}" class="btn btn-outline-indigo">Manage</a>
                </div>

                @if($lowAttendanceStudents->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-3 font-medium">Student</th>
                                    <th class="text-left p-3 font-medium">Batch</th>
                                    <th class="text-left p-3 font-medium">Attendance</th>
                                    <th class="text-left p-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowAttendanceStudents as $student)
                                    <tr class="border-b">
                                        <td class="p-3 font-medium">{{ $student->name_en }}</td>
                                        <td class="p-3 text-gray-500">{{ $student->batch?->name ?? '—' }}</td>
                                        <td class="p-3">
                                            <span class="font-semibold {{ $student->attendanceSummary()['percentage'] < 80 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ number_format($student->attendanceSummary()['percentage'], 1) }}%
                                            </span>
                                        </td>
                                        <td class="p-3 text-gray-500">{{ ucfirst($student->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-400">No students currently below the 80% attendance threshold. 🎉</p>
                @endif
            </div>

            {{-- Project Cards --}}
            @if($projects->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Projects
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-indigo ml-auto">Manage</a>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @php
                            $gradients = [
                                'from-violet-600 to-indigo-700',
                                'from-emerald-500 to-teal-700',
                                'from-rose-500 to-pink-700',
                                'from-amber-500 to-orange-700',
                                'from-cyan-500 to-blue-700',
                                'from-fuchsia-500 to-purple-700',
                            ];
                            $i = 0;
                        @endphp
                        @foreach($projectStats as $stat)
                            @php $g = $gradients[$i % count($gradients)]; $i++; @endphp
                            <div class="rounded-2xl shadow-md overflow-hidden group hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <a href="{{ route('projects.dashboard', $stat['project']) }}" class="block bg-gradient-to-br {{ $g }} p-5 text-white hover:brightness-110 transition">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-bold text-lg">{{ $stat['project']->name }}</h4>
                                            @if($stat['project']->code)
                                                <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full">{{ $stat['project']->code }}</span>
                                            @endif
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded-full {{ $stat['project']->status === 'active' ? 'bg-green-400/30 text-green-100' : 'bg-red-400/30 text-red-100' }}">
                                            {{ ucfirst($stat['project']->status) }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-center mt-4">
                                        <div class="bg-white/15 rounded-lg p-2">
                                            <div class="text-xl font-bold">{{ $stat['courses'] }}</div>
                                            <div class="text-xs text-white/80">Courses</div>
                                        </div>
                                        <div class="bg-white/15 rounded-lg p-2">
                                            <div class="text-xl font-bold">{{ $stat['batches'] }}</div>
                                            <div class="text-xs text-white/80">Batches</div>
                                        </div>
                                        <div class="bg-white/15 rounded-lg p-2">
                                            <div class="text-xl font-bold">{{ $stat['students'] }}</div>
                                            <div class="text-xs text-white/80">Students</div>
                                        </div>
                                    </div>
                                    <div class="flex gap-3 mt-3 text-xs">
                                        <span class="bg-yellow-400/20 px-2 py-0.5 rounded">Applied: {{ $stat['applied'] }}</span>
                                        <span class="bg-green-400/20 px-2 py-0.5 rounded">Admitted: {{ $stat['admitted'] }}</span>
                                    </div>
                                </a>
                                <div class="bg-white p-3 flex gap-2 border-t border-gray-50">
                                    <a href="{{ route('projects.dashboard', $stat['project']) }}" class="btn btn-outline-sky">Open</a>
                                    <form action="{{ route('projects.switch', $stat['project']) }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="redirect" value="{{ route('courses.index') }}">
                                        <button type="submit" class="btn btn-outline-violet">Courses</button>
                                    </form>
                                    <form action="{{ route('projects.switch', $stat['project']) }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="redirect" value="{{ route('admissions.students') }}">
                                        <button type="submit" class="btn btn-outline-rose">Students</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                    <p class="text-gray-400 mb-4">No projects yet. Create your first project to get started.</p>
                    <a href="{{ route('projects.index', ['create' => 1]) }}" class="btn btn-solid-indigo">+ Create Project</a>
                </div>
            @endif

            {{-- Recent Students Across All Projects --}}
            @if($allStudents->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        Recent Students (All Projects)
                    </h3>
                    <div class="flex flex-wrap justify-end gap-2">
                        <span class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-700">{{ $appliedCount }} Applied</span>
                        <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">{{ $admittedCount }} Admitted</span>
                        <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">{{ $rejectedCount }} Rejected</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left p-3 font-medium">Name</th>
                                <th class="text-left p-3 font-medium">Project</th>
                                <th class="text-left p-3 font-medium">Course</th>
                                <th class="text-left p-3 font-medium">Batch</th>
                                <th class="text-left p-3 font-medium">Status</th>
                                <th class="text-left p-3 font-medium">Date</th>
                                <th class="text-left p-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allStudents as $s)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3 font-medium">{{ $s->name_en }}</td>
                                    <td class="p-3 text-gray-500">{{ $s->project?->name ?? '—' }}</td>
                                    <td class="p-3 text-gray-500">{{ $s->course?->name ?? '—' }}</td>
                                    <td class="p-3 text-gray-500">{{ $s->batch?->name ?? '—' }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs rounded
                                            @if($s->status === 'admitted') bg-green-100 text-green-700
                                             @elseif($s->status === 'rejected') bg-red-100 text-red-700
                                             @else bg-yellow-100 text-yellow-700
                                             @endif">{{ ucfirst($s->status) }}</span>
                                    </td>
                                    <td class="p-3 text-gray-400 text-xs">{{ $s->created_at->format('d M Y') }}</td>
                                    <td class="p-3">
                                        <div class="flex gap-1.5">
                                            <a href="{{ route('admissions.show', $s) }}" class="btn btn-outline-sky" target="_blank">View</a>
                                            <a href="{{ route('admissions.print', $s) }}" class="btn btn-outline-indigo" target="_blank">Print</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const financialCanvas = document.getElementById('financialChart');
            if (financialCanvas && typeof Chart !== 'undefined') {
                const financial = @json($financialTrend->toArray());

                new Chart(financialCanvas, {
                    type: 'bar',
                    data: {
                        labels: financial.map((t) => t.label),
                        datasets: [
                            {
                                label: 'Income',
                                data: financial.map((t) => Number(t.income) || 0),
                                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderWidth: 1,
                                borderRadius: 6,
                            },
                            {
                                label: 'Expense',
                                data: financial.map((t) => Number(t.expense) || 0),
                                backgroundColor: 'rgba(239, 68, 68, 0.85)',
                                borderColor: 'rgba(239, 68, 68, 1)',
                                borderWidth: 1,
                                borderRadius: 6,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } },
                    },
                });
            }

            const distributionCanvas = document.getElementById('courseDistributionChart');
            if (distributionCanvas && typeof Chart !== 'undefined') {
                const distributionData = @json($courseDistribution->toArray());
                const distribution = distributionData
                    .filter((c) => Number(c.student_count) > 0)
                    .map((c) => ({ label: c.name, value: Number(c.student_count) }));

                const colors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#06b6d4', '#8b5cf6', '#f97316', '#84cc16'];

                new Chart(distributionCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: distribution.map((c) => c.label),
                        datasets: [{
                            label: 'Students',
                            data: distribution.map((c) => c.value),
                            backgroundColor: distribution.map((c, i) => colors[i % colors.length]),
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                    },
                });
            }
        });
    </script>
</x-app-layout>
