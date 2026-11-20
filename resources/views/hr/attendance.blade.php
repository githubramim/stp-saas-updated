<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Employee Attendance') }}</h2>
            <p class="text-xs text-slate-400">{{ __('Mark daily attendance for the selected branch.') }}</p>
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

            <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="POST" action="{{ route('ui.set', 'hr.attendance') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-auto">
                        <label for="att_date" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Date') }}</label>
                        <input id="att_date" type="date" name="date" value="{{ $date }}" class="form-control form-control-sm rounded-3">
                    </div>
                    <div class="col-auto">
                        <label for="att_month" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Summary Month') }}</label>
                        <input id="att_month" type="month" name="month" value="{{ $month }}" class="form-control form-control-sm rounded-3">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-primary rounded-3" type="submit">{{ __('View') }}</button>
                    </div>
                </form>
            </div>

            <form method="POST" action="{{ route('hr.attendance.store') }}">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800">{{ \Illuminate\Support\Carbon::parse($date)->format('l, d M Y') }}</h3>
                        <button type="submit" class="btn btn-sm btn-success rounded-3"><i class="bi bi-check2 me-1"></i> {{ __('Save Attendance') }}</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ __('Employee') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Present') }}</th>
                                    <th>{{ __('Late') }}</th>
                                    <th>{{ __('Absent') }}</th>
                                    <th>{{ __('Leave') }}</th>
                                    <th class="pe-4">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $member)
                                    @php
                                        $row = $summaries->get($member->id, collect());
                                        $current = $marks->get($member->id, 'present');
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">{{ $member->name }}</div>
                                            <div class="text-muted small">{{ ucfirst($member->role) }}</div>
                                        </td>
                                        <td>
                                            <select name="statuses[{{ $member->id }}]" class="form-select form-select-sm rounded-3">
                                                @foreach(\App\Models\EmployeeAttendance::STATUSES as $value => $label)
                                                    <option value="{{ $value }}" {{ $current === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>{{ (int) ($row['present'] ?? 0) }}</td>
                                        <td>{{ (int) ($row['late'] ?? 0) }}</td>
                                        <td>{{ (int) ($row['absent'] ?? 0) }}</td>
                                        <td>{{ (int) ($row['leave'] ?? 0) }}</td>
                                        <td class="pe-4 fw-semibold">{{ (int) ($row->sum() ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="py-6 text-center text-muted">{{ __('No employees in this branch.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
