<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Salary Sheet') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Generate monthly payroll, mark payments and post salary expenses to the ledger.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('accounts.salaries') }}" class="d-flex align-items-center gap-2">
                    <label for="salary_month" class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Month') }}</label>
                    <select id="salary_month" name="month" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        @foreach($months as $option)
                            <option value="{{ $option }}" {{ $option === $month ? 'selected' : '' }}>
                                {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $option)->format('F Y') }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <button type="button" class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#generateSalaryModal">
                    <i class="bi bi-magic me-1"></i> {{ __('Generate Salary Sheet') }}
                </button>
                <button type="button" class="btn btn-outline-success rounded-3" data-bs-toggle="modal" data-bs-target="#payAllModal"
                        {{ $summary['due_count'] ? '' : 'disabled' }}>
                    <i class="bi bi-check2-all me-1"></i> {{ __('Pay All Drafts') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('accounts.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                </div>
            @endif
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

            <div class="mb-4 flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                <div>
                    <div class="text-sm font-bold text-slate-800">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
                    <div class="text-xs text-slate-400">{{ __('Payroll month') }}</div>
                </div>
                <a href="{{ route('accounts.expenses', ['category' => config('accounts.salary_category', 'Salary')]) }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800">
                    {{ __('View salary expenses in ledger') }} →
                </a>
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Payroll') }}</div>
                    <div class="mt-2 text-2xl font-black text-slate-800">৳{{ number_format($summary['total'], 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $summary['count'] }} {{ __('sheet(s) for this month') }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Paid') }}</div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">৳{{ number_format($summary['paid'], 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $summary['paid_count'] }} {{ __('sheet(s) paid') }}</div>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Due') }}</div>
                    <div class="mt-2 text-2xl font-black text-amber-600">৳{{ number_format($summary['due'], 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $summary['due_count'] }} {{ __('sheet(s) awaiting payment') }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3">
                    <h3 class="text-sm font-bold text-slate-800">{{ __('Payroll Register') }}</h3>
                    <form method="GET" action="{{ route('accounts.salaries') }}" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <label for="salary_status" class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                        <select id="salary_status" name="status" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                            <option value="">{{ __('All') }}</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small datatable" data-dt-page-length="25">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Staff') }}</th>
                                <th class="text-end">{{ __('Basic') }}</th>
                                <th class="text-end">{{ __('Allowance') }}</th>
                                <th class="text-end">{{ __('Deduction') }}</th>
                                <th class="text-end">{{ __('Net Salary') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Paid On') }}</th>
                                <th class="text-center pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sheets as $sheet)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $sheet->user?->name ?? __('Unknown staff') }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $sheet->user?->designation ?: ucfirst($sheet->user?->role ?? 'Staff') }}
                                        </div>
                                    </td>
                                    <td class="text-end">৳{{ number_format($sheet->basic_salary, 2) }}</td>
                                    <td class="text-end text-success">+৳{{ number_format($sheet->allowance, 2) }}</td>
                                    <td class="text-end text-danger">−৳{{ number_format($sheet->deduction, 2) }}</td>
                                    <td class="text-end fw-bold text-slate-800">৳{{ number_format($sheet->net_salary, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $sheet->isPaid() ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ ucfirst($sheet->status) }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $sheet->paid_on ? $sheet->paid_on->format('d M Y') : '—' }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                            @if($sheet->isPaid())
                                                <form action="{{ route('accounts.salaries.unpay', $sheet) }}" method="POST"
                                                      onsubmit="return confirm('{{ __('Revert this payment? The linked salary expense will be deleted.') }}')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2" title="{{ __('Revert payment') }}">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 js-pay-salary"
                                                        title="{{ __('Mark paid') }}"
                                                        data-bs-toggle="modal" data-bs-target="#paySalaryModal"
                                                        data-action="{{ route('accounts.salaries.pay', $sheet) }}"
                                                        data-name="{{ $sheet->user?->name }}"
                                                        data-net="{{ number_format($sheet->net_salary, 2) }}">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                        title="{{ __('Edit') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editSalaryModal{{ $sheet->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('accounts.salaries.destroy', $sheet) }}" method="POST"
                                                      onsubmit="return confirm('{{ __('Delete this draft salary sheet?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-muted">
                                        <i class="bi bi-cash-stack fs-2 d-block mb-2"></i>
                                        {{ __('No salary sheets for this month yet.') }}
                                        <button type="button" class="ms-1 text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#generateSalaryModal">
                                            {{ __('Generate one now') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Generate salary sheet modal --}}
    <div class="modal fade" id="generateSalaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white">
                        <i class="bi bi-magic text-primary me-1"></i> {{ __('Generate Salary Sheet') }} — {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('accounts.salaries.generate') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div class="modal-body p-4">
                        <div class="mb-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-800">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ __('Basic salaries are pre-filled from each staff member’s saved salary (or the most recent sheet). Existing drafts are updated; paid sheets are never overwritten.') }}
                        </div>

                        @if($staff->isEmpty())
                            <p class="py-6 text-center text-sm text-muted">{{ __('No staff members found for this institute.') }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Staff') }}</th>
                                            <th class="text-end" style="width: 150px;">{{ __('Basic (৳)') }}</th>
                                            <th class="text-end" style="width: 130px;">{{ __('Allowance (৳)') }}</th>
                                            <th class="text-end" style="width: 130px;">{{ __('Deduction (৳)') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($staff as $staffMember)
                                            @php
                                                $sheet = $existing->get($staffMember->id);
                                                $basicValue = $sheet?->basic_salary
                                                    ?? $staffMember->monthly_salary
                                                    ?? ($lastBasics[$staffMember->id] ?? '');
                                                $allowanceValue = $sheet?->allowance ?? 0;
                                                $deductionValue = $sheet?->deduction ?? 0;
                                            @endphp
                                            <tr class="{{ $sheet?->isPaid() ? 'table-success' : '' }}">
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $staffMember->name }}</div>
                                                    <div class="text-muted" style="font-size: 11px;">
                                                        {{ $staffMember->designation ?: ucfirst($staffMember->role ?? 'Staff') }}
                                                        @if($sheet?->isPaid())
                                                            · <span class="text-success fw-bold">{{ __('Already paid — will be skipped') }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="basics[{{ $staffMember->id }}]"
                                                           value="{{ $basicValue }}" class="form-control form-control-sm rounded-3 text-end"
                                                           {{ $sheet?->isPaid() ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="allowances[{{ $staffMember->id }}]"
                                                           value="{{ $allowanceValue }}" class="form-control form-control-sm rounded-3 text-end"
                                                           {{ $sheet?->isPaid() ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="deductions[{{ $staffMember->id }}]"
                                                           value="{{ $deductionValue }}" class="form-control form-control-sm rounded-3 text-end"
                                                           {{ $sheet?->isPaid() ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Generate / Update Drafts') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Pay one salary modal (shared, populated by JS) --}}
    <div class="modal fade" id="paySalaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white">
                        <i class="bi bi-check2-circle text-success me-1"></i> {{ __('Pay Salary') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="paySalaryForm" action="#">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="mb-3 text-sm text-slate-600">
                            {{ __('Pay') }} <strong id="paySalaryName">—</strong>:
                            <strong class="text-emerald-600">৳<span id="paySalaryNet">0.00</span></strong>
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Paid On') }}</label>
                                <input type="date" name="paid_on" value="{{ now()->toDateString() }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Payment Method') }}</label>
                                <select name="payment_method" class="form-select rounded-3">
                                    <option value="Cash">Cash</option>
                                    <option value="bKash">bKash</option>
                                    <option value="Nagad">Nagad</option>
                                    <option value="Bank">Bank</option>
                                </select>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 text-xs text-slate-400">
                            {{ __('A salary expense transaction will be created automatically in the ledger.') }}
                        </p>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Confirm Payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Pay all drafts modal --}}
    <div class="modal fade" id="payAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white">
                        <i class="bi bi-check2-all text-success me-1"></i> {{ __('Pay All Draft Salary Sheets') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('accounts.salaries.pay-all') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div class="modal-body p-4">
                        <p class="text-sm text-slate-600">
                            {{ __('This will mark all draft salary sheets for') }}
                            <strong>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</strong>
                            {{ __('as paid and create one expense transaction per staff member.') }}
                        </p>
                        <div class="mt-3">
                            <label class="form-label fw-bold text-dark small">{{ __('Payment Method') }}</label>
                            <select name="payment_method" class="form-select rounded-3">
                                <option value="Cash">Cash</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Bank">Bank</option>
                            </select>
                        </div>
                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            {{ __('Total due:') }} <strong>৳{{ number_format($summary['due'], 2) }}</strong> ({{ $summary['due_count'] }} {{ __('sheet(s)') }})
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Pay All') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Per-row edit modals (drafts only) --}}
    @foreach($sheets as $sheet)
        @if(! $sheet->isPaid())
            <div class="modal fade" id="editSalaryModal{{ $sheet->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-dark text-white rounded-top-4">
                            <h5 class="modal-header-title fw-bold mb-0 text-white">
                                <i class="bi bi-pencil-square text-primary me-1"></i> {{ __('Edit Salary') }} — {{ $sheet->user?->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('accounts.salaries.update', $sheet) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark small">{{ __('Basic Salary (৳)') }} *</label>
                                        <input type="number" step="0.01" min="0" name="basic_salary" value="{{ $sheet->basic_salary }}" class="form-control rounded-3" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark small">{{ __('Allowance (৳)') }}</label>
                                        <input type="number" step="0.01" min="0" name="allowance" value="{{ $sheet->allowance }}" class="form-control rounded-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark small">{{ __('Deduction (৳)') }}</label>
                                        <input type="number" step="0.01" min="0" name="deduction" value="{{ $sheet->deduction }}" class="form-control rounded-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark small">{{ __('Net Salary') }}</label>
                                        <input type="text" value="৳{{ number_format($sheet->net_salary, 2) }}" class="form-control rounded-3 bg-light" disabled>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-dark small">{{ __('Notes') }}</label>
                                        <textarea name="notes" rows="2" class="form-control rounded-3">{{ $sheet->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                                    <i class="bi bi-check-circle-fill me-1"></i> {{ __('Update Sheet') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var payForm = document.getElementById('paySalaryForm');
            document.querySelectorAll('.js-pay-salary').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!payForm) return;
                    payForm.setAttribute('action', button.dataset.action || '#');
                    var nameEl = document.getElementById('paySalaryName');
                    var netEl = document.getElementById('paySalaryNet');
                    if (nameEl) nameEl.textContent = button.dataset.name || '—';
                    if (netEl) netEl.textContent = button.dataset.net || '0.00';
                });
            });
        });
    </script>
</x-app-layout>
