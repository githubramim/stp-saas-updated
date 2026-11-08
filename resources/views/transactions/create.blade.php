<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type *</label>
                            <select name="type" id="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">— Select Type —</option>
                                <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Income (Fee Collection)</option>
                                <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select name="category" id="category_select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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
                            @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Dynamic Custom Category Manual Input --}}
                        <div id="custom_category_wrapper" class="mb-4 hidden">
                            <label class="block text-sm font-medium text-indigo-700 mb-1">Custom Category Name *</label>
                            <input type="text" name="custom_category" id="custom_category" value="{{ old('custom_category') }}"
                                   placeholder="Type manual category name (e.g. Lab Rent, Event Sponsor...)"
                                   class="w-full rounded-md border-indigo-300 bg-indigo-50/50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="text-xs text-indigo-500 mt-1">Specify custom category for this transaction.</p>
                            @error('custom_category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (৳) *</label>
                                <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('transaction_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch (Optional Filter)</label>
                            <select name="batch_id" id="batch_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— All Batches (Show All Students) —</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}" {{ old('batch_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Select a batch to filter the student list below.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Student (Optional)</label>
                            <select name="student_id" id="student_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Select Student —</option>
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}" data-batch-id="{{ $s->batch_id ?? '' }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->name_en }} | Ref: {{ $s->reference_no ?? 'N/A' }} | {{ $s->batch?->name ?? $s->course?->name ?? 'No Batch' }} ({{ $s->contact }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Select —</option>
                                <option value="Cash">Cash</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Bank">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea name="description" rows="2"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-gray">Cancel</a>
                            <button type="submit" class="btn btn-solid-indigo">Record Transaction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive JS for Batch-wise Student Filtering & Custom Category Input --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_select');
            const customCategoryWrapper = document.getElementById('custom_category_wrapper');
            const customCategoryInput = document.getElementById('custom_category');

            const batchSelect = document.getElementById('batch_id');
            const studentSelect = document.getElementById('student_id');
            const studentOptions = Array.from(studentSelect.options);

            // 1. Custom Category Toggle
            function toggleCustomCategory() {
                const val = categorySelect.value;
                if (val === 'Other Income' || val === 'Other Expense' || val === 'Others') {
                    customCategoryWrapper.classList.remove('hidden');
                    customCategoryInput.setAttribute('required', 'required');
                } else {
                    customCategoryWrapper.classList.add('hidden');
                    customCategoryInput.removeAttribute('required');
                }
            }

            categorySelect.addEventListener('change', toggleCustomCategory);
            toggleCustomCategory(); // Init on page load

            // 2. Batch-wise Student Filtering
            function filterStudentsByBatch() {
                const selectedBatchId = batchSelect.value;

                studentOptions.forEach(option => {
                    if (!option.value) {
                        option.hidden = false; // Always show default "— Select Student —"
                        return;
                    }

                    const studentBatchId = option.getAttribute('data-batch-id');

                    if (!selectedBatchId || studentBatchId === selectedBatchId) {
                        option.hidden = false;
                    } else {
                        option.hidden = true;
                    }
                });

                // If currently selected student doesn't match selected batch, reset student select
                const currentSelectedOpt = studentSelect.options[studentSelect.selectedIndex];
                if (currentSelectedOpt && currentSelectedOpt.hidden) {
                    studentSelect.value = "";
                }
            }

            batchSelect.addEventListener('change', filterStudentsByBatch);

            // 3. Auto-select Batch when Student is chosen
            studentSelect.addEventListener('change', function () {
                const selectedOpt = studentSelect.options[studentSelect.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    const studentBatchId = selectedOpt.getAttribute('data-batch-id');
                    if (studentBatchId && batchSelect.querySelector(`option[value="${studentBatchId}"]`)) {
                        batchSelect.value = studentBatchId;
                        filterStudentsByBatch();
                    }
                }
            });

            // Initial filter on load if batch is preselected
            if (batchSelect.value) {
                filterStudentsByBatch();
            }
        });
    </script>
</x-app-layout>
