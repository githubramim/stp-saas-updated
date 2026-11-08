<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaction Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold">
                                <span class="px-2 py-1 text-sm rounded {{ $transaction->type === 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                                — {{ $transaction->category }}
                            </h3>
                            <p class="text-gray-500 text-sm mt-1">{{ $transaction->transaction_date }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                ৳{{ number_format($transaction->amount, 2) }}
                            </div>
                            @if($transaction->receipt_number)
                                <p class="text-xs text-gray-500 mt-1">Receipt: {{ $transaction->receipt_number }}</p>
                            @endif
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                        @if($transaction->student)
                            <div>
                                <dt class="text-xs text-gray-500">Student</dt>
                                <dd class="font-medium">{{ $transaction->student->name_en }}</dd>
                            </div>
                        @endif
                        @if($transaction->batch)
                            <div>
                                <dt class="text-xs text-gray-500">Batch</dt>
                                <dd class="font-medium">{{ $transaction->batch->name }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs text-gray-500">Payment Method</dt>
                            <dd class="font-medium">{{ $transaction->payment_method ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Created</dt>
                            <dd class="font-medium">{{ $transaction->created_at->format('d M Y, h:i A') }}</dd>
                        </div>
                    </dl>

                    @if($transaction->description)
                        <div class="border-t mt-4 pt-4">
                            <dt class="text-xs text-gray-500">Description</dt>
                            <dd class="mt-1">{{ $transaction->description }}</dd>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                        @if($transaction->type === 'income' && $transaction->receipt_number)
                            <a href="{{ route('transactions.receipt', $transaction) }}" target="_blank" class="btn btn-outline-indigo">Print Receipt</a>
                        @endif
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-gray">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
