<div class=" mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-3">
    @php
        $links = [
            ['hr.dashboard', __('Overview')],
            ['hr.employees', __('Employees')],
            ['hr.attendance', __('Attendance')],
            ['hr.leaves', __('Leaves')],
            ['hr.payroll', __('Payroll')],
            ['hr.performance', __('Performance')],
        ];
    @endphp
    @foreach($links as [$route, $label])
        <a href="{{ route($route) }}"
           class="rounded-lg px-3 py-2 text-xs font-bold transition {{ request()->routeIs($route) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
