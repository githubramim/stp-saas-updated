<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight flex items-center gap-2 min-w-0">
                    <i class="bi bi-bell text-primary"></i>
                    <span class="truncate">{{ __('Fee Reminders') }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">{{ __('Notify students about upcoming and overdue fee installments.') }}</p>
            </div>
            <a href="{{ route('fees.installments') }}" class="btn btn-outline-primary font-bold shadow-sm rounded-3">
                <i class="bi bi-calendar2-week me-1"></i> {{ __('Installments') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-light min-vh-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('fees.partials.nav')

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

            {{-- Reminder stats --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Due Installments') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="bi bi-hourglass-split"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-amber-600">{{ number_format($stats['due_count']) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Waiting to be reminded') }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('SMS Sent') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="bi bi-chat-dots"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-emerald-600">{{ number_format($stats['sms_sent']) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Delivered successfully') }}</div>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Failed') }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i class="bi bi-exclamation-octagon"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-rose-600">{{ number_format($stats['failed_count']) }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ __('Need a retry') }}</div>
                </div>
            </div>

            {{-- Send reminders --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="POST" action="{{ route('fees.reminders.send') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label for="reminder_days" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Days From Due Date') }}</label>
                        <input id="reminder_days" type="number" name="days" value="0" min="0" max="30" class="form-control form-control-sm rounded-3" required>
                        <div class="mt-1 text-[11px] text-slate-400">{{ __('0 = already due, up to 30 days ahead.') }}</div>
                    </div>
                    <div class="col-md-8">
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 font-bold">
                            <i class="bi bi-send me-1"></i> {{ __('Send Due Reminders') }}
                        </button>
                    </div>
                </form>
            </div>

            @php
                $channelOptions = [];
                foreach ($channels as $key => $label) {
                    $value = is_int($key) ? (string) $label : (string) $key;
                    $channelOptions[$value] = is_int($key) ? ucfirst(str_replace('_', ' ', (string) $label)) : $label;
                }
                $statusOptions = [];
                foreach ($statuses as $key => $label) {
                    $value = is_int($key) ? (string) $label : (string) $key;
                    $statusOptions[$value] = is_int($key) ? ucfirst(str_replace('_', ' ', (string) $label)) : $label;
                }
            @endphp

            {{-- Filters --}}
            <form method="POST" action="{{ route('ui.set', 'fees.reminders') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="reminder_channel" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Channel') }}</label>
                        <select id="reminder_channel" name="channel" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All channels') }}</option>
                            @foreach($channelOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('channel') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="reminder_status" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                        <select id="reminder_status" name="status" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 font-bold"><i class="bi bi-funnel me-1"></i>{{ __('Filter') }}</button>
                        <a href="{{ route('fees.reminders') }}" class="btn btn-sm btn-outline-secondary rounded-3">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-bell me-1 text-indigo-500"></i>{{ __('Reminder Log') }}</h4>
                        <span class="text-[11px] font-semibold text-slate-400">{{ __('Delivery status for every sent notification.') }}</span>
                    </div>

                    @if($reminders->isEmpty())
                        <div class="p-10 text-center">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <i class="bi bi-bell-slash text-2xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('No reminders found') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Send due reminders or adjust the filters to see activity here.') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small" style="min-width: 1100px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Student') }}</th>
                                        <th>{{ __('Installment') }}</th>
                                        <th>{{ __('Channel') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th>{{ __('Sent At') }}</th>
                                        <th>{{ __('Message') }}</th>
                                        <th class="pe-4">{{ __('Response') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reminders as $reminder)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold text-dark">{{ $reminder->student?->name_en ?? '—' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $reminder->student?->reference_no ?? '—' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">#{{ $reminder->installment?->sequence ?? '—' }}</span>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ ucfirst($reminder->channel) }}</span></td>
                                            <td>{{ $reminder->due_date?->format('d M Y') ?? '—' }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $reminder->status === 'sent' ? 'bg-success' : ($reminder->status === 'failed' ? 'bg-danger' : ($reminder->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary')) }}">{{ ucfirst($reminder->status) }}</span>
                                            </td>
                                            <td class="text-muted">{{ $reminder->sent_at?->format('d M Y h:i A') ?? '—' }}</td>
                                            <td class="text-muted" style="max-width: 240px;">
                                                {{ $reminder->message ? \Illuminate\Support\Str::limit($reminder->message, 60) : '—' }}
                                            </td>
                                            <td class="pe-4 text-muted" style="max-width: 180px;">
                                                {{ $reminder->response ? \Illuminate\Support\Str::limit($reminder->response, 50) : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($reminders, 'links'))
                            <div class="mt-3">
                                @include('partials.clean-pagination', ['paginator' => $reminders])
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
