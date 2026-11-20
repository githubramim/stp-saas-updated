<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Employees') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Joining date, salary structure and leave balance for every staff member.') }}</p>
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

            <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="POST" action="{{ route('ui.set', 'hr.employees') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label for="hr_q" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Search') }}</label>
                        <input id="hr_q" name="q" value="{{ $search }}" placeholder="{{ __('Name, email, designation or phone') }}" class="form-control form-control-sm rounded-3">
                    </div>
                    <div class="col-md-3">
                        <label for="hr_role" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Role') }}</label>
                        <select id="hr_role" name="role" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All roles') }}</option>
                            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="trainer" {{ $role === 'trainer' ? 'selected' : '' }}>Trainer</option>
                            <option value="staff" {{ $role === 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="hr_status" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Status') }}</label>
                        <select id="hr_status" name="status" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All') }}</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-sm btn-primary rounded-3" type="submit">{{ __('Filter') }}</button>
                        <a href="{{ route('hr.employees') }}" class="btn btn-sm btn-outline-secondary rounded-3">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Employee') }}</th>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Joining Date') }}</th>
                                <th>{{ __('Salary Structure') }}</th>
                                <th>{{ __('Leave Balance') }}</th>
                                <th>{{ __('Present') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-center pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $row)
                                @php /** @var \App\Models\User $user */ $user = $row['user']; $profile = $row['profile']; @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->email }} · {{ ucfirst($user->role) }}</div>
                                    </td>
                                    <td>{{ $profile->employee_code ?: '—' }}</td>
                                    <td>{{ $profile->joining_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        <div class="fw-semibold">৳{{ number_format($profile->grossSalary(), 2) }}</div>
                                        <div class="text-muted small">{{ __('Basic') }} ৳{{ number_format($profile->basic_salary, 2) }} + {{ __('Allowances') }} ৳{{ number_format($profile->totalAllowance(), 2) }}</div>
                                    </td>
                                    <td>{{ $row['leave_balance'] }} / {{ $profile->annual_leave_entitlement }} {{ __('days') }}</td>
                                    <td>{{ $row['present_days'] }}</td>
                                    <td>
                                        <span class="badge {{ $profile->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($profile->status) }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('hr.employees.edit', $user) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('hr.employees.status', $user) }}" onsubmit="return confirm('{{ __('Change employment status?') }}')">
                                                @csrf @method('PUT')
                                                <button class="btn btn-outline-warning" title="{{ __('Toggle status') }}"><i class="bi bi-toggle-{{ $profile->status === 'active' ? 'on' : 'off' }}"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="py-6 text-center text-muted">{{ __('No employees found in this branch.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
