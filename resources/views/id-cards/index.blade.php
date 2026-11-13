<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="flex items-center gap-2 text-lg font-semibold leading-tight text-gray-800 sm:text-xl">
                    <i class="bi bi-person-vcard text-teal-500"></i>
                    <span class="truncate">{{ __('Student & Employee ID Cards') }}</span>
                </h2>
                <p class="mt-1 text-xs text-slate-500">{{ __('Photo ID cards with QR verification, a front/back design editor and batch PDF printing.') }}</p>
            </div>
            <a href="{{ route('id-cards.editor', ['type' => $type]) }}"
               class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                <i class="bi bi-sliders2"></i>
                {{ __('Design Editor') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Stats --}}
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Students') }}</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600"><i class="bi bi-mortarboard-fill"></i></span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($counts['student']) }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ __(':n have a photo on file', ['n' => number_format($counts['student_photo'])]) }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Employees') }}</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><i class="bi bi-person-badge-fill"></i></span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($counts['employee']) }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ __(':n have a photo on file', ['n' => number_format($counts['employee_photo'])]) }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Ready to print') }}</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><i class="bi bi-printer-fill"></i></span>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-800"><span data-selected-count>0</span></p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ __('Front + back printed for each person') }}</p>
                </div>
            </div>

            {{-- Type tabs + filters --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex rounded-xl border border-slate-200 bg-slate-100 p-1">
                        <a href="{{ route('id-cards.index', ['type' => 'student']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $type === 'student' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            <i class="bi bi-mortarboard"></i>
                            {{ __('Students') }}
                            <span class="rounded-full bg-slate-100 px-1.5 text-[11px] font-bold text-slate-500">{{ number_format($counts['student']) }}</span>
                        </a>
                        <a href="{{ route('id-cards.index', ['type' => 'employee']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $type === 'employee' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            <i class="bi bi-person-badge"></i>
                            {{ __('Employees') }}
                            <span class="rounded-full bg-slate-100 px-1.5 text-[11px] font-bold text-slate-500">{{ number_format($counts['employee']) }}</span>
                        </a>
                    </div>
                    <p class="text-xs text-slate-500">{{ __(':n record(s) match the current filters', ['n' => $people->count()]) }}</p>
                </div>

                <form method="GET" action="{{ route('id-cards.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="min-w-[220px] flex-1">
                        <label for="idc_q" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Search') }}</label>
                        <div class="relative">
                            <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="idc_q" type="search" name="q" value="{{ $q }}"
                                   placeholder="{{ $type === 'student' ? __('Name, ID no or phone') : __('Name, designation or email') }}"
                                   class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                    </div>

                    @if($type === 'student')
                        <div class="w-full sm:w-48">
                            <label for="idc_course" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Course') }}</label>
                            <select id="idc_course" name="course_id"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                <option value="">{{ __('All courses') }}</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-48">
                            <label for="idc_batch" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Batch') }}</label>
                            <select id="idc_batch" name="batch_id"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                <option value="">{{ __('All batches') }}</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected((string) request('batch_id') === (string) $batch->id)>{{ $batch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-40">
                            <label for="idc_status" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</label>
                            <select id="idc_status" name="status"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                <option value="">{{ __('Any status') }}</option>
                                @foreach(['admitted', 'active', 'completed', 'selected', 'shortlisted', 'applied'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="w-full sm:w-48">
                            <label for="idc_role" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Role') }}</label>
                            <select id="idc_role" name="role"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                <option value="">{{ __('All roles') }}</option>
                                <option value="admin" @selected(request('role') === 'admin')>{{ __('Administrator') }}</option>
                                <option value="trainer" @selected(request('role') === 'trainer')>{{ __('Trainer') }}</option>
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn-solid-indigo">
                            <i class="bi bi-funnel-fill"></i>
                            {{ __('Apply') }}
                        </button>
                        <a href="{{ route('id-cards.index', ['type' => $type]) }}"
                           class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            {{-- Selectable list + print form --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <form id="printForm" method="GET" action="{{ route('id-cards.print') }}">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">
                                {{ $type === 'student' ? __('Students') : __('Employees') }}
                            </h3>
                            <p class="mt-0.5 text-xs text-slate-400">{{ __('Tick the people to include. Cards are printed front + back, four people per A4 page.') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                <span data-selected-count>0</span> {{ __('selected') }}
                            </span>
                            <button type="submit" id="printSelected" disabled
                                    class="btn-solid-indigo disabled:cursor-not-allowed disabled:opacity-50">
                                <i class="bi bi-printer-fill"></i>
                                {{ __('Print selected') }}
                            </button>
                        </div>
                    </div>

                    @if($people->isEmpty())
                        <div class="px-4 py-16 text-center text-slate-500">
                            <i class="bi bi-person-vcard mb-3 block text-4xl text-slate-300"></i>
                            <p class="text-sm font-semibold">{{ __('No matching records found.') }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ __('Try a different search term or clear the filters.') }}</p>
                        </div>
                    @else
                        <div class="max-h-[60vh] overflow-auto">
                            <table class="w-full min-w-[920px] text-left text-sm">
                                <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="w-10 px-4 py-3">
                                            <input type="checkbox" data-select-all
                                                   aria-label="{{ __('Select all') }}"
                                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </th>
                                        <th class="px-4 py-3">{{ __('Name') }}</th>
                                        <th class="px-4 py-3">{{ $type === 'student' ? __('Student ID') : __('Employee ID') }}</th>
                                        <th class="px-4 py-3">{{ $type === 'student' ? __('Course / Batch') : __('Designation') }}</th>
                                        <th class="px-4 py-3">{{ __('Contact') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($people as $person)
                                        @php
                                            $tone = match (strtolower((string) $person['status'])) {
                                                'active', 'admitted' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                                'completed' => 'border-blue-200 bg-blue-50 text-blue-700',
                                                'selected', 'shortlisted', 'applied' => 'border-amber-200 bg-amber-50 text-amber-700',
                                                default => 'border-slate-200 bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <tr class="transition hover:bg-slate-50/80">
                                            <td class="px-4 py-3">
                                                <input type="checkbox" name="ids[]" value="{{ $person['id'] }}" data-select
                                                       aria-label="{{ __('Select :name', ['name' => $person['name']]) }}"
                                                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    @if($person['photo'])
                                                        <img src="{{ $person['photo'] }}" alt=""
                                                             class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                                                    @else
                                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500">
                                                            {{ $person['initials'] }}
                                                        </span>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <p class="truncate font-semibold text-slate-800">{{ $person['name'] }}</p>
                                                        @if($person['name_bn'])
                                                            <p class="truncate text-xs text-slate-500">{{ $person['name_bn'] }}</p>
                                                        @elseif(! $person['photo'])
                                                            <p class="text-xs font-medium text-amber-600">{{ __('Photo missing') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $person['id_no'] }}</td>
                                            <td class="px-4 py-3">
                                                <p class="truncate text-slate-700">{{ $person['line1'] ?: '—' }}</p>
                                                <p class="truncate text-xs text-slate-400">{{ $person['line2'] ?: '—' }}</p>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $person['phone'] ?: '—' }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $tone }}">
                                                    {{ $person['status'] ?: '—' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('id-cards.print', ['type' => $type, 'ids' => [$person['id']]]) }}"
                                                       target="_blank" rel="noopener"
                                                       title="{{ __('Preview / print card') }}"
                                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-indigo-50 hover:text-indigo-600">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ $person['qr_url'] }}" target="_blank" rel="noopener"
                                                       title="{{ __('Open QR profile') }}"
                                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-teal-50 hover:text-teal-600">
                                                        <i class="bi bi-qr-code"></i>
                                                    </a>
                                                    <button type="button"
                                                            data-photo-target="{{ route('id-cards.photo', ['type' => $type, 'person' => $person['id']]) }}"
                                                            data-photo-name="{{ $person['name'] }}"
                                                            data-photo-src="{{ $person['photo'] }}"
                                                            title="{{ __('Upload / change photo') }}"
                                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-amber-50 hover:text-amber-600">
                                                        <i class="bi bi-camera"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-400">
                            <span>{{ __('Showing :n record(s) — filters run before printing, so you can select an entire batch.', ['n' => $people->count()]) }}</span>
                            <span class="hidden sm:inline">{{ __('Tip: shift-click to select a range of rows.') }}</span>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Card photo modal --}}
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header rounded-top-4 bg-slate-900 text-white">
                    <h5 class="modal-title fw-bold text-white" id="photoModalLabel">
                        <i class="bi bi-camera-fill text-amber-400"></i> {{ __('ID Card Photo') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form id="photoForm" method="POST" enctype="multipart/form-data" action="">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
                    <div class="modal-body p-4">
                        @if($errors->any())
                            <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <p class="text-sm text-slate-600">
                            {{ __('Upload a clear passport-style photo for') }}
                            <strong id="photoName" class="text-slate-800">—</strong>.
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <img id="photoPreview" src="" alt=""
                                 class="hidden h-20 w-20 rounded-xl object-cover ring-1 ring-slate-200">
                            <span id="photoPlaceholder"
                                  class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                <i class="bi bi-person-bounding-box text-2xl"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <input type="file" name="photo" accept="image/*" required
                                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700">
                                <p class="mt-1 text-xs text-slate-400">{{ __('JPG or PNG, up to 4 MB. Used on the card and the public QR profile.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer rounded-bottom-4 bg-light">
                        <button type="button" class="btn btn-secondary rounded-3 font-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 font-bold">
                            <i class="bi bi-upload me-1"></i> {{ __('Upload Photo') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var form = document.getElementById('printForm');
                if (!form) {
                    return;
                }

                var selectAll = form.querySelector('[data-select-all]');
                var boxes = Array.prototype.slice.call(form.querySelectorAll('[data-select]'));
                var countEls = Array.prototype.slice.call(document.querySelectorAll('[data-selected-count]'));
                var printBtn = document.getElementById('printSelected');

                function refresh() {
                    var selected = boxes.filter(function (box) { return box.checked; }).length;

                    countEls.forEach(function (el) { el.textContent = selected; });
                    if (printBtn) {
                        printBtn.disabled = selected === 0;
                    }
                    if (selectAll) {
                        selectAll.checked = selected > 0 && selected === boxes.length;
                        selectAll.indeterminate = selected > 0 && selected < boxes.length;
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        boxes.forEach(function (box) { box.checked = selectAll.checked; });
                        refresh();
                    });
                }

                var lastIndex = null;
                boxes.forEach(function (box, index) {
                    box.addEventListener('change', refresh);
                    box.addEventListener('click', function (event) {
                        if (event.shiftKey && lastIndex !== null) {
                            var start = Math.min(lastIndex, index);
                            var end = Math.max(lastIndex, index);
                            for (var i = start; i <= end; i++) {
                                boxes[i].checked = box.checked;
                            }
                            refresh();
                        }
                        lastIndex = index;
                    });
                });

                refresh();

                var modalEl = document.getElementById('photoModal');
                var photoForm = document.getElementById('photoForm');
                var photoName = document.getElementById('photoName');
                var photoPreview = document.getElementById('photoPreview');
                var photoPlaceholder = document.getElementById('photoPlaceholder');

                document.querySelectorAll('[data-photo-target]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (photoForm) {
                            photoForm.action = btn.dataset.photoTarget;
                        }
                        if (photoName) {
                            photoName.textContent = btn.dataset.photoName || '';
                        }
                        if (photoPreview && photoPlaceholder) {
                            if (btn.dataset.photoSrc) {
                                photoPreview.src = btn.dataset.photoSrc;
                                photoPreview.classList.remove('hidden');
                                photoPlaceholder.classList.add('hidden');
                            } else {
                                photoPreview.removeAttribute('src');
                                photoPreview.classList.add('hidden');
                                photoPlaceholder.classList.remove('hidden');
                            }
                        }
                        if (modalEl && window.bootstrap) {
                            bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        }
                    });
                });

                @if($errors->any() && $type === 'employee')
                    if (modalEl && window.bootstrap) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                @endif
            })();
        </script>
    @endpush
</x-app-layout>
