<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">
                    <i class="bi bi-people me-1 text-indigo-500"></i>
                    {{ __('Trainee Application → Shortlist → Selection') }}
                </h2>
                <p class="text-xs text-slate-400">{{ __('Move applicants through the funded-project pipeline and keep student records in sync.') }}</p>
            </div>
            <span class="badge bg-indigo-100 text-indigo-700 rounded-pill px-3 py-2">{{ $students->count() }} {{ __('trainees') }}</span>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @include('pbtm.partials.nav')

            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    @if($project)
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                    @endif
                    <div class="min-w-[220px] flex-1">
                        <label for="trainee_search" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Search') }}</label>
                        <input id="trainee_search" type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-xl" placeholder="{{ __('Name, contact or reference no…') }}">
                    </div>
                    <div>
                        <label for="trainee_status" class="form-label text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Status') }}</label>
                        <select id="trainee_status" name="status" class="form-select form-select-sm rounded-xl">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm rounded-xl font-bold">
                        <i class="bi bi-funnel me-1"></i> {{ __('Filter') }}
                    </button>
                    <a href="{{ route('pbtm.trainees', array_filter(['project_id' => $project?->id])) }}" class="btn btn-light btn-sm rounded-xl border">{{ __('Reset') }}</a>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                @if($students->isEmpty())
                    <div class="p-10 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <i class="bi bi-person-plus text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('No trainees found') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('New public applications will appear here automatically. Adjust the filters to see more records.') }}</p>
                        <a href="{{ route('apply') }}" class="btn btn-outline-primary rounded-3 mt-4" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('Open Public Application Form') }}
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small datatable" style="min-width: 900px;" data-dt-page-length="25">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Trainee') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Course / Batch') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th class="text-center">{{ __('Current Stage') }}</th>
                                    <th class="text-center" style="min-width: 180px;">{{ __('Move To') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    @php
                                        $badge = match ($student->status) {
                                            'shortlisted' => 'bg-info text-dark',
                                            'selected' => 'bg-primary',
                                            'admitted', 'active' => 'bg-success',
                                            'completed' => 'bg-dark',
                                            'rejected', 'dropped_out' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="font-bold text-slate-800">{{ $student->name_en }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $student->name_bn }}</div>
                                        </td>
                                        <td class="font-monospace text-[11px] text-slate-500">{{ $student->reference_no }}</td>
                                        <td class="text-slate-600">{{ $student->project?->name ?? '—' }}</td>
                                        <td>
                                            <div class="text-slate-700">{{ $student->course?->name ?? '—' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $student->batch?->name ?? __('No batch') }}</div>
                                            @if($student->course?->tam_occupation)
                                                <div class="text-[11px] font-semibold text-indigo-500">
                                                    {{ $student->course->tam_occupation }}
                                                    @if($student->ntvqf_level || $student->course->ntvqf_level)
                                                        · {{ __('Level') }} {{ $student->ntvqf_level ?: $student->course->ntvqf_level }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-slate-600">{{ $student->contact }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $badge }}">{{ $statuses[$student->status] ?? ucfirst($student->status) }}</span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('pbtm.trainees.status', $student) }}" class="d-flex gap-1">
                                                @csrf
                                                <select name="status" class="form-select form-select-sm rounded-lg" onchange="this.form.submit()">
                                                    @foreach($statuses as $value => $label)
                                                        <option value="{{ $value }}" {{ $student->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-semibold text-slate-500">
                <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ __('Applied') }}</span>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-cyan-700">{{ __('Shortlisted') }}</span>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">{{ __('Selected') }}</span>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">{{ __('Admitted / Active') }}</span>
                <span class="rounded-full bg-teal-50 px-3 py-1 text-teal-700">{{ __('Completed') }}</span>
                <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-700">{{ __('Rejected / Dropped Out') }}</span>
            </div>
        </div>
    </div>
</x-app-layout>
