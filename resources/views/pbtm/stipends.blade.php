<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">
                    <i class="bi bi-cash-stack me-1 text-teal-500"></i>
                    {{ __('Stipend & Allowance Management') }}
                </h2>
                <p class="text-xs text-slate-400">{{ __('Track monthly stipends, allowances and bank payments for funded trainees.') }}</p>
            </div>
            <button type="button" class="btn btn-primary rounded-3 font-bold" data-bs-toggle="modal" data-bs-target="#createStipendModal">
                <i class="bi bi-plus-circle me-1"></i> {{ __('New Stipend Record') }}
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @include('pbtm.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif

            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Total Paid') }}</div>
                    <div class="mt-1 text-2xl font-black text-emerald-600">৳{{ number_format($totals['paid'], 2) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Pending Payment') }}</div>
                    <div class="mt-1 text-2xl font-black text-amber-600">৳{{ number_format($totals['pending'], 2) }}</div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Records') }}</div>
                    <div class="mt-1 text-2xl font-black text-slate-800">{{ number_format($totals['count']) }}</div>
                </div>
            </div>

            <div class="mb-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div>
                        <label for="stipend_period" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Period') }}</label>
                        <input id="stipend_period" type="month" name="period" value="{{ request('period') }}" class="form-control form-control-sm rounded-xl">
                    </div>
                    <div>
                        <label for="stipend_type" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Type') }}</label>
                        <select id="stipend_type" name="type" class="form-select form-select-sm rounded-xl">
                            <option value="">{{ __('All types') }}</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="stipend_status" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                        <select id="stipend_status" name="status" class="form-select form-select-sm rounded-xl">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm rounded-xl font-bold"><i class="bi bi-funnel me-1"></i> {{ __('Filter') }}</button>
                    <a href="{{ route('pbtm.stipends', array_filter(['project_id' => $project?->id])) }}" class="btn btn-light btn-sm rounded-xl border">{{ __('Reset') }}</a>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                @if($stipends->isEmpty())
                    <div class="p-10 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-50 text-teal-600">
                            <i class="bi bi-cash-coin text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('No stipend records') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Create the first stipend or allowance record to start reporting project payments.') }}</p>
                        <button type="button" class="btn btn-primary rounded-3 mt-4" data-bs-toggle="modal" data-bs-target="#createStipendModal">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('New Stipend Record') }}
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small" style="min-width: 980px;">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Trainee') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Period') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th>{{ __('Payment') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stipends as $stipend)
                                    <tr>
                                        <td>
                                            <div class="font-bold text-slate-800">{{ $stipend->student?->name_en ?? '—' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $stipend->student?->reference_no }}</div>
                                        </td>
                                        <td class="text-slate-600">{{ $stipend->project?->name ?? '—' }}</td>
                                        <td><span class="badge bg-slate-100 text-slate-600">{{ $types[$stipend->type] ?? ucfirst($stipend->type) }}</span></td>
                                        <td class="font-semibold text-slate-700">{{ $stipend->periodLabel() }}</td>
                                        <td class="text-end font-black text-slate-800">৳{{ number_format($stipend->amount, 2) }}</td>
                                        <td>
                                            <div class="text-slate-700">{{ $stipend->payment_method ? ucfirst($stipend->payment_method) : '—' }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $stipend->payment_date?->format('d M Y') ?? __('Not paid') }}
                                                @if($stipend->bank_name)
                                                    · {{ $stipend->bank_name }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $stipend->status === 'paid' ? 'bg-success' : ($stipend->status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $statuses[$stipend->status] ?? ucfirst($stipend->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                @if($stipend->status !== 'paid')
                                                    <form method="POST" action="{{ route('pbtm.stipends.pay', $stipend) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success py-0 px-2" onclick="return confirm('Mark this stipend as paid today?')">{{ __('Mark Paid') }}</button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('pbtm.stipends.destroy', $stipend) }}" onsubmit="return confirm('Delete this stipend record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="createStipendModal" tabindex="-1" aria-labelledby="createStipendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="createStipendModalLabel">
                        <i class="bi bi-cash-stack text-teal-400"></i> {{ __('New Stipend / Allowance') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('pbtm.stipends.store') }}">
                    @csrf
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="stipend_student" class="form-label fw-semibold text-slate-700 small">{{ __('Trainee') }} *</label>
                                <select id="stipend_student" name="student_id" class="form-select rounded-3" required>
                                    <option value="">— {{ __('Select trainee') }} —</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name_en }} · {{ $student->reference_no }} · {{ $student->project?->name ?? __('No project') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_type_input" class="form-label fw-semibold text-slate-700 small">{{ __('Type') }} *</label>
                                <select id="stipend_type_input" name="type" class="form-select rounded-3" required>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ old('type', 'stipend') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_period_input" class="form-label fw-semibold text-slate-700 small">{{ __('Period (Month)') }} *</label>
                                <input id="stipend_period_input" type="month" name="period" value="{{ old('period', now()->format('Y-m')) }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_amount" class="form-label fw-semibold text-slate-700 small">{{ __('Amount (৳)') }} *</label>
                                <input id="stipend_amount" type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="form-control rounded-3" required placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_status_input" class="form-label fw-semibold text-slate-700 small">{{ __('Status') }} *</label>
                                <select id="stipend_status_input" name="status" class="form-select rounded-3" required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', 'pending') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_payment_date" class="form-label fw-semibold text-slate-700 small">{{ __('Payment Date') }}</label>
                                <input id="stipend_payment_date" type="date" name="payment_date" value="{{ old('payment_date') }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_payment_method" class="form-label fw-semibold text-slate-700 small">{{ __('Payment Method') }}</label>
                                <select id="stipend_payment_method" name="payment_method" class="form-select rounded-3">
                                    <option value="">{{ __('Select method') }}</option>
                                    @foreach(['bank' => 'Bank Transfer', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'cash' => 'Cash'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_bank_name" class="form-label fw-semibold text-slate-700 small">{{ __('Bank Name') }}</label>
                                <input id="stipend_bank_name" type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control rounded-3" placeholder="{{ __('e.g. Islami Bank') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="stipend_account_no" class="form-label fw-semibold text-slate-700 small">{{ __('Account / Mobile No') }}</label>
                                <input id="stipend_account_no" type="text" name="account_no" value="{{ old('account_no') }}" class="form-control rounded-3" placeholder="{{ __('Account number') }}">
                            </div>
                            <div class="col-md-12">
                                <label for="stipend_transaction_ref" class="form-label fw-semibold text-slate-700 small">{{ __('Transaction Reference') }}</label>
                                <input id="stipend_transaction_ref" type="text" name="transaction_ref" value="{{ old('transaction_ref') }}" class="form-control rounded-3 font-monospace" placeholder="{{ __('Bank / bKash transaction ID') }}">
                            </div>
                            <div class="col-12">
                                <label for="stipend_remarks" class="form-label fw-semibold text-slate-700 small">{{ __('Remarks') }}</label>
                                <textarea id="stipend_remarks" name="remarks" rows="2" class="form-control rounded-3" placeholder="{{ __('Optional notes') }}">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Record') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() || request('create'))
                    var modal = document.getElementById('createStipendModal');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
