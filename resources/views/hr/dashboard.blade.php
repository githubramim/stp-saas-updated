<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('HR & Employee Management') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Manage trainers and staff, salary structure, leave balance, attendance and performance.') }}</p>
            </div>
            <a href="{{ route('hr.employees.create') }}" class="btn btn-primary rounded-3">
                <i class="bi bi-person-plus me-1"></i> {{ __('Add Employee') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('hr.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Employees') }}</div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ $staff->count() }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $staff->where('role', 'trainer')->count() }} {{ __('trainers') }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Present Today') }}</div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">
                        {{ (int) ($todayRows['present'] ?? 0) + (int) ($todayRows['late'] ?? 0) }}
                    </div>
                    <div class="mt-1 text-xs text-slate-400">{{ (int) ($todayRows['absent'] ?? 0) }} {{ __('absent') }}</div>
                </div>
                <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Payroll This Month') }}</div>
                    <div class="mt-2 text-2xl font-black text-indigo-600">৳{{ number_format($payrollTotal, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Pending Leaves') }}</div>
                    <div class="mt-2 text-2xl font-black text-amber-600">{{ $pendingLeaves->count() }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('awaiting approval') }}</div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">{{ __('Last 7 Days Attendance') }}</h3>
                    <div class="space-y-3">
                        @forelse($weekly as $day)
                            <div class="flex items-center gap-3">
                                <span class="w-16 text-[11px] font-semibold text-slate-500">{{ $day['label'] }}</span>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                    @php $max = max(1, $staff->count()); @endphp
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, ($day['present'] / $max) * 100) }}%"></div>
                                </div>
                                <span class="w-20 text-right text-[11px] text-slate-500">{{ $day['present'] }} {{ __('present') }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">{{ __('No attendance data yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">{{ __('Top Performers') }}</h3>
                    <div class="space-y-3">
                        @forelse($topPerformers as $item)
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2 last:border-0">
                                <div>
                                    <div class="text-sm font-semibold text-slate-800">{{ $item['user']->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $item['rating_count'] }} {{ __('student ratings') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-indigo-600">
                                        {{ $item['official_rating'] > 0 ? number_format($item['official_rating'], 1) : '—' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">{{ __('out of 5') }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">{{ __('No trainers in this branch yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($pendingLeaves->isNotEmpty())
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">{{ __('Recent Leave Requests') }}</h3>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 small">
                            <thead class="table-light"><tr><th>{{ __('Employee') }}</th><th>{{ __('Type') }}</th><th>{{ __('Period') }}</th><th>{{ __('Days') }}</th></tr></thead>
                            <tbody>
                                @foreach($pendingLeaves as $leave)
                                    <tr>
                                        <td class="font-semibold">{{ $leave->user?->name ?? '—' }}</td>
                                        <td>{{ \App\Models\EmployeeLeave::TYPES[$leave->leave_type] ?? $leave->leave_type }}</td>
                                        <td>{{ $leave->dateRangeLabel() }}</td>
                                        <td>{{ $leave->days }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
