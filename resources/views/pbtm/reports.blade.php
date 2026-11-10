<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">
                    <i class="bi bi-file-earmark-bar-graph me-1 text-indigo-500"></i>
                    {{ __('Monthly / Quarterly Progress Reports') }}
                </h2>
                <p class="text-xs text-slate-400">{{ __('NSDA / SEIP-ready enrolment, attendance, stipend and placement progress.') }}</p>
            </div>
            <a href="{{ route('pbtm.reports.csv', array_filter(['project_id' => $project?->id, 'year' => $year, 'view' => $view])) }}" class="btn btn-outline-success rounded-3 font-bold">
                <i class="bi bi-file-earmark-excel me-1"></i> {{ __('Export CSV') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @include('pbtm.partials.nav')

            <div class="mb-5 flex flex-wrap items-end justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div>
                        <label for="report_year" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Report Year') }}</label>
                        <select id="report_year" name="year" class="form-select form-select-sm rounded-xl" onchange="this.form.submit()">
                            @foreach(range(now()->year, now()->year - 6) as $optionYear)
                                <option value="{{ $optionYear }}" {{ $year === $optionYear ? 'selected' : '' }}>{{ $optionYear }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="report_view" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Frequency') }}</label>
                        <select id="report_view" name="view" class="form-select form-select-sm rounded-xl" onchange="this.form.submit()">
                            <option value="monthly" {{ $view === 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                            <option value="quarterly" {{ $view === 'quarterly' ? 'selected' : '' }}>{{ __('Quarterly') }}</option>
                        </select>
                    </div>
                </form>

                <div class="text-right text-xs text-slate-500">
                    <div class="font-bold uppercase tracking-wide text-slate-400">{{ __('Reporting Entity') }}</div>
                    <div class="text-sm font-semibold text-slate-800">{{ $project?->name ?? __('All Projects') }}</div>
                    <div>
                        {{ $project?->donor_name ?: __('No donor recorded') }}
                        @if($project?->mou_number)
                            · MoU {{ $project->mou_number }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Total Trainees') }}</div>
                    <div class="mt-1 text-2xl font-black text-slate-800">{{ number_format($summary['trainees']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Completed') }}</div>
                    <div class="mt-1 text-2xl font-black text-emerald-600">{{ number_format($summary['completed']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Placed') }}</div>
                    <div class="mt-1 text-2xl font-black text-amber-600">{{ number_format($summary['placed']) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Stipend Paid') }}</div>
                    <div class="mt-1 text-2xl font-black text-teal-700">৳{{ number_format($summary['stipend_paid'], 0) }}</div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">{{ __('Progress by') }} {{ $view === 'quarterly' ? __('Quarter') : __('Month') }} — {{ $year }}</h3>
                    @if($view === 'monthly')
                        <div class="flex flex-wrap gap-2 text-[11px] font-semibold">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">{{ __('Q1: Jan–Mar') }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">{{ __('Q2: Apr–Jun') }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">{{ __('Q3: Jul–Sep') }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">{{ __('Q4: Oct–Dec') }}</span>
                        </div>
                    @else
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700">{{ __('NSDA / SEIP quarterly format') }}</span>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" style="min-width: 860px;">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $view === 'quarterly' ? __('Quarter') : __('Month') }}</th>
                                <th class="text-center">{{ __('New Enrolments') }}</th>
                                <th class="text-center">{{ __('Selected / Admitted') }}</th>
                                <th class="text-end">{{ __('Stipend Paid') }}</th>
                                <th class="text-center">{{ __('Attendance') }}</th>
                                <th class="text-center">{{ __('Job Placements') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td class="font-semibold text-slate-700">{{ $row['label'] }}</td>
                                    <td class="text-center">{{ number_format($row['enrolled']) }}</td>
                                    <td class="text-center">{{ number_format($row['selected']) }}</td>
                                    <td class="text-end">৳{{ number_format($row['stipend_paid'], 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $row['attendance'] >= 80 ? 'bg-success' : ($row['attendance'] >= 60 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ number_format($row['attendance'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">{{ number_format($row['placements']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>{{ __('Total') }}</td>
                                <td class="text-center">{{ number_format($totals['enrolled']) }}</td>
                                <td class="text-center">{{ number_format($totals['selected']) }}</td>
                                <td class="text-end">৳{{ number_format($totals['stipend_paid'], 2) }}</td>
                                <td class="text-center">{{ number_format($totals['attendance'], 1) }}%</td>
                                <td class="text-center">{{ number_format($totals['placements']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="grid grid-cols-2 gap-px border-t border-slate-100 bg-slate-100 sm:grid-cols-4">
                    <div class="bg-white p-4 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Assets Distributed') }}</div>
                        <div class="text-lg font-black text-slate-800">{{ number_format($summary['assets_distributed']) }}</div>
                    </div>
                    <div class="bg-white p-4 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Attainment') }}</div>
                        <div class="text-lg font-black text-indigo-600">
                            {{ $project?->target_trainees > 0 ? round(($summary['trainees'] / max($project->target_trainees, 1)) * 100, 1) : 0 }}%
                        </div>
                    </div>
                    <div class="bg-white p-4 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Placement Rate') }}</div>
                        <div class="text-lg font-black text-amber-600">
                            {{ $summary['completed'] > 0 ? round(($summary['placed'] / max($summary['completed'], 1)) * 100, 1) : 0 }}%
                        </div>
                    </div>
                    <div class="bg-white p-4 text-center">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Project Budget') }}</div>
                        <div class="text-lg font-black text-slate-800">৳{{ number_format((float) $project?->budget_amount, 0) }}</div>
                    </div>
                </div>
            </div>

            <p class="mt-4 text-[11px] text-slate-400">
                <i class="bi bi-info-circle me-1"></i>
                {{ __('CSV export includes project, donor, MoU, budget and monthly progress columns for NSDA / SEIP submission.') }}
            </p>
        </div>
    </div>
</x-app-layout>
