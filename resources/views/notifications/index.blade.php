<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Notifications & SMS Center') }}</h2>
                <p class="text-xs text-slate-400">
                    {{ __('Active Driver:') }}
                    <span class="font-mono font-semibold text-indigo-600">{{ strtoupper(config('sms.driver', 'log')) }}</span>
                </p>
            </div>
            <button type="button" class="btn-solid-indigo shrink-0" data-bs-toggle="modal" data-bs-target="#createNotificationModal">
                <i class="bi bi-send-plus-fill"></i>
                <span>{{ __('Send Notification') }}</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <button type="button" class="group flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-indigo-200 hover:shadow-md"
                        data-bs-toggle="modal" data-bs-target="#createNotificationModal">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <i class="bi bi-megaphone-fill text-lg"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-700">{{ __('Bulk SMS to Batch') }}</span>
                        <span class="mt-1 block text-xs text-slate-400">{{ __('Broadcast a message to every active student in a batch.') }}</span>
                    </span>
                </button>
                <button type="button" class="group flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-indigo-200 hover:shadow-md"
                        data-bs-toggle="modal" data-bs-target="#createNotificationModal">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="bi bi-phone-fill text-lg"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-700">{{ __('Single Direct SMS') }}</span>
                        <span class="mt-1 block text-xs text-slate-400">{{ __('Send a one-off SMS to an individual phone number.') }}</span>
                    </span>
                </button>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">{{ __('Notification Dispatch History') }}</h3>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $notifications->total() }}</span>
                </div>

                @if($notifications->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <i class="bi bi-bell-slash text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-600">{{ __('No notifications dispatched yet.') }}</p>
                        <button type="button" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700"
                                data-bs-toggle="modal" data-bs-target="#createNotificationModal">
                            {{ __('Send your first notification') }} &rarr;
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-sm datatable">
                            <thead>
                                <tr class="border-b bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="p-3 font-semibold">{{ __('Date & Time') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Recipient / Target') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Message') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Type') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Status') }}</th>
                                    <th class="p-3 font-semibold">{{ __('Gateway Response Log') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $n)
                                    <tr class="border-b border-slate-100 transition hover:bg-slate-50/70">
                                        <td class="whitespace-nowrap p-3 text-xs text-slate-500">{{ $n->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="p-3 font-medium text-slate-700">
                                            {{ $n->recipient_phone ?? $n->batch?->name ?? '—' }}
                                            @if($n->batch)
                                                <span class="block text-xs font-normal text-slate-400">{{ __('Batch:') }} {{ $n->batch->name }}</span>
                                            @endif
                                        </td>
                                        <td class="max-w-xs truncate p-3 text-slate-600" title="{{ $n->message_body }}">{{ $n->message_body }}</td>
                                        <td class="whitespace-nowrap p-3">
                                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $n->type === 'sms' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                {{ strtoupper($n->type) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap p-3">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                                {{ $n->status === 'sent' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ $n->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                                {{ $n->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($n->status) }}
                                            </span>
                                        </td>
                                        <td class="max-w-xs truncate p-3 font-mono text-xs text-slate-500" title="{{ $n->response }}">
                                            {{ Str::limit($n->response, 45, '...') ?: '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-4">{{ $notifications->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Send Notification Modal --}}
    <div class="modal fade" id="createNotificationModal" tabindex="-1" aria-labelledby="createNotificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-slate-900 text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white flex items-center gap-2" id="createNotificationModalLabel">
                        <i class="bi bi-send text-indigo-400"></i> {{ __('Send New Notification / SMS') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body p-4">
                    @if($errors->any())
                        <div class="mb-3 rounded-3 border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                            <strong>{{ __('Please fix the following errors:') }}</strong>
                            <ul class="mt-1 list-inside list-disc">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <ul class="nav nav-pills nav-fill mb-4 gap-2" id="notificationModalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-3 fw-semibold" id="notification-bulk-tab" data-bs-toggle="pill"
                                    data-bs-target="#notification-bulk-pane" type="button" role="tab" aria-controls="notification-bulk-pane" aria-selected="true">
                                <i class="bi bi-megaphone-fill me-1"></i> {{ __('Bulk SMS to Batch') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 fw-semibold" id="notification-single-tab" data-bs-toggle="pill"
                                    data-bs-target="#notification-single-pane" type="button" role="tab" aria-controls="notification-single-pane" aria-selected="false">
                                <i class="bi bi-phone-fill me-1"></i> {{ __('Single Direct SMS') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="notification-bulk-pane" role="tabpanel" aria-labelledby="notification-bulk-tab">
                            <form method="POST" action="{{ route('notifications.send-bulk') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="notification_bulk_batch" class="form-label fw-semibold text-slate-700 small">{{ __('Target Batch') }} *</label>
                                    <select id="notification_bulk_batch" name="batch_id" required class="form-select rounded-3">
                                        <option value="">— {{ __('Select Target Batch') }} —</option>
                                        @foreach($batches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->course?->name }})</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-slate-400">{{ __('SMS will be dispatched to all active/admitted students in this batch.') }}</p>
                                </div>
                                <div class="mb-3">
                                    <label for="notification_bulk_type" class="form-label fw-semibold text-slate-700 small">{{ __('Notification Type') }}</label>
                                    <select id="notification_bulk_type" name="type" class="form-select rounded-3">
                                        <option value="sms">{{ __('SMS via Gateway') }}</option>
                                        <option value="system_alert">{{ __('System Alert Only') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="notification_bulk_message" class="form-label fw-semibold text-slate-700 small mb-0">{{ __('Message Body') }} *</label>
                                        <span id="notificationBulkCount" class="text-xs text-slate-400">0 / 500 {{ __('characters') }}</span>
                                    </div>
                                    <textarea id="notification_bulk_message" name="message_body" rows="4" required maxlength="500"
                                              placeholder="{{ __('Type your announcement or alert message here...') }}"
                                              class="form-control rounded-3"
                                              oninput="updateNotificationCount('notification_bulk_message', 'notificationBulkCount')"></textarea>
                                </div>
                                <div class="d-flex justify-content-end border-t pt-3">
                                    <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                                        <i class="bi bi-send-fill me-1"></i> {{ __('Send Bulk Notification') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="notification-single-pane" role="tabpanel" aria-labelledby="notification-single-tab">
                            <form method="POST" action="{{ route('notifications.send-single') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="notification_single_phone" class="form-label fw-semibold text-slate-700 small">{{ __('Recipient Phone Number') }} *</label>
                                    <input id="notification_single_phone" type="text" name="recipient_phone" required
                                           placeholder="{{ __('e.g. 01700000000') }}" class="form-control rounded-3">
                                    <p class="mt-1 text-xs text-slate-400">{{ __('Supports standard 11-digit mobile numbers.') }}</p>
                                </div>
                                <div class="mb-3">
                                    <label for="notification_single_type" class="form-label fw-semibold text-slate-700 small">{{ __('Notification Type') }}</label>
                                    <select id="notification_single_type" name="type" class="form-select rounded-3">
                                        <option value="sms">{{ __('SMS via Gateway') }}</option>
                                        <option value="system_alert">{{ __('System Alert Only') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="notification_single_message" class="form-label fw-semibold text-slate-700 small mb-0">{{ __('Message Body') }} *</label>
                                        <span id="notificationSingleCount" class="text-xs text-slate-400">0 / 500 {{ __('characters') }}</span>
                                    </div>
                                    <textarea id="notification_single_message" name="message_body" rows="4" required maxlength="500"
                                              placeholder="{{ __('Type direct SMS content...') }}"
                                              class="form-control rounded-3"
                                              oninput="updateNotificationCount('notification_single_message', 'notificationSingleCount')"></textarea>
                                </div>
                                <div class="d-flex justify-content-end border-t pt-3">
                                    <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                                        <i class="bi bi-send-fill me-1"></i> {{ __('Send Direct SMS') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateNotificationCount(inputId, countId) {
                var input = document.getElementById(inputId);
                var target = document.getElementById(countId);
                if (!input || !target) return;
                var length = input.value.length;
                var smsCount = Math.ceil(length / 160) || 1;
                target.innerText = length + ' / 500 ( ' + smsCount + ' SMS )';
            }

            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() || request('create'))
                    var notificationModal = document.getElementById('createNotificationModal');
                    if (notificationModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(notificationModal).show();
                @endif
            });
        </script>
    @endpush
</x-app-layout>
