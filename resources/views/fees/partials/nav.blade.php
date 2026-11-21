<div class=" mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-3">
    @php
        $links = [
            ['fees.dashboard', __('Overview')],
            ['fees.plans', __('Fee Plans')],
            ['fees.students', __('Student Plans')],
            ['fees.installments', __('Installments')],
            ['fees.adjustments', __('Adjustments')],
            ['fees.reminders', __('Reminders')],
        ];
    @endphp
    @foreach($links as [$route, $label])
        <a href="{{ route($route) }}"
           class="rounded-lg px-3 py-2 text-xs font-bold transition {{ request()->routeIs($route) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
