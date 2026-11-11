<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Leads') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Every Facebook, website, phone and walk-in enquiry in one pipeline.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('crm.leads.export', request()->query()) }}" class="btn btn-outline-success rounded-3">
                    <i class="bi bi-file-earmark-excel me-1"></i> {{ __('Export CSV') }}
                </a>
                <button type="button" class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createLeadModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> {{ __('New Lead') }}
                </button>
            </div>
        </div>
    </x-slot>

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

            {{-- Status pills --}}
            @php
                $totalAll = (int) $statusCounts->sum();
                $statusPillClass = [
                    'new' => 'bg-sky-50 text-sky-700 border-sky-200',
                    'contacted' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'qualified' => 'bg-violet-50 text-violet-700 border-violet-200',
                    'follow_up' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'converted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'lost' => 'bg-rose-50 text-rose-700 border-rose-200',
                ];
            @endphp
            <div class="mb-4 flex flex-wrap gap-2">
                <a href="{{ route('crm.leads', request()->except(['status', 'page'])) }}"
                   class="rounded-xl border px-3 py-2 text-xs font-semibold transition {{ request('status') ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' : 'border-indigo-600 bg-indigo-600 text-white' }}">
                    {{ __('All') }} <span class="ms-1 font-bold">{{ $totalAll }}</span>
                </a>
                @foreach(\App\Models\Lead::STATUSES as $value => $label)
                    <a href="{{ route('crm.leads', array_merge(request()->except(['status', 'page']), ['status' => $value])) }}"
                       class="rounded-xl border px-3 py-2 text-xs font-semibold transition {{ request('status') === $value ? 'ring-2 ring-indigo-300 ' . ($statusPillClass[$value] ?? 'bg-slate-50 text-slate-700') : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        {{ $label }} <span class="ms-1 font-bold">{{ (int) ($statusCounts[$value] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-4">
                    <form method="GET" action="{{ route('crm.leads') }}" class="flex flex-wrap items-end gap-2">
                        <div class="min-w-[200px] flex-1">
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Search') }}</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-3"
                                   placeholder="{{ __('Name, phone, email or interest…') }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Source') }}</label>
                            <select name="source" class="form-select form-select-sm rounded-3" style="min-width: 140px;">
                                <option value="">{{ __('All Sources') }}</option>
                                @foreach(\App\Models\Lead::SOURCES as $value => $label)
                                    <option value="{{ $value }}" {{ request('source') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                            <select name="status" class="form-select form-select-sm rounded-3" style="min-width: 130px;">
                                <option value="">{{ __('All Statuses') }}</option>
                                @foreach(\App\Models\Lead::STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Follow-up') }}</label>
                            <select name="followup" class="form-select form-select-sm rounded-3" style="min-width: 130px;">
                                <option value="">{{ __('Any') }}</option>
                                <option value="overdue" {{ request('followup') === 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                                <option value="today" {{ request('followup') === 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                                <option value="upcoming" {{ request('followup') === 'upcoming' ? 'selected' : '' }}>{{ __('Upcoming') }}</option>
                                <option value="none" {{ request('followup') === 'none' ? 'selected' : '' }}>{{ __('Not scheduled') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Assigned') }}</label>
                            <select name="assigned" class="form-select form-select-sm rounded-3" style="min-width: 140px;">
                                <option value="">{{ __('Anyone') }}</option>
                                @foreach($staff as $member)
                                    <option value="{{ $member->id }}" {{ (string) request('assigned') === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary rounded-3">
                            <i class="bi bi-funnel me-1"></i> {{ __('Apply') }}
                        </button>
                        <a href="{{ route('crm.leads') }}" class="btn btn-sm btn-outline-secondary rounded-3">{{ __('Reset') }}</a>
                        <span class="ms-auto self-center text-[11px] font-semibold text-slate-400">{{ $filteredCount }} {{ __('lead(s) match') }}</span>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small datatable" data-dt-page-length="20">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('Lead') }}</th>
                                <th>{{ __('Contact') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Interest / Course') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Next Follow-up') }}</th>
                                <th>{{ __('Assigned') }}</th>
                                <th class="text-center pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                                @php
                                    $state = $lead->followUpState();
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('crm.leads.show', $lead) }}" class="fw-semibold text-decoration-none text-dark">{{ $lead->name }}</a>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $lead->email ?: ($lead->address ?: optional($lead->created_at)->diffForHumans()) }}
                                        </div>
                                    </td>
                                    <td>
                                        <a href="tel:{{ $lead->phone }}" class="text-decoration-none text-dark">{{ $lead->phone }}</a>
                                        <div class="d-flex gap-1 mt-1">
                                            <a href="https://wa.me/{{ app(\App\Services\WhatsAppService::class)->normalizePhone($lead->phone) }}"
                                               target="_blank" rel="noopener" class="badge bg-success text-decoration-none" title="{{ __('Open WhatsApp') }}">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>
                                            <a href="tel:{{ $lead->phone }}" class="badge bg-light text-dark border text-decoration-none" title="{{ __('Call') }}">
                                                <i class="bi bi-telephone"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi {{ $lead->sourceIcon() }} me-1"></i>{{ $lead->sourceLabel() }}</span></td>
                                    <td class="text-muted">
                                        {{ $lead->interest ?: '—' }}
                                        @if($lead->course)
                                            <div style="font-size: 11px;"><i class="bi bi-journal-text me-1"></i>{{ $lead->course->name }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span></td>
                                    <td>
                                        @if($lead->next_follow_up_at)
                                            <div class="fw-semibold {{ $state === 'overdue' ? 'text-danger' : ($state === 'today' ? 'text-warning' : 'text-slate-700') }}">
                                                {{ $lead->next_follow_up_at->format('d M Y') }}
                                            </div>
                                            <div style="font-size: 11px;" class="{{ $state === 'overdue' ? 'text-danger' : 'text-muted' }}">
                                                {{ $lead->next_follow_up_at->format('h:i A') }}
                                                @if($state === 'overdue')
                                                    · {{ __('overdue') }}
                                                @elseif($state === 'today')
                                                    · {{ __('today') }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $lead->assignedTo?->name ?? '—' }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="{{ __('Open lead') }}">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                            <form action="{{ route('crm.leads.destroy', $lead) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Delete this lead? Follow-ups and message history will also be removed.') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="{{ __('Delete') }}"
                                                        {{ $lead->isConverted() ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-muted">
                                        <i class="bi bi-inboxes d-block mb-2 fs-2"></i>
                                        {{ __('No leads match the current filters.') }}
                                        <button type="button" class="ms-1 text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#createLeadModal">{{ __('Add a lead') }}</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Create lead modal --}}
    <div class="modal fade" id="createLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-header-title fw-bold mb-0 text-white">
                        <i class="bi bi-person-plus text-primary me-1"></i> {{ __('Capture New Lead') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('crm.leads.store') }}" id="createLeadForm">
                    @csrf
                    <input type="hidden" name="_modal" value="create-lead">
                    <div class="modal-body p-4">
                        @include('partials.modal-errors')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Full Name') }} *</label>
                                <input type="text" name="name" value="{{ old('_modal') === 'create-lead' ? old('name') : '' }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Phone / WhatsApp') }} *</label>
                                <input type="text" name="phone" value="{{ old('_modal') === 'create-lead' ? old('phone') : '' }}" class="form-control rounded-3" required placeholder="01XXXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Email') }}</label>
                                <input type="email" name="email" value="{{ old('_modal') === 'create-lead' ? old('email') : '' }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Source') }} *</label>
                                <select name="source" class="form-select rounded-3" required>
                                    @foreach(\App\Models\Lead::SOURCES as $value => $label)
                                        <option value="{{ $value }}" {{ (old('_modal') === 'create-lead' ? old('source') : 'website') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Status') }}</label>
                                <select name="status" class="form-select rounded-3">
                                    @foreach(\App\Models\Lead::MANUAL_STATUSES as $value => $label)
                                        <option value="{{ $value }}" {{ old('_modal') === 'create-lead' && old('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Assigned To') }}</label>
                                <select name="assigned_to" class="form-select rounded-3">
                                    <option value="">{{ __('— Unassigned —') }}</option>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}" {{ old('_modal') === 'create-lead' && (string) old('assigned_to') === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">{{ __('Interest') }}</label>
                                <input type="text" name="interest" value="{{ old('_modal') === 'create-lead' ? old('interest') : '' }}" class="form-control rounded-3" placeholder="{{ __('e.g. Web Development, Evening batch') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small">{{ __('Course') }}</label>
                                <select name="course_id" class="form-select rounded-3 js-lead-course">
                                    <option value="">{{ __('— Any —') }}</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" data-project-id="{{ $course->project_id }}"
                                            {{ old('_modal') === 'create-lead' && (string) old('course_id') === (string) $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small">{{ __('Batch') }}</label>
                                <select name="batch_id" class="form-select rounded-3 js-lead-batch">
                                    <option value="">{{ __('— Any —') }}</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" data-course-id="{{ $batch->course_id }}"
                                            {{ old('_modal') === 'create-lead' && (string) old('batch_id') === (string) $batch->id ? 'selected' : '' }}>
                                            {{ $batch->name }}{{ $batch->shift ? ' · ' . $batch->shift : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small">{{ __('Next Follow-up') }}</label>
                                <input type="datetime-local" name="next_follow_up_at" value="{{ old('_modal') === 'create-lead' ? old('next_follow_up_at') : '' }}" class="form-control rounded-3">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small">{{ __('Follow-up Type') }}</label>
                                <select name="follow_up_type" class="form-select rounded-3">
                                    @foreach(\App\Models\LeadFollowUp::TYPES as $value => $label)
                                        <option value="{{ $value }}" {{ old('_modal') === 'create-lead' && old('follow_up_type') === $value ? 'selected' : ($value === 'call' && old('_modal') !== 'create-lead' ? 'selected' : '') }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">{{ __('Notes') }}</label>
                                <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="{{ __('What did they ask about? Budget, timing, documents…') }}">{{ old('_modal') === 'create-lead' ? old('notes') : '' }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-800">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ __('Setting a next follow-up date automatically creates a reminder in the follow-up queue.') }}
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ __('Save Lead') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(request('create') || old('_modal') === 'create-lead')
                var createModal = document.getElementById('createLeadModal');
                if (createModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(createModal).show();
            @endif

            function filterBatches(courseSelect, batchSelect) {
                if (!courseSelect || !batchSelect) return;
                var courseId = courseSelect.value;
                Array.prototype.forEach.call(batchSelect.options, function (option) {
                    if (!option.value) return;
                    var show = !courseId || option.dataset.courseId === courseId;
                    option.hidden = !show;
                    option.disabled = !show;
                    if (!show && option.selected) {
                        batchSelect.value = '';
                    }
                });
            }

            var course = document.querySelector('#createLeadForm .js-lead-course');
            var batch = document.querySelector('#createLeadForm .js-lead-batch');
            if (course && batch) {
                course.addEventListener('change', function () { filterBatches(course, batch); });
                filterBatches(course, batch);
            }
        });
    </script>
</x-app-layout>
