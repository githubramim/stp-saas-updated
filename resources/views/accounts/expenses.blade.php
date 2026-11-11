<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Daily Expenses') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Record day-to-day institute expenses. They feed straight into the P&L report.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('accounts.profit-loss') }}" class="btn btn-outline-success rounded-3">
                    <i class="bi bi-graph-up-arrow me-1"></i> {{ __('Profit & Loss') }}
                </a>
                <button type="button" class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> {{ __('New Daily Expense') }}
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

            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __("Today's Expenses") }}</div>
                    <div class="mt-2 text-2xl font-black text-slate-800">৳{{ number_format($todayTotal, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ now()->format('d M Y') }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('This Month') }}</div>
                    <div class="mt-2 text-2xl font-black text-rose-600">৳{{ number_format($monthTotal, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ now()->format('F Y') }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Filtered Total') }}</div>
                    <div class="mt-2 text-2xl font-black text-indigo-600">৳{{ number_format($filteredTotal, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('According to current filters') }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Entries') }}</div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($filteredCount) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Expense records matching filters') }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-4">
                    <form method="GET" action="{{ route('accounts.expenses') }}" class="flex flex-wrap items-end gap-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('From') }}</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('To') }}</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm rounded-3">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Category') }}</label>
                            <select name="category" class="form-select form-select-sm rounded-3" style="min-width: 170px;">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-[180px] flex-1">
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Search') }}</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-3" placeholder="{{ __('Description or category…') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary rounded-3">
                            <i class="bi bi-funnel me-1"></i> {{ __('Apply') }}
                        </button>
                        <a href="{{ route('accounts.expenses') }}" class="btn btn-sm btn-outline-secondary rounded-3">{{ __('Reset') }}</a>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small datatable"
                           data-dt-paging="false" data-dt-searching="false" data-dt-info="false">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Date') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Payment') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th class="text-center pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ optional($expense->transaction_date)->format('d M Y') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $expense->category }}</span></td>
                                    <td class="text-muted">{{ \Illuminate\Support\Str::limit($expense->description ?: '—', 55) }}</td>
                                    <td class="text-muted">{{ $expense->payment_method ?: '—' }}</td>
                                    <td class="text-end fw-bold text-rose-600">৳{{ number_format($expense->amount, 2) }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    data-bs-toggle="modal" data-bs-target="#editExpenseModal{{ $expense->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('accounts.expenses.destroy', $expense) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Delete this expense?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        {{ __('No expenses found. Record your first daily expense to get started.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Create expense modal --}}
    <div class="modal fade" id="createExpenseModal" tabindex="-1" aria-labelledby="createExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white" id="createExpenseModalLabel">
                        <i class="bi bi-plus-circle text-primary me-1"></i> {{ __('Record Daily Expense') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('accounts.expenses.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Category') }} *</label>
                                <select name="category" class="form-select rounded-3 js-expense-category" required>
                                    <option value="">— {{ __('Select Category') }} —</option>
                                    @foreach(config('accounts.expense_categories', []) as $category)
                                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                                    @endforeach
                                    <option value="Others" {{ old('category') === 'Others' ? 'selected' : '' }}>{{ __('Others (custom)') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 custom-category-wrap {{ in_array(old('category'), ['Others', 'Other Expense'], true) ? '' : 'd-none' }}">
                                <label class="form-label fw-bold text-primary small">{{ __('Custom Category Name') }} *</label>
                                <input type="text" name="custom_category" value="{{ old('custom_category') }}"
                                       class="form-control rounded-3 custom-category-input bg-light"
                                       placeholder="{{ __('e.g. Lab Rent, Event Sponsor…') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Amount (৳)') }} *</label>
                                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="form-control rounded-3" required placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Expense Date') }} *</label>
                                <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Payment Method') }}</label>
                                <select name="payment_method" class="form-select rounded-3">
                                    <option value="">{{ __('— Select —') }}</option>
                                    @foreach(['Cash', 'bKash', 'Nagad', 'Bank'] as $method)
                                        <option value="{{ $method }}" {{ old('payment_method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Description') }}</label>
                                <input type="text" name="description" value="{{ old('description') }}" class="form-control rounded-3" placeholder="{{ __('Optional note') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Expense') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Per-row edit modals --}}
    @foreach($expenses as $expense)
        <div class="modal fade" id="editExpenseModal{{ $expense->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-dark text-white rounded-top-4">
                        <h5 class="modal-header-title fw-bold mb-0 text-white">
                            <i class="bi bi-pencil-square text-primary me-1"></i> {{ __('Edit Expense') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('accounts.expenses.update', $expense) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_editing" value="1">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Category') }} *</label>
                                    <select name="category" class="form-select rounded-3 js-expense-category" required>
                                        @foreach($categories->merge(['Others'])->unique() as $category)
                                            <option value="{{ $category }}" {{ $expense->category === $category ? 'selected' : '' }}>{{ $category === 'Others' ? __('Others (custom)') : $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 custom-category-wrap d-none">
                                    <label class="form-label fw-bold text-primary small">{{ __('Custom Category Name') }} *</label>
                                    <input type="text" name="custom_category" class="form-control rounded-3 custom-category-input bg-light"
                                           placeholder="{{ __('Only when “Others” is selected') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Amount (৳)') }} *</label>
                                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ $expense->amount }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Expense Date') }} *</label>
                                    <input type="date" name="transaction_date" value="{{ optional($expense->transaction_date)->format('Y-m-d') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Payment Method') }}</label>
                                    <select name="payment_method" class="form-select rounded-3">
                                        <option value="">{{ __('— Select —') }}</option>
                                        @foreach(['Cash', 'bKash', 'Nagad', 'Bank'] as $method)
                                            <option value="{{ $method }}" {{ $expense->payment_method === $method ? 'selected' : '' }}>{{ $method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Description') }}</label>
                                    <input type="text" name="description" value="{{ $expense->description }}" class="form-control rounded-3">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                                <i class="bi bi-check-circle-fill me-1"></i> {{ __('Update Expense') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(request('create') || ($errors->any() && !old('_editing')))
                var createModal = document.getElementById('createExpenseModal');
                if (createModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(createModal).show();
            @endif

            document.querySelectorAll('.js-expense-category').forEach(function (select) {
                select.addEventListener('change', function () {
                    var form = select.closest('form');
                    if (!form) return;
                    var wrap = form.querySelector('.custom-category-wrap');
                    var input = form.querySelector('.custom-category-input');
                    var needsCustom = ['Others', 'Other Expense'].indexOf(select.value) !== -1;
                    if (wrap) wrap.classList.toggle('d-none', !needsCustom);
                    if (input) {
                        if (needsCustom) {
                            input.setAttribute('required', 'required');
                        } else {
                            input.removeAttribute('required');
                        }
                    }
                });
            });
        });
    </script>
</x-app-layout>
