<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Notifications & SMS Center') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Active Driver: <span class="font-mono text-indigo-600 font-semibold">{{ strtoupper(config('sms.driver', 'log')) }}</span>
                </p>
            </div>
            <a href="{{ route('notifications.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition gap-2">
                ➕ Send New Notification / SMS
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Quick Send Notification</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-5 bg-gray-50/50">
                            <h4 class="font-medium text-gray-800 mb-3 flex items-center gap-2">📢 Bulk SMS to Batch</h4>
                            <form method="POST" action="{{ route('notifications.send-bulk') }}">
                                @csrf
                                <div class="mb-3">
                                    <select name="batch_id" class="w-full rounded-md border-gray-300 text-sm" required>
                                        <option value="">— Select Batch —</option>
                                        @foreach($batches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->course?->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea name="message_body" rows="3" placeholder="Type your message..."
                                              class="w-full rounded-md border-gray-300 text-sm" required maxlength="500"></textarea>
                                </div>
                                <input type="hidden" name="type" value="sms">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">Send to All Batch Students</button>
                            </form>
                        </div>

                        <div class="border rounded-lg p-5 bg-gray-50/50">
                            <h4 class="font-medium text-gray-800 mb-3 flex items-center gap-2">📱 Single Direct SMS</h4>
                            <form method="POST" action="{{ route('notifications.send-single') }}">
                                @csrf
                                <div class="mb-3">
                                    <input type="text" name="recipient_phone" placeholder="01XXXXXXXXX"
                                           class="w-full rounded-md border-gray-300 text-sm" required>
                                </div>
                                <div class="mb-3">
                                    <textarea name="message_body" rows="3" placeholder="Type your message..."
                                              class="w-full rounded-md border-gray-300 text-sm" required maxlength="500"></textarea>
                                </div>
                                <input type="hidden" name="type" value="sms">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">Send Single Direct SMS</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Notification Dispatch History</h3>

                    @if($notifications->isEmpty())
                        <p class="text-gray-500 text-center py-8">No notifications dispatched yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm datatable">
                                <thead>
                                    <tr class="border-b bg-gray-50">
                                        <th class="text-left p-3 font-medium">Date & Time</th>
                                        <th class="text-left p-3 font-medium">Recipient / Target</th>
                                        <th class="text-left p-3 font-medium">Message</th>
                                        <th class="text-left p-3 font-medium">Type</th>
                                        <th class="text-left p-3 font-medium">Status</th>
                                        <th class="text-left p-3 font-medium">Gateway Response Log</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $n)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-3 text-xs text-gray-600 whitespace-nowrap">{{ $n->created_at->format('d M Y, h:i A') }}</td>
                                            <td class="p-3 font-medium">
                                                {{ $n->recipient_phone ?? $n->batch?->name ?? '—' }}
                                                @if($n->batch)
                                                    <span class="block text-xs text-gray-400 font-normal">Batch: {{ $n->batch->name }}</span>
                                                @endif
                                            </td>
                                            <td class="p-3 max-w-xs truncate" title="{{ $n->message_body }}">{{ $n->message_body }}</td>
                                            <td class="p-3 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $n->type === 'sms' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                    {{ strtoupper($n->type) }}
                                                </span>
                                            </td>
                                            <td class="p-3 whitespace-nowrap">
                                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                                    {{ $n->status === 'sent' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $n->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $n->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ ucfirst($n->status) }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-xs font-mono text-gray-500 max-w-xs truncate" title="{{ $n->response }}">
                                                {{ Str::limit($n->response, 45, '...') ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $notifications->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
