<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Follow-up Reminders') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Every pending call, WhatsApp and meeting in one queue so no lead goes cold.') }}</p>
            </div>
            <a href="{{ route('crm.leads', ['create' => 1]) }}" class="btn btn-primary rounded-3">
                <i class="bi bi-plus-circle-fill me-1"></i> {{ __('New Lead') }}
            </a>
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
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                </div>
            @endif

            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('crm.reminders') }}" class="rounded-2xl border {{ $bucket === 'all' ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-slate-200' }} bg-white p-5 shadow-sm transition hover:border-indigo-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('All Pending') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><i class="bi bi-bell"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ $counts['all'] }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Across the whole pipeline') }}</div>
                </a>

                <a href="{{ route('crm.reminders', ['bucket' => 'overdue']) }}" class="rounded-2xl border {{ $bucket === 'overdue' ? 'border-rose-300 ring-2 ring-rose-100' : 'border-rose-100' }} bg-white p-5 shadow-sm transition hover:border-rose-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Overdue') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i class="bi bi-exclamation-triangle"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-rose-600">{{ $counts['overdue'] }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Past their scheduled date') }}</div>
                </a>

                <a href="{{ route('crm.reminders', ['bucket' => 'today']) }}" class="rounded-2xl border {{ $bucket === 'today' ? 'border-amber-300 ring-2 ring-amber-100' : 'border-amber-100' }} bg-white p-5 shadow-sm transition hover:border-amber-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Today') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="bi bi-sun"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-amber-600">{{ $counts['today'] }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ now()->format('d M Y') }}</div>
                </a>

                <a href="{{ route('crm.reminders', ['bucket' => 'upcoming']) }}" class="rounded-2xl border {{ $bucket === 'upcoming' ? 'border-sky-300 ring-2 ring-sky-100' : 'border-sky-100' }} bg-white p-5 shadow-sm transition hover:border-sky-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Upcoming') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="bi bi-calendar-week"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-sky-600">{{ $counts['upcoming'] }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Scheduled after today') }}</div>
                </a>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 p-4">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['all' => __('All'), 'overdue' => __('Overdue'), 'today' => __('Today'), 'upcoming' => __('Upcoming')] as $value => $label)
                            <a href="{{ route('crm.reminders', array_merge(request()->except(['bucket', 'page']), ['bucket' => $value])) }}"
                               class="rounded-xl px-3 py-2 text-xs font-semibold transition {{ $bucket === $value ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                    <form method="GET" action="{{ route('crm.reminders') }}" class="flex items-center gap-2">
                        <input type="hidden" name="bucket" value="{{ $bucket }}">
                        <label class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Assigned') }}</label>
                        <select name="assigned" class="form-select form-select-sm rounded-3" style="min-width: 160px;" onchange="this.form.submit()">
                            <option value="">{{ __('Anyone') }}</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ (string) request('assigned') === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small datatable" data-dt-page-length="25">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Lead') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Scheduled') }}</th>
                                <th>{{ __('State') }}</th>
                                <th>{{ __('Notes') }}</th>
                                <th>{{ __('Assigned') }}</th>
                                <th class="text-center pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($followUps as $followUp)
                                @php
                                    $state = $followUp->state();
                                    $stateClass = $state === 'overdue' ? 'bg-rose-100 text-rose-700' : ($state === 'today' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        @if($followUp->lead)
                                            <a href="{{ route('crm.leads.show', $followUp->lead) }}" class="fw-semibold text-decoration-none text-dark">{{ $followUp->lead->name }}</a>
                                            <div class="text-muted" style="font-size: 11px;">
                                                {{ $followUp->lead->phone }} · {{ $followUp->lead->sourceLabel() }}
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('Deleted lead') }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi {{ $followUp->typeIcon() }} me-1"></i>{{ $followUp->typeLabel() }}</span></td>
                                    <td>
                                        <div class="fw-semibold {{ $state === 'overdue' ? 'text-danger' : ($state === 'today' ? 'text-warning' : 'text-slate-700') }}">
                                            {{ $followUp->scheduled_at?->format('d M Y') }}
                                        </div>
                                        <div style="font-size: 11px;" class="text-muted">{{ $followUp->scheduled_at?->format('h:i A') }}</div>
                                    </td>
                                    <td><span class="badge {{ $stateClass }}">{{ ucfirst($state) }}</span></td>
                                    <td class="text-muted">{{ \Illuminate\Support\Str::limit($followUp->notes ?: '—', 60) }}</td>
                                    <td class="text-muted">{{ $followUp->user?->name ?? '—' }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-inline-flex gap-1">
                                            @if($followUp->lead)
                                                <a href="{{ route('crm.leads.show', $followUp->lead) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="{{ __('Open lead') }}">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('crm.follow-ups.complete', $followUp) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" title="{{ __('Mark as done') }}">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-muted">
                                        <i class="bi bi-check2-circle d-block mb-2 fs-2 text-success"></i>
                                        {{ __('No pending follow-ups in this bucket.') }}
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
