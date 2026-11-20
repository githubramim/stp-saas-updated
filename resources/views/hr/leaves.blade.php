<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Employee Leaves') }}</h2>
            <p class="text-xs text-slate-400">{{ __('Record leave requests, approve or reject them and track leave balance.') }}</p>
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

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">{{ __('New Leave Request') }}</h3>
                    @if($errors->any())
                        <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800">
                            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    @endif
                    <form method="POST" action="{{ route('hr.leaves.store') }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="leave_user" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Employee') }}</label>
                            <select id="leave_user" name="user_id" class="form-select form-select-sm rounded-3" required>
                                @foreach($staff as $row)
                                    <option value="{{ $row['user']->id }}">{{ $row['user']->name }} ({{ $row['profile']->leaveBalance((int) now()->year) }} {{ __('days left') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="leave_type" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Leave Type') }}</label>
                            <select id="leave_type" name="leave_type" class="form-select form-select-sm rounded-3" required>
                                @foreach(\App\Models\EmployeeLeave::TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="start_date" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('From') }}</label>
                                <input id="start_date" type="date" name="start_date" required value="{{ old('start_date') }}" class="form-control form-control-sm rounded-3">
                            </div>
                            <div class="col-6">
                                <label for="end_date" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('To') }}</label>
                                <input id="end_date" type="date" name="end_date" required value="{{ old('end_date') }}" class="form-control form-control-sm rounded-3">
                            </div>
                        </div>
                        <div>
                            <label for="reason" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Reason') }}</label>
                            <textarea id="reason" name="reason" rows="2" class="form-control form-control-sm rounded-3">{{ old('reason') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary rounded-3">{{ __('Submit Request') }}</button>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3">
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Leave History') }}</h3>
                        <form method="POST" action="{{ route('ui.set', 'hr.leaves') }}">
                            @csrf
                            <select name="status" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                            </select>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ __('Employee') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Period') }}</th>
                                    <th>{{ __('Days') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-center pe-4">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $leave->user?->name ?? '—' }}</td>
                                        <td>{{ \App\Models\EmployeeLeave::TYPES[$leave->leave_type] ?? $leave->leave_type }}</td>
                                        <td>{{ $leave->dateRangeLabel() }}</td>
                                        <td>{{ $leave->days }}</td>
                                        <td>
                                            <span class="badge bg-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($leave->status) }}</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            @if($leave->status === 'pending')
                                                <div class="btn-group btn-group-sm">
                                                    <form method="POST" action="{{ route('hr.leaves.status', $leave) }}">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="status" value="approved">
                                                        <button class="btn btn-outline-success" title="{{ __('Approve') }}"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                    <form method="POST" action="{{ route('hr.leaves.status', $leave) }}">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button class="btn btn-outline-danger" title="{{ __('Reject') }}"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-muted small">{{ $leave->approved_at?->format('d M Y') ?? '—' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-6 text-center text-muted">{{ __('No leave requests found.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
