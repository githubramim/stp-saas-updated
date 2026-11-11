<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="mb-1 flex flex-wrap items-center gap-2">
                    <a href="{{ route('crm.leads') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-600">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('Leads') }}
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="text-xs text-slate-400">{{ __('Lead #' . $lead->id) }}</span>
                </div>
                <h2 class="truncate text-lg font-semibold leading-tight text-slate-800">{{ $lead->name }}</h2>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <span class="badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span>
                    <span class="badge bg-light text-dark border"><i class="bi {{ $lead->sourceIcon() }} me-1"></i>{{ $lead->sourceLabel() }}</span>
                    <span>{{ __('Created') }} {{ optional($lead->created_at)->format('d M Y, h:i A') }}</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="https://wa.me/{{ app(\App\Services\WhatsAppService::class)->normalizePhone($lead->phone) }}" target="_blank" rel="noopener" class="btn btn-outline-success rounded-3">
                    <i class="bi bi-whatsapp me-1"></i> {{ __('WhatsApp') }}
                </a>
                <a href="tel:{{ $lead->phone }}" class="btn btn-outline-primary rounded-3">
                    <i class="bi bi-telephone me-1"></i> {{ __('Call') }}
                </a>
                <button type="button" class="btn btn-outline-dark rounded-3" data-bs-toggle="modal" data-bs-target="#addFollowUpModal">
                    <i class="bi bi-calendar-plus me-1"></i> {{ __('Follow-up') }}
                </button>
                @if($lead->isConverted() && $lead->convertedStudent)
                    <a href="{{ route('admissions.show', $lead->convertedStudent) }}" class="btn btn-success rounded-3">
                        <i class="bi bi-mortarboard me-1"></i> {{ __('View Student') }}
                    </a>
                @else
                    <button type="button" class="btn btn-success rounded-3" data-bs-toggle="modal" data-bs-target="#convertLeadModal">
                        <i class="bi bi-mortarboard me-1"></i> {{ __('Convert to Student') }}
                    </button>
                @endif
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-toggle="modal" data-bs-target="#editLeadModal">
                    <i class="bi bi-pencil me-1"></i> {{ __('Edit') }}
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $isConvertError = old('_modal') === 'convert-lead';
        $isEditError = old('_modal') === 'edit-lead';
        $isFollowError = old('_modal') === 'add-follow-up';
        $convertValue = fn ($key, $default = null) => $isConvertError ? old($key, $default) : $default;
        $editValue = fn ($key, $default = null) => $isEditError ? old($key, $default) : $default;

        $pendingFollowUps = $lead->followUps->where('status', \App\Models\LeadFollowUp::STATUS_PENDING);
        $closedFollowUps = $lead->followUps->where('status', '!=', \App\Models\LeadFollowUp::STATUS_PENDING)->sortByDesc(fn ($f) => $f->completed_at ?? $f->scheduled_at);
        $leadOverdue = $pendingFollowUps->filter(fn ($f) => $f->scheduled_at?->isPast())->count();
    @endphp

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            @include('crm.partials.nav')

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
            @if(session('whatsapp_link'))
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <span><i class="bi bi-whatsapp me-1"></i> {{ __('Your WhatsApp message is ready. Open it to send.') }}</span>
                    <a href="{{ session('whatsapp_link') }}" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-3">
                        {{ __('Open WhatsApp') }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
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

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
                {{-- LEFT COLUMN --}}
                <div class="space-y-5 xl:col-span-2">
                    {{-- Lead overview --}}
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                            <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-person-vcard me-1 text-indigo-500"></i>{{ __('Lead Overview') }}</h3>
                            <span class="text-[11px] font-semibold text-slate-400">{{ $lead->next_follow_up_at ? __('Next: ') . $lead->next_follow_up_at->format('d M, h:i A') : __('No follow-up scheduled') }}</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Phone / WhatsApp') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->phone }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Email') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->email ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Assigned To') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->assignedTo?->name ?? __('Unassigned') }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Interest') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->interest ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Course') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->course?->name ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Batch') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">
                                    {{ $lead->batch?->name ?? '—' }}
                                    @if($lead->batch?->shift)
                                        <span class="text-xs font-normal text-slate-400">· {{ $lead->batch->shift }}</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Project') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->project?->name ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Address') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->address ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Last Contacted') }}</div>
                                <div class="mt-0.5 text-sm font-semibold text-slate-800">{{ $lead->last_contacted_at ? $lead->last_contacted_at->format('d M Y, h:i A') : '—' }}</div>
                            </div>
                            <div class="sm:col-span-2 lg:col-span-3">
                                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Notes') }}</div>
                                <div class="mt-1 whitespace-pre-line rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $lead->notes ?: __('No notes yet.') }}</div>
                            </div>
                            @if($lead->lost_reason)
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <div class="text-[11px] font-bold uppercase tracking-wide text-rose-400">{{ __('Lost Reason') }}</div>
                                    <div class="mt-1 whitespace-pre-line rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $lead->lost_reason }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Follow-ups --}}
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                            <h3 class="text-sm font-bold text-slate-800">
                                <i class="bi bi-calendar-check me-1 text-amber-500"></i>{{ __('Follow-ups') }}
                                @if($leadOverdue > 0)
                                    <span class="badge bg-danger ms-1">{{ $leadOverdue }} {{ __('overdue') }}</span>
                                @endif
                            </h3>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#addFollowUpModal">
                                <i class="bi bi-plus-lg me-1"></i>{{ __('Schedule') }}
                            </button>
                        </div>

                        <div class="p-4">
                            <div class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Pending') }}</div>
                            @forelse($pendingFollowUps as $followUp)
                                @php
                                    $state = $followUp->state();
                                    $stateClass = $state === 'overdue' ? 'bg-rose-100 text-rose-700' : ($state === 'today' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700');
                                @endphp
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="badge bg-white text-dark border"><i class="bi {{ $followUp->typeIcon() }} me-1"></i>{{ $followUp->typeLabel() }}</span>
                                            <span class="text-xs font-semibold text-slate-700">{{ $followUp->scheduled_at?->format('d M Y, h:i A') }}</span>
                                            <span class="badge {{ $stateClass }}">{{ ucfirst($state) }}</span>
                                        </div>
                                        @if($followUp->notes)
                                            <div class="mt-1 text-xs text-slate-500">{{ $followUp->notes }}</div>
                                        @endif
                                        <div class="mt-0.5 text-[10px] text-slate-400">{{ __('Set by') }} {{ $followUp->user?->name ?? __('System') }}</div>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <form action="{{ route('crm.follow-ups.complete', $followUp) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" title="{{ __('Mark done') }}">
                                                <i class="bi bi-check2"></i> {{ __('Done') }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" title="{{ __('Reschedule') }}"
                                                data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $followUp->id }}">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <form action="{{ route('crm.follow-ups.destroy', $followUp) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('{{ __('Remove this follow-up?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="{{ __('Remove') }}">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-3 py-4 text-center text-xs text-slate-400">
                                    {{ __('No pending follow-ups. Schedule the next touchpoint to keep this lead warm.') }}
                                </p>
                            @endforelse

                            @if($closedFollowUps->isNotEmpty())
                                <div class="mb-2 mt-4 text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('History') }}</div>
                                <div class="space-y-1">
                                    @foreach($closedFollowUps as $followUp)
                                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg px-2 py-1.5 text-xs hover:bg-slate-50">
                                            <div class="flex flex-wrap items-center gap-2 text-slate-500">
                                                <i class="bi {{ $followUp->typeIcon() }}"></i>
                                                <span>{{ $followUp->typeLabel() }}</span>
                                                <span>·</span>
                                                <span>{{ $followUp->scheduled_at?->format('d M Y, h:i A') }}</span>
                                                <span class="badge {{ $followUp->status === 'done' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($followUp->status) }}</span>
                                            </div>
                                            @if($followUp->notes)
                                                <span class="max-w-[60%] truncate text-[11px] text-slate-400" title="{{ $followUp->notes }}">{{ $followUp->notes }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                            <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-clock-history me-1 text-slate-400"></i>{{ __('Activity Timeline') }}</h3>
                            <span class="text-[11px] font-semibold text-slate-400">{{ $timeline->count() }} {{ __('entries') }}</span>
                        </div>
                        <div class="p-5">
                            @forelse($timeline as $item)
                                @php $model = $item['model']; @endphp
                                <div class="relative border-l border-slate-200 pb-4 pl-4 last:pb-0">
                                    <span class="absolute -left-[9px] top-0 flex h-4 w-4 items-center justify-center rounded-full {{ $item['kind'] === 'follow_up' ? 'bg-amber-100 text-amber-600' : ($model->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500') }}">
                                        <i class="bi {{ $item['kind'] === 'follow_up' ? $model->typeIcon() : $model->channelIcon() }}" style="font-size: 9px;"></i>
                                    </span>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-bold text-slate-700">
                                            @if($item['kind'] === 'follow_up')
                                                {{ $model->typeLabel() }} {{ __('follow-up') }}
                                            @elseif($model->channel === 'note')
                                                {{ __('Internal note') }}
                                            @else
                                                {{ $model->channelLabel() }} {{ $model->direction === 'outbound' ? __('sent') : __('received') }}
                                            @endif
                                        </span>
                                        <span class="text-[11px] text-slate-400">{{ optional($item['at'])->format('d M Y, h:i A') }}</span>
                                        @if($item['kind'] === 'follow_up')
                                            <span class="badge {{ $model->status === 'done' ? 'bg-emerald-100 text-emerald-700' : ($model->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ ucfirst($model->status) }}</span>
                                        @else
                                            <span class="badge {{ $model->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($model->status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">{{ ucfirst((string) ($model->status ?: 'logged')) }}</span>
                                        @endif
                                        <span class="text-[11px] text-slate-400">· {{ $model->user?->name ?? __('System') }}</span>
                                    </div>
                                    @if($item['kind'] === 'message' && filled($model->body))
                                        <div class="mt-1 whitespace-pre-line rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-600">{{ $model->body }}</div>
                                    @elseif($item['kind'] === 'follow_up' && filled($model->notes))
                                        <div class="mt-1 whitespace-pre-line text-xs text-slate-500">{{ $model->notes }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="py-6 text-center text-xs text-slate-400">{{ __('No activity yet. Log a message or complete a follow-up to start the timeline.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-5">
                    {{-- Pipeline status --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-3 text-sm font-bold text-slate-800"><i class="bi bi-signpost-split me-1 text-violet-500"></i>{{ __('Pipeline Status') }}</h3>
                        <form method="POST" action="{{ route('crm.leads.status', $lead) }}" id="leadStatusForm">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select rounded-3 js-lead-status" required {{ $lead->isConverted() ? 'disabled' : '' }}>
                                @foreach(\App\Models\Lead::STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ $lead->status === $value ? 'selected' : '' }} {{ $value === 'converted' && ! $lead->converted_student_id ? 'disabled' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="js-lost-reason mt-2 {{ $lead->status === 'lost' ? '' : 'd-none' }}">
                                <label class="form-label fw-bold text-dark small">{{ __('Reason for losing the lead') }}</label>
                                <textarea name="lost_reason" rows="2" class="form-control rounded-3">{{ $lead->lost_reason }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-3 mt-3" {{ $lead->isConverted() ? 'disabled' : '' }}>
                                <i class="bi bi-check2-circle me-1"></i> {{ __('Update Status') }}
                            </button>
                        </form>
                        @if($lead->isConverted())
                            <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                                <i class="bi bi-mortarboard me-1"></i>
                                {{ __('Enrolled on') }} {{ optional($lead->converted_at)->format('d M Y') }}.
                            </div>
                        @endif
                    </div>

                    {{-- WhatsApp composer --}}
                    <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-800"><i class="bi bi-whatsapp me-1 text-emerald-500"></i>{{ __('Message the Lead') }}</h3>
                            <span class="badge {{ $whatsappCloud ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $whatsappCloud ? __('Cloud API') : __('wa.me link') }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('crm.messages.store', $lead) }}" id="leadMessageForm">
                            @csrf
                            <label class="form-label fw-bold text-dark small">{{ __('Channel') }}</label>
                            <select name="channel" class="form-select form-select-sm rounded-3 js-message-channel">
                                <option value="whatsapp" {{ old('channel', 'whatsapp') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="sms" {{ old('channel') === 'sms' ? 'selected' : '' }}>SMS (log only)</option>
                                <option value="note" {{ old('channel') === 'note' ? 'selected' : '' }}>Internal Note</option>
                            </select>

                            <label class="form-label fw-bold text-dark small mt-2">{{ __('Template') }}</label>
                            <select class="form-select form-select-sm rounded-3 js-message-template">
                                <option value="">{{ __('— Pick a template —') }}</option>
                                <option value="intro">{{ __('Introduction') }}</option>
                                <option value="follow_up">{{ __('Friendly follow-up') }}</option>
                                <option value="documents">{{ __('Document request') }}</option>
                                <option value="reminder">{{ __('Follow-up reminder') }}</option>
                            </select>

                            <label class="form-label fw-bold text-dark small mt-2">{{ __('Message') }}</label>
                            <textarea name="body" rows="5" class="form-control rounded-3 js-message-body" required placeholder="{{ __('Write your message…') }}">{{ old('body', $whatsappTemplates['intro'] ?? '') }}</textarea>

                            <button type="submit" class="btn btn-success w-100 rounded-3 mt-3">
                                <i class="bi bi-send me-1"></i> {{ __('Send / Log Message') }}
                            </button>
                            <p class="mt-2 mb-0 text-[11px] text-slate-400">
                                {{ $whatsappCloud
                                    ? __('The message is sent through the WhatsApp Cloud API and logged on the timeline.')
                                    : __('The message is logged and a ready-to-send WhatsApp link is shown after saving.') }}
                            </p>
                        </form>
                    </div>

                    {{-- Conversion card --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-3 text-sm font-bold text-slate-800"><i class="bi bi-mortarboard me-1 text-emerald-500"></i>{{ __('Admission') }}</h3>
                        @if($lead->isConverted() && $lead->convertedStudent)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ __('Converted Student') }}</div>
                                <div class="mt-1 text-sm font-bold text-emerald-900">{{ $lead->convertedStudent->name_en }}</div>
                                <div class="text-xs text-emerald-700">{{ $lead->convertedStudent->reference_no }}</div>
                                <div class="mt-1 text-[11px] text-emerald-700">
                                    {{ __('Status') }}: <strong>{{ ucfirst($lead->convertedStudent->status) }}</strong>
                                    @if($lead->convertedStudent->batch)
                                        · {{ $lead->convertedStudent->batch->name }}
                                    @endif
                                </div>
                                <a href="{{ route('admissions.show', $lead->convertedStudent) }}" class="btn btn-sm btn-success w-100 rounded-3 mt-2">
                                    {{ __('Open Student Record') }} <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-slate-500">
                                {{ __('When this lead is ready, convert them into a student. The admission record is created automatically and linked back to this lead.') }}
                            </p>
                            <button type="button" class="btn btn-success w-100 rounded-3 mt-3" data-bs-toggle="modal" data-bs-target="#convertLeadModal">
                                <i class="bi bi-mortarboard me-1"></i> {{ __('Convert to Student') }}
                            </button>
                        @endif
                    </div>

                    {{-- Danger zone --}}
                    @if(! $lead->isConverted())
                        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                            <h3 class="mb-2 text-sm font-bold text-rose-600"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('Delete Lead') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Removes the lead, its follow-ups and the full message history.') }}</p>
                            <form action="{{ route('crm.leads.destroy', $lead) }}" method="POST" class="mt-2"
                                  onsubmit="return confirm('{{ __('Delete this lead permanently?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100 rounded-3">
                                    <i class="bi bi-trash me-1"></i> {{ __('Delete Lead') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit lead modal --}}
    <div class="modal fade" id="editLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white">
                        <i class="bi bi-pencil-square text-primary me-1"></i> {{ __('Edit Lead') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('crm.leads.update', $lead) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="edit-lead">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Full Name') }} *</label>
                                <input type="text" name="name" value="{{ $editValue('name', $lead->name) }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Phone / WhatsApp') }} *</label>
                                <input type="text" name="phone" value="{{ $editValue('phone', $lead->phone) }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Email') }}</label>
                                <input type="email" name="email" value="{{ $editValue('email', $lead->email) }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Address') }}</label>
                                <input type="text" name="address" value="{{ $editValue('address', $lead->address) }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Source') }} *</label>
                                <select name="source" class="form-select rounded-3" required>
                                    @foreach(\App\Models\Lead::SOURCES as $value => $label)
                                        <option value="{{ $value }}" {{ $editValue('source', $lead->source) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Assigned To') }}</label>
                                <select name="assigned_to" class="form-select rounded-3">
                                    <option value="">{{ __('— Unassigned —') }}</option>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}" {{ (string) $editValue('assigned_to', $lead->assigned_to) === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Interest') }}</label>
                                <input type="text" name="interest" value="{{ $editValue('interest', $lead->interest) }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small">{{ __('Course') }}</label>
                                <select name="course_id" class="form-select rounded-3 js-edit-course">
                                    <option value="">{{ __('— Any —') }}</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ (string) $editValue('course_id', $lead->course_id) === (string) $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small">{{ __('Batch') }}</label>
                                <select name="batch_id" class="form-select rounded-3 js-edit-batch">
                                    <option value="">{{ __('— Any —') }}</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" data-course-id="{{ $batch->course_id }}" {{ (string) $editValue('batch_id', $lead->batch_id) === (string) $batch->id ? 'selected' : '' }}>
                                            {{ $batch->name }}{{ $batch->shift ? ' · ' . $batch->shift : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">{{ __('Notes') }}</label>
                                <textarea name="notes" rows="3" class="form-control rounded-3">{{ $editValue('notes', $lead->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Update Lead') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add follow-up modal --}}
    <div class="modal fade" id="addFollowUpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white">
                        <i class="bi bi-calendar-plus text-primary me-1"></i> {{ __('Schedule Follow-up') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('crm.follow-ups.store', $lead) }}">
                    @csrf
                    <input type="hidden" name="_modal" value="add-follow-up">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Type') }} *</label>
                                <select name="type" class="form-select rounded-3" required>
                                    @foreach(\App\Models\LeadFollowUp::TYPES as $value => $label)
                                        <option value="{{ $value }}" {{ old('type', 'call') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Date & Time') }} *</label>
                                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', now()->addDay()->format('Y-m-d\TH:i')) }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">{{ __('Notes') }}</label>
                                <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="{{ __('What should be discussed?') }}">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Follow-up') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reschedule modals --}}
    @foreach($pendingFollowUps as $followUp)
        <div class="modal fade" id="rescheduleModal{{ $followUp->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-dark text-white rounded-top-4">
                        <h5 class="modal-header-title fw-bold mb-0 text-white">
                            <i class="bi bi-arrow-repeat text-primary me-1"></i> {{ __('Reschedule Follow-up') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('crm.follow-ups.reschedule', $followUp) }}">
                        @csrf
                        <div class="modal-body p-4">
                            <p class="text-xs text-slate-500">
                                {{ $followUp->typeLabel() }} · {{ __('currently') }} {{ $followUp->scheduled_at?->format('d M Y, h:i A') }}
                            </p>
                            <label class="form-label fw-bold text-dark small">{{ __('New Date & Time') }} *</label>
                            <input type="datetime-local" name="scheduled_at" value="{{ $followUp->scheduled_at?->format('Y-m-d\TH:i') }}" class="form-control rounded-3" required>
                        </div>
                        <div class="modal-footer bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                                <i class="bi bi-check-circle-fill me-1"></i> {{ __('Reschedule') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Convert lead modal --}}
    @if(! $lead->isConverted())
        <div class="modal fade" id="convertLeadModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-dark text-white rounded-top-4">
                        <h5 class="modal-header-title fw-bold mb-0 text-white">
                            <i class="bi bi-mortarboard text-success me-1"></i> {{ __('Convert Lead to Student') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('crm.leads.convert', $lead) }}" id="convertLeadForm">
                        @csrf
                        <input type="hidden" name="_modal" value="convert-lead">
                        <div class="modal-body p-4">
                            <div class="mb-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-800">
                                <i class="bi bi-info-circle me-1"></i>
                                {{ __('This creates a real student record with a unique reference number. Selecting a batch marks the student as admitted; otherwise the application is saved as applied for review.') }}
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Full Name (English)') }} *</label>
                                    <input type="text" name="name_en" value="{{ $convertValue('name_en', $lead->name) }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Full Name (Bangla)') }}</label>
                                    <input type="text" name="name_bn" value="{{ $convertValue('name_bn') }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Contact Number') }} *</label>
                                    <input type="text" name="contact" value="{{ $convertValue('contact', $lead->phone) }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Email') }}</label>
                                    <input type="email" name="email" value="{{ $convertValue('email', $lead->email) }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Father Name (English)') }} *</label>
                                    <input type="text" name="father_name_en" value="{{ $convertValue('father_name_en') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Mother Name (English)') }} *</label>
                                    <input type="text" name="mother_name_en" value="{{ $convertValue('mother_name_en') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Date of Birth') }} *</label>
                                    <input type="date" name="dob" value="{{ $convertValue('dob') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Gender') }} *</label>
                                    <select name="gender" class="form-select rounded-3" required>
                                        <option value="">{{ __('— Select —') }}</option>
                                        @foreach(['Male', 'Female', 'Other'] as $gender)
                                            <option value="{{ $gender }}" {{ $convertValue('gender') === $gender ? 'selected' : '' }}>{{ $gender }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Occupation') }}</label>
                                    <input type="text" name="occupation" value="{{ $convertValue('occupation') }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('ID Type') }} *</label>
                                    <select name="id_type" class="form-select rounded-3" required>
                                        <option value="">{{ __('— Select —') }}</option>
                                        @foreach(['NID' => 'National ID (NID)', 'Birth' => 'Birth Registration', 'Passport' => 'Passport', 'Student ID' => 'Student ID'] as $value => $label)
                                            <option value="{{ $value }}" {{ $convertValue('id_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('ID Number') }} *</label>
                                    <input type="text" name="id_no" value="{{ $convertValue('id_no') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Blood Group') }}</label>
                                    <select name="blood_group" class="form-select rounded-3">
                                        <option value="">{{ __('— Unknown —') }}</option>
                                        @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $group)
                                            <option value="{{ $group }}" {{ $convertValue('blood_group') === $group ? 'selected' : '' }}>{{ $group }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Guardian Contact') }} *</label>
                                    <input type="text" name="guardian_contact" value="{{ $convertValue('guardian_contact') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">{{ __('Guardian Relation') }} *</label>
                                    <select name="guardian_relation" class="form-select rounded-3" required>
                                        <option value="">{{ __('— Select —') }}</option>
                                        @foreach(['Father', 'Mother', 'Brother', 'Sister', 'Uncle', 'Aunt', 'Spouse', 'Other'] as $relation)
                                            <option value="{{ $relation }}" {{ $convertValue('guardian_relation') === $relation ? 'selected' : '' }}>{{ $relation }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Religion') }}</label>
                                    <select name="religion" class="form-select rounded-3">
                                        <option value="">{{ __('— Select —') }}</option>
                                        @foreach(['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Other'] as $religion)
                                            <option value="{{ $religion }}" {{ $convertValue('religion') === $religion ? 'selected' : '' }}>{{ $religion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Nationality') }}</label>
                                    <input type="text" name="nationality" value="{{ $convertValue('nationality', 'Bangladeshi') }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Course') }}</label>
                                    <select name="course_id" class="form-select rounded-3 js-convert-course">
                                        <option value="">{{ __('— Keep lead course —') }}</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ (string) $convertValue('course_id', $lead->course_id) === (string) $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Batch') }} <span class="font-normal text-slate-400">({{ __('admits immediately') }})</span></label>
                                    <select name="batch_id" class="form-select rounded-3 js-convert-batch">
                                        <option value="">{{ __('— No batch yet —') }}</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}" data-course-id="{{ $batch->course_id }}" {{ (string) $convertValue('batch_id', $lead->batch_id) === (string) $batch->id ? 'selected' : '' }}>
                                                {{ $batch->name }}{{ $batch->shift ? ' · ' . $batch->shift : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Present Village / Area') }}</label>
                                    <input type="text" name="present_village" value="{{ $convertValue('present_village') }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Present District') }}</label>
                                    <input type="text" name="present_district" value="{{ $convertValue('present_district') }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">{{ __('Present Division') }}</label>
                                    <input type="text" name="present_division" value="{{ $convertValue('present_division') }}" class="form-control rounded-3">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success rounded-3 font-bold px-4">
                                <i class="bi bi-mortarboard me-1"></i> {{ __('Convert & Create Student') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if($isConvertError)
                var convertModal = document.getElementById('convertLeadModal');
                if (convertModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(convertModal).show();
            @elseif($isEditError)
                var editModal = document.getElementById('editLeadModal');
                if (editModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(editModal).show();
            @elseif($isFollowError)
                var followModal = document.getElementById('addFollowUpModal');
                if (followModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(followModal).show();
            @endif

            var statusSelect = document.querySelector('.js-lead-status');
            var lostWrap = document.querySelector('.js-lost-reason');
            if (statusSelect && lostWrap) {
                var toggleLost = function () {
                    lostWrap.classList.toggle('d-none', statusSelect.value !== 'lost');
                };
                statusSelect.addEventListener('change', toggleLost);
                toggleLost();
            }

            function filterBatches(courseSelect, batchSelect) {
                if (!courseSelect || !batchSelect) return;
                var courseId = courseSelect.value;
                Array.prototype.forEach.call(batchSelect.options, function (option) {
                    if (!option.value) return;
                    var show = !courseId || option.dataset.courseId === courseId;
                    option.hidden = !show;
                    option.disabled = !show;
                    if (!show && option.selected) batchSelect.value = '';
                });
            }

            var editCourse = document.querySelector('.js-edit-course');
            var editBatch = document.querySelector('.js-edit-batch');
            if (editCourse && editBatch) {
                editCourse.addEventListener('change', function () { filterBatches(editCourse, editBatch); });
                filterBatches(editCourse, editBatch);
            }

            var convertCourse = document.querySelector('.js-convert-course');
            var convertBatch = document.querySelector('.js-convert-batch');
            if (convertCourse && convertBatch) {
                convertCourse.addEventListener('change', function () { filterBatches(convertCourse, convertBatch); });
                filterBatches(convertCourse, convertBatch);
            }

            var templates = @json($whatsappTemplates);
            var templateSelect = document.querySelector('.js-message-template');
            var messageBody = document.querySelector('.js-message-body');
            if (templateSelect && messageBody) {
                templateSelect.addEventListener('change', function () {
                    if (templates[templateSelect.value]) {
                        messageBody.value = templates[templateSelect.value];
                    }
                });
            }
        });
    </script>
</x-app-layout>
