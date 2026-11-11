<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Marketing & Lead Management') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Capture leads, follow up on time and watch the conversion funnel fill up.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('crm.reminders') }}" class="btn btn-outline-primary rounded-3 position-relative">
                    <i class="bi bi-bell me-1"></i> {{ __('Reminders') }}
                    @if($overdueCount + $todayCount > 0)
                        <span class="badge bg-danger ms-1">{{ $overdueCount + $todayCount }}</span>
                    @endif
                </a>
                <a href="{{ route('crm.leads', ['create' => 1]) }}" class="btn btn-primary rounded-3">
                    <i class="bi bi-plus-circle-fill me-1"></i> {{ __('New Lead') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('crm.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Primary stats --}}
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Leads') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="bi bi-inboxes"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($totalLeads) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $thisMonth }} {{ __('new this month') }}</div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Open Pipeline') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="bi bi-funnel"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-amber-600">{{ number_format($open) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $lost }} {{ __('lost so far') }}</div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Enrolled') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bi bi-mortarboard"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">{{ number_format($converted) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $convertedThisMonth }} {{ __('converted this month') }}</div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Conversion Rate') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="bi bi-graph-up-arrow"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-indigo-600">{{ number_format($conversionRate, 1) }}%</div>
                    <div class="mt-1 text-xs {{ $monthGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ $monthGrowth >= 0 ? '▲' : '▼' }} {{ number_format(abs($monthGrowth), 1) }}% {{ __('lead growth vs last month') }}
                    </div>
                </div>
            </div>

            {{-- Reminder strip --}}
            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <a href="{{ route('crm.reminders', ['bucket' => 'overdue']) }}"
                   class="flex items-center justify-between rounded-xl border px-4 py-3 transition {{ $overdueCount > 0 ? 'border-rose-200 bg-rose-50 hover:bg-rose-100' : 'border-slate-200 bg-slate-50' }}">
                    <span class="text-xs font-semibold {{ $overdueCount > 0 ? 'text-rose-700' : 'text-slate-500' }}"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('Overdue follow-ups') }}</span>
                    <span class="text-sm font-bold {{ $overdueCount > 0 ? 'text-rose-700' : 'text-slate-700' }}">{{ $overdueCount }}</span>
                </a>
                <a href="{{ route('crm.reminders', ['bucket' => 'today']) }}"
                   class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 transition hover:bg-amber-100">
                    <span class="text-xs font-semibold text-amber-700"><i class="bi bi-sun me-1"></i>{{ __('Due today') }}</span>
                    <span class="text-sm font-bold text-amber-700">{{ $todayCount }}</span>
                </a>
                <a href="{{ route('crm.reminders', ['bucket' => 'upcoming']) }}"
                   class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:bg-slate-100">
                    <span class="text-xs font-semibold text-slate-500"><i class="bi bi-calendar-week me-1"></i>{{ __('Next 7 days') }}</span>
                    <span class="text-sm font-bold text-slate-700">{{ $upcomingCount }}</span>
                </a>
            </div>

            <div class="mb-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
                {{-- Funnel --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-filter-square me-1 text-indigo-500"></i>{{ __('Conversion Funnel') }}</h3>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('All time') }}</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($funnel as $stage)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-600">
                                        <i class="bi {{ $stage['icon'] }} me-1 text-slate-400"></i>{{ $stage['label'] }}
                                    </span>
                                    <span class="font-bold text-slate-800">
                                        {{ number_format($stage['count']) }}
                                        <span class="ms-1 text-[11px] font-semibold text-slate-400">{{ number_format($stage['percent'], 1) }}%</span>
                                    </span>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-violet-500 to-emerald-500"
                                         style="width: {{ $stage['count'] > 0 ? max(3, $stage['percent']) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-3 text-[11px] text-slate-500">
                        <span><i class="bi bi-circle-fill me-1 text-emerald-500"></i>{{ __('Enrolled') }}: <strong>{{ $converted }}</strong></span>
                        <span><i class="bi bi-circle-fill me-1 text-rose-400"></i>{{ __('Lost') }}: <strong>{{ $lost }}</strong></span>
                        <span><i class="bi bi-whatsapp me-1 text-emerald-600"></i>{{ __('WhatsApp this month') }}: <strong>{{ $whatsappThisMonth }}</strong></span>
                    </div>
                </div>

                {{-- Source performance --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-megaphone me-1 text-rose-500"></i>{{ __('Lead Sources') }}</h3>
                        <a href="{{ route('crm.leads') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('View all') }} →</a>
                    </div>

                    @if($sourcePerformance->isEmpty())
                        <div class="flex h-48 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                            <i class="bi bi-megaphone mb-2 text-2xl text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">{{ __('No leads captured yet') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($sourcePerformance as $row)
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-xs">
                                        <span class="font-semibold text-slate-600"><i class="bi {{ $row['icon'] }} me-1 text-slate-400"></i>{{ $row['label'] }}</span>
                                        <span class="text-slate-400">
                                            <strong class="text-slate-700">{{ $row['total'] }}</strong>
                                            <span class="mx-1">·</span>
                                            <span class="text-emerald-600">{{ $row['converted'] }} {{ __('enrolled') }}</span>
                                        </span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-indigo-500" style="width: {{ $row['rate'] }}%"></div>
                                    </div>
                                    <div class="mt-0.5 text-right text-[10px] font-semibold text-slate-400">{{ number_format($row['rate'], 1) }}% {{ __('conversion') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Reminders today + trend --}}
            <div class="mb-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-bell me-1 text-amber-500"></i>{{ __("Today's Follow-ups") }}</h3>
                        <a href="{{ route('crm.reminders') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('All reminders') }} →</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ __('Lead') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Time') }}</th>
                                    <th class="text-center pe-4">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayFollowUps as $followUp)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('crm.leads.show', $followUp->lead) }}" class="fw-semibold text-decoration-none text-dark">
                                                {{ $followUp->lead?->name ?? __('Deleted lead') }}
                                            </a>
                                            <div class="text-muted" style="font-size: 11px;">{{ $followUp->lead?->phone }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><i class="bi {{ $followUp->typeIcon() }} me-1"></i>{{ $followUp->typeLabel() }}</span></td>
                                        <td class="fw-semibold {{ $followUp->scheduled_at?->isPast() ? 'text-danger' : 'text-slate-700' }}">
                                            {{ optional($followUp->scheduled_at)->format('h:i A') }}
                                        </td>
                                        <td class="text-center pe-4">
                                            <form action="{{ route('crm.follow-ups.complete', $followUp) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" title="{{ __('Mark done') }}">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-muted">
                                            <i class="bi bi-check2-circle d-block mb-1 fs-4 text-success"></i>
                                            {{ __('Nothing due today. The pipeline is under control.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-slate-800"><i class="bi bi-bar-chart-line me-1 text-emerald-500"></i>{{ __('6-Month Trend') }}</h3>
                    @php
                        $trendMax = max(1, collect($trend)->max(fn ($row) => max($row['leads'], $row['converted'])));
                    @endphp
                    <div class="space-y-4">
                        @foreach($trend as $row)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-600">{{ $row['label'] }}</span>
                                    <span class="text-slate-400"><strong class="text-slate-700">{{ $row['leads'] }}</strong> {{ __('leads') }} · <strong class="text-emerald-600">{{ $row['converted'] }}</strong> {{ __('enrolled') }}</span>
                                </div>
                                <div class="space-y-1">
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-sky-400" style="width: {{ min(100, round($row['leads'] / $trendMax * 100)) }}%"></div>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, round($row['converted'] / $trendMax * 100)) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-3 text-[11px] font-semibold text-slate-500">
                        <span><i class="bi bi-circle-fill me-1 text-sky-400"></i>{{ __('New leads') }}</span>
                        <span><i class="bi bi-circle-fill me-1 text-emerald-500"></i>{{ __('Enrolled') }}</span>
                    </div>
                </div>
            </div>

            {{-- Recent leads --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-clock-history me-1 text-slate-400"></i>{{ __('Recent Leads') }}</h3>
                    <a href="{{ route('crm.leads') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">{{ __('Open lead list') }} →</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Name') }}</th>
                                <th>{{ __('Contact') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Interest') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Assigned') }}</th>
                                <th class="text-center pe-4">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeads as $lead)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('crm.leads.show', $lead) }}" class="fw-semibold text-decoration-none text-dark">{{ $lead->name }}</a>
                                        <div class="text-muted" style="font-size: 11px;">{{ optional($lead->created_at)->diffForHumans() }}</div>
                                    </td>
                                    <td>{{ $lead->phone }}</td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi {{ $lead->sourceIcon() }} me-1"></i>{{ $lead->sourceLabel() }}</span></td>
                                    <td class="text-muted">{{ $lead->interest ?: ($lead->course?->name ?: '—') }}</td>
                                    <td><span class="badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span></td>
                                    <td class="text-muted">{{ $lead->assignedTo?->name ?? '—' }}</td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="{{ __('Open') }}">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-muted">
                                        <i class="bi bi-inboxes d-block mb-2 fs-2"></i>
                                        {{ __('No leads yet. Add your first Facebook, website or walk-in lead.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
