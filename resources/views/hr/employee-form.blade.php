<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ $user->exists ? __('Edit Employee') : __('Add Employee') }}</h2>
            <p class="text-xs text-slate-400">{{ __('Staff record, joining date, salary structure and performance rating.') }}</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-4xl px-3 sm:px-6 lg:px-8">
            @include('hr.partials.nav')

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <div class="font-bold">{{ __('Please fix the following errors:') }}</div>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $user->exists ? route('hr.employees.update', $user) : route('hr.employees.store') }}">
                @csrf
                @if($user->exists) @method('PUT') @endif

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">{{ __('Basic Information') }}</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Full Name') }} *</label>
                            <input id="name" name="name" required value="{{ old('name', $user->name) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Email') }} *</label>
                            <input id="email" type="email" name="email" required value="{{ old('email', $user->email) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label for="phone" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Phone') }}</label>
                            <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label for="role" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Role') }} *</label>
                            <select id="role" name="role" class="form-select form-select-sm rounded-3" required>
                                @foreach(['admin' => 'Admin', 'trainer' => 'Trainer', 'staff' => 'Staff'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('role', $user->role ?: 'staff') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="branch_id" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Branch') }} *</label>
                            <select id="branch_id" name="branch_id" class="form-select form-select-sm rounded-3" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (int) old('branch_id', $user->branch_id ?: \App\Support\BranchContext::scopeId()) === $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="designation" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Designation') }}</label>
                            <input id="designation" name="designation" value="{{ old('designation', $user->designation) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Department') }}</label>
                            <input id="department" name="department" value="{{ old('department', $profile->department) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ $user->exists ? __('New Password (leave blank to keep)') : __('Password') }} *</label>
                            <input id="password" type="password" name="password" class="form-control form-control-sm rounded-3" {{ $user->exists ? '' : 'required' }}>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Confirm Password') }}</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-sm rounded-3" {{ $user->exists ? '' : 'required' }}>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">{{ __('Employment & Salary Structure') }}</h3>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="employee_code" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Employee Code') }}</label>
                            <input id="employee_code" name="employee_code" value="{{ old('employee_code', $profile->employee_code) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-3">
                            <label for="joining_date" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Joining Date') }} *</label>
                            <input id="joining_date" type="date" name="joining_date" required value="{{ old('joining_date', $profile->joining_date?->toDateString() ?? now()->toDateString()) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-3">
                            <label for="employment_type" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Employment Type') }} *</label>
                            <select id="employment_type" name="employment_type" class="form-select form-select-sm rounded-3" required>
                                @foreach(\App\Models\EmployeeProfile::EMPLOYMENT_TYPES as $type)
                                    <option value="{{ $type }}" {{ old('employment_type', $profile->employment_type ?: 'full_time') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Employment Status') }} *</label>
                            <select id="status" name="status" class="form-select form-select-sm rounded-3" required>
                                @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $profile->status ?: 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="basic_salary" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Basic Salary') }} *</label>
                            <input id="basic_salary" type="number" step="0.01" min="0" name="basic_salary" required value="{{ old('basic_salary', $profile->basic_salary) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-3">
                            <label for="house_allowance" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('House Allowance') }}</label>
                            <input id="house_allowance" type="number" step="0.01" min="0" name="house_allowance" value="{{ old('house_allowance', $profile->house_allowance) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-3">
                            <label for="transport_allowance" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Transport Allowance') }}</label>
                            <input id="transport_allowance" type="number" step="0.01" min="0" name="transport_allowance" value="{{ old('transport_allowance', $profile->transport_allowance) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-3">
                            <label for="other_allowance" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Other Allowance') }}</label>
                            <input id="other_allowance" type="number" step="0.01" min="0" name="other_allowance" value="{{ old('other_allowance', $profile->other_allowance) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label for="annual_leave_entitlement" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Annual Leave Entitlement') }} *</label>
                            <input id="annual_leave_entitlement" type="number" min="0" max="365" name="annual_leave_entitlement" required value="{{ old('annual_leave_entitlement', $profile->annual_leave_entitlement ?: 12) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label for="emergency_contact" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Emergency Contact') }}</label>
                            <input id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $profile->emergency_contact) }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label for="nid" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('NID') }}</label>
                            <input id="nid" name="nid" value="{{ old('nid', $profile->nid) }}" class="form-control form-control-sm rounded-3">
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">{{ __('Performance') }}</h3>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="performance_rating" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Admin Rating (1–5)') }}</label>
                            <select id="performance_rating" name="performance_rating" class="form-select form-select-sm rounded-3">
                                <option value="">—</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ (string) old('performance_rating', $profile->performance_rating ?? '') === (string) $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label for="performance_notes" class="form-label text-[11px] font-bold uppercase text-slate-400">{{ __('Performance Notes') }}</label>
                            <input id="performance_notes" name="performance_notes" value="{{ old('performance_notes', $profile->performance_notes) }}" class="form-control form-control-sm rounded-3">
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <a href="{{ route('hr.employees') }}" class="btn btn-outline-secondary rounded-3">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary rounded-3">{{ $user->exists ? __('Update Employee') : __('Save Employee') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
