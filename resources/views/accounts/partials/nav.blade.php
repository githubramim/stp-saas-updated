@php
    $accountTabs = [
        ['route' => 'accounts.dashboard', 'label' => 'Accounts Overview', 'icon' => 'bi-speedometer2'],
        ['route' => 'accounts.expenses', 'label' => 'Daily Expenses', 'icon' => 'bi-receipt'],
        ['route' => 'accounts.salaries', 'label' => 'Salary Sheet', 'icon' => 'bi-cash-stack'],
        ['route' => 'accounts.profit-loss', 'label' => 'Profit & Loss', 'icon' => 'bi-graph-up-arrow'],
    ];
@endphp

<div class="no-print mb-6 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
    <div class="flex flex-wrap gap-1.5">
        @foreach($accountTabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold transition
                      {{ request()->routeIs($tab['route']) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' }}">
                <i class="bi {{ $tab['icon'] }}"></i>
                <span>{{ __($tab['label']) }}</span>
            </a>
        @endforeach
    </div>
</div>
