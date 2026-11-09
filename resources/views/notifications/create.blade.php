<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">{{ __('Send New Notification / SMS') }}</h2>
            <a href="{{ route('notifications.index') }}" class="inline-flex shrink-0 items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                ← Back to Log History
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8" id="notificationTabs">
                            <button type="button" onclick="switchTab('bulk')" id="tab-bulk" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                                📢 Bulk SMS to Batch
                            </button>
                            <button type="button" onclick="switchTab('single')" id="tab-single" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                                📱 Single Direct SMS
                            </button>
                        </nav>
                    </div>

                    <!-- Bulk SMS Form -->
                    <div id="content-bulk" class="tab-content">
                        <form method="POST" action="{{ route('notifications.send-bulk') }}" class="space-y-6">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Batch <span class="text-red-500">*</span></label>
                                <select name="batch_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                    <option value="">— Select Target Batch —</option>
                                    @foreach($batches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }} (Course: {{ $b->course?->name }})</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">SMS will be dispatched to all active/admitted students in this batch.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notification Type</label>
                                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="sms">📱 SMS via Gateway</option>
                                    <option value="system_alert">🔔 System Alert Only</option>
                                </select>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Message Body <span class="text-red-500">*</span></label>
                                    <span id="bulkCharCount" class="text-xs text-gray-500">0 / 500 characters (1 SMS)</span>
                                </div>
                                <textarea name="message_body" id="bulkMessage" rows="5"
                                          placeholder="Type your announcement or alert message here..."
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                          required maxlength="500" oninput="updateCharCount('bulkMessage', 'bulkCharCount')"></textarea>
                            </div>

                            <div class="flex justify-end pt-4 border-t">
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 transition">
                                    🚀 Send Bulk Notification
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Single Direct SMS Form -->
                    <div id="content-single" class="tab-content hidden">
                        <form method="POST" action="{{ route('notifications.send-single') }}" class="space-y-6">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" name="recipient_phone" placeholder="e.g. 01700000000"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Supports standard 11-digit mobile numbers (e.g., 01712345678).</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notification Type</label>
                                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="sms">📱 SMS via Gateway</option>
                                    <option value="system_alert">🔔 System Alert Only</option>
                                </select>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Message Body <span class="text-red-500">*</span></label>
                                    <span id="singleCharCount" class="text-xs text-gray-500">0 / 500 characters (1 SMS)</span>
                                </div>
                                <textarea name="message_body" id="singleMessage" rows="5"
                                          placeholder="Type direct SMS content..."
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                          required maxlength="500" oninput="updateCharCount('singleMessage', 'singleCharCount')"></textarea>
                            </div>

                            <div class="flex justify-end pt-4 border-t">
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 transition">
                                    📲 Send Direct SMS
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('#notificationTabs button').forEach(el => {
                el.classList.remove('border-indigo-500', 'text-indigo-600');
                el.classList.add('border-transparent', 'text-gray-500');
            });

            document.getElementById('content-' + tab).classList.remove('hidden');
            const activeBtn = document.getElementById('tab-' + tab);
            activeBtn.classList.add('border-indigo-500', 'text-indigo-600');
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
        }

        function updateCharCount(inputId, countId) {
            const length = document.getElementById(inputId).value.length;
            const smsCount = Math.ceil(length / 160) || 1;
            document.getElementById(countId).innerText = `${length} / 500 characters (${smsCount} SMS)`;
        }
    </script>
</x-app-layout>
