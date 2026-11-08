<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <i class="bi bi-wallet2 text-primary"></i>
                {{ __('Finance & Transactions') }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('export.transactions') }}" class="btn btn-outline-success font-bold shadow-sm rounded-3">
                    <i class="bi bi-file-earmark-excel me-1"></i> 📥 Export CSV/Excel
                </a>
                <button type="button" class="btn btn-primary font-bold shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createTransactionModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> + New Transaction
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-light min-vh-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Summary Cards --}}
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-success">
                        <div class="text-muted small uppercase fw-bold">Total Income</div>
                        <div class="fs-3 fw-black text-success mt-1">৳{{ number_format($totalIncome, 2) }}</div>
                    </div>
                </div>
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-danger">
                        <div class="text-muted small uppercase fw-bold">Total Expense</div>
                        <div class="fs-3 fw-black text-danger mt-1">৳{{ number_format($totalExpense, 2) }}</div>
                    </div>
                </div>
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-4 border-primary">
                        <div class="text-muted small uppercase fw-bold">Net Balance</div>
                        <div class="fs-3 fw-black {{ ($totalIncome - $totalExpense) >= 0 ? 'text-primary' : 'text-danger' }} mt-1">
                            ৳{{ number_format($totalIncome - $totalExpense, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transactions List & Filter --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="fw-bold text-dark mb-0">Transaction Records</h4>
                        <form method="GET" class="d-flex gap-2 flex-wrap">
                            <select name="type" class="form-select form-select-sm rounded-3">
                                <option value="">All Types</option>
                                <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm rounded-3" placeholder="From">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm rounded-3" placeholder="To">
                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-3">Filter</button>
                        </form>
                    </div>

                    @if($transactions->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No transactions recorded yet. Click <strong>+ New Transaction</strong> to add one.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small datatable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Student</th>
                                        <th class="text-end">Amount</th>
                                        <th>Receipt</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $t)
                                        <tr>
                                            <td class="fw-bold">{{ $t->transaction_date }}</td>
                                            <td>
                                                <span class="badge {{ $t->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($t->type) }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold text-dark">{{ $t->category }}</td>
                                            <td class="text-muted">{{ $t->student?->name_en ?? '—' }}</td>
                                            <td class="text-end fw-black {{ $t->type === 'income' ? 'text-success' : 'text-danger' }}">
                                                ৳{{ number_format($t->amount, 2) }}
                                            </td>
                                            <td>
                                                @if($t->receipt_number)
                                                    <a href="{{ route('transactions.receipt', $t) }}" class="badge bg-primary text-decoration-none" target="_blank">
                                                        {{ $t->receipt_number }} ↗
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('transactions.show', $t) }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                                                    <form action="{{ route('transactions.destroy', $t) }}" method="POST" class="d-inline" onsubmit="confirmDelete(event)">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($transactions, 'links'))
                            <div class="mt-3">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Create Transaction Modal --}}
    <div class="modal fade" id="createTransactionModal" tabindex="-1" aria-labelledby="createTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white flex items-center gap-2" id="createTransactionModalLabel">
                        <i class="bi bi-plus-circle text-primary"></i> Record New Transaction
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('transactions.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            {{-- Type --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Transaction Type *</label>
                                <select name="type" id="type" class="form-select rounded-3" required>
                                    <option value="">— Select Type —</option>
                                    <option value="income">Income (Fee Collection)</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Category *</label>
                                <select name="category" id="modal_category_select" class="form-select rounded-3" required>
                                    <option value="">— Select Category —</option>
                                    <optgroup label="Income">
                                        <option value="Admission Fee">Admission Fee</option>
                                        <option value="Exam Fee">Exam Fee</option>
                                        <option value="Monthly Fee">Monthly Fee</option>
                                        <option value="Other Income">Other Income (Specify)</option>
                                    </optgroup>
                                    <optgroup label="Expense">
                                        <option value="Trainer Salary">Trainer Salary</option>
                                        <option value="Utility Bill">Utility Bill</option>
                                        <option value="Equipment">Equipment</option>
                                        <option value="Stationery">Stationery</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Other Expense">Other Expense (Specify)</option>
                                    </optgroup>
                                    <option value="Others">Others (Custom Category)</option>
                                </select>
                            </div>

                            {{-- Custom Category Input --}}
                            <div class="col-12 d-none" id="modal_custom_category_wrapper">
                                <label class="form-label fw-bold text-primary small">Custom Category Name *</label>
                                <input type="text" name="custom_category" id="modal_custom_category"
                                       placeholder="Type manual category name (e.g. Lab Rent, Event Sponsor...)"
                                       class="form-control rounded-3 bg-light">
                            </div>

                            {{-- Amount --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Amount (৳) *</label>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control rounded-3" required placeholder="0.00">
                            </div>

                            {{-- Date --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Transaction Date *</label>
                                <input type="date" name="transaction_date" value="{{ now()->toDateString() }}" class="form-control rounded-3" required>
                            </div>

                            {{-- Batch Filter --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Batch (Filter Students)</label>
                                <select name="batch_id" id="modal_batch_id" class="form-select rounded-3">
                                    <option value="">— All Batches —</option>
                                    @foreach($batches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Student --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Student (Optional)</label>
                                <select name="student_id" id="modal_student_id" class="form-select rounded-3">
                                    <option value="">— Select Student —</option>
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}" data-batch-id="{{ $s->batch_id ?? '' }}">
                                            {{ $s->name_en }} | Ref: {{ $s->reference_no ?? 'N/A' }} | {{ $s->batch?->name ?? $s->course?->name ?? 'No Batch' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Payment Method --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark small">Payment Method</label>
                                <select name="payment_method" class="form-select rounded-3">
                                    <option value="">— Select Payment Method —</option>
                                    <option value="Cash">Cash</option>
                                    <option value="bKash">bKash</option>
                                    <option value="Nagad">Nagad</option>
                                    <option value="Bank">Bank Transfer</option>
                                </select>
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Description (Optional)</label>
                                <textarea name="description" rows="2" class="form-control rounded-3" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Save Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Interactive Modal JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const catSelect = document.getElementById('modal_category_select');
            const customWrap = document.getElementById('modal_custom_category_wrapper');
            const customInput = document.getElementById('modal_custom_category');

            const batchSel = document.getElementById('modal_batch_id');
            const studentSel = document.getElementById('modal_student_id');
            const studentOpts = Array.from(studentSel.options);

            // Custom Category toggle
            catSelect.addEventListener('change', function () {
                const val = catSelect.value;
                if (val === 'Other Income' || val === 'Other Expense' || val === 'Others') {
                    customWrap.classList.remove('d-none');
                    customInput.setAttribute('required', 'required');
                } else {
                    customWrap.classList.add('d-none');
                    customInput.removeAttribute('required');
                }
            });

            // Batch-wise Student Filtering
            batchSel.addEventListener('change', function () {
                const selectedBatchId = batchSel.value;
                studentOpts.forEach(opt => {
                    if (!opt.value) {
                        opt.hidden = false;
                        return;
                    }
                    const sBatchId = opt.getAttribute('data-batch-id');
                    if (!selectedBatchId || sBatchId === selectedBatchId) {
                        opt.hidden = false;
                    } else {
                        opt.hidden = true;
                    }
                });
                const currentOpt = studentSel.options[studentSel.selectedIndex];
                if (currentOpt && currentOpt.hidden) {
                    studentSel.value = "";
                }
            });

            // Auto-select batch when student is picked
            studentSel.addEventListener('change', function () {
                const selectedOpt = studentSel.options[studentSel.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    const sBatchId = selectedOpt.getAttribute('data-batch-id');
                    if (sBatchId && batchSel.querySelector(`option[value="${sBatchId}"]`)) {
                        batchSel.value = sBatchId;
                        batchSel.dispatchEvent(new Event('change'));
                    }
                }
            });
        });
    </script>
</x-app-layout>
