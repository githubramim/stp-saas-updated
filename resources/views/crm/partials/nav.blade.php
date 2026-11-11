@php
    $crmReminderBadge = \App\Models\LeadFollowUp::query()
        ->where('institute_id', auth()->user()->institute_id)
        ->where('status', \App\Models\LeadFollowUp::STATUS_PENDING)
        ->where('scheduled_at', '<=', now()->endOfDay())
        ->count();

    $crmTabs = [
        ['route' => 'crm.dashboard', 'label' => 'CRM Dashboard', 'icon' => 'bi-speedometer2'],
        ['route' => 'crm.leads', 'label' => 'Leads', 'icon' => 'bi-funnel'],
        ['route' => 'crm.reminders', 'label' => 'Follow-up Reminders', 'icon' => 'bi-bell'],
    ];
@endphp

<div class="no-print mb-6 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
    <div class="flex flex-wrap gap-1.5">
        @foreach($crmTabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold transition
                      {{ request()->routeIs($tab['route']) || ($tab['route'] === 'crm.leads' && request()->routeIs('crm.leads.*')) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800' }}">
                <i class="bi {{ $tab['icon'] }}"></i>
                <span>{{ __($tab['label']) }}</span>
                @if($tab['route'] === 'crm.reminders' && $crmReminderBadge > 0)
                    <span class="rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $crmReminderBadge }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
