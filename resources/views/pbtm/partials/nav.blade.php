@php
    $pbtmTabs = [
        ['route' => 'pbtm.index', 'label' => 'Overview', 'icon' => 'bi-speedometer2'],
        ['route' => 'pbtm.trainees', 'label' => 'Trainee Pipeline', 'icon' => 'bi-people'],
        ['route' => 'pbtm.stipends', 'label' => 'Stipends & Allowances', 'icon' => 'bi-cash-stack'],
        ['route' => 'pbtm.assets', 'label' => 'Assets', 'icon' => 'bi-box-seam'],
        ['route' => 'pbtm.placements', 'label' => 'Job Placement', 'icon' => 'bi-briefcase'],
        ['route' => 'pbtm.reports', 'label' => 'Progress Reports', 'icon' => 'bi-file-earmark-bar-graph'],
    ];
    $pbtmQuery = array_filter([
        'project_id' => $project?->id,
        'year' => request('year'),
        'view' => request('view'),
    ]);
@endphp

<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-1.5">
            @foreach($pbtmTabs as $tab)
                <a href="{{ route($tab['route'], $pbtmQuery) }}"
                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold transition
                          {{ request()->routeIs($tab['route']) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' }}">
                    <i class="bi {{ $tab['icon'] }}"></i>
                    <span>{{ __($tab['label']) }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route(request()->route()->getName()) }}" class="flex items-center gap-2">
            @if(request('year'))
                <input type="hidden" name="year" value="{{ request('year') }}">
            @endif
            @if(request('view'))
                <input type="hidden" name="view" value="{{ request('view') }}">
            @endif
            <label for="pbtm_project_filter" class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Project') }}</label>
            <select id="pbtm_project_filter" name="project_id" class="form-select form-select-sm rounded-xl border-slate-200" onchange="this.form.submit()">
                <option value="">{{ __('All Projects') }}</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ $project?->id === $p->id ? 'selected' : '' }}>
                        {{ $p->name }}{{ $p->code ? ' (' . $p->code . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>
