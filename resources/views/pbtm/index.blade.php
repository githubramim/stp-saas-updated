<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-lg font-semibold leading-tight text-slate-800">
                    <i class="bi bi-diagram-3 me-1 text-indigo-500"></i>
                    {{ __('Project-Based Training Management') }}
                </h2>
                <p class="text-xs text-slate-400">{{ __('Donor, budget, MoU, trainee pipeline, stipend, asset and placement compliance in one place.') }}</p>
            </div>
            @if($project)
                <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary rounded-3 font-semibold">
                    <i class="bi bi-pencil-square me-1"></i> {{ __('Edit Project Profile') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @include('pbtm.partials.nav')

            @unless($project)
                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ __('Showing combined data for all projects. Select a specific project above to manage its trainees, stipends, assets and placements.') }}
                </div>
            @endunless

            <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Trainees') }}</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><i class="bi bi-people"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($stats['trainees']) }}</div>
                    <div class="text-[11px] text-slate-400">{{ $stats['applied'] }} applied · {{ $stats['shortlisted'] }} shortlisted</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Selected / Admitted') }}</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><i class="bi bi-person-check"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($stats['selected']) }}</div>
                    <div class="text-[11px] text-slate-400">{{ $stats['completed'] }} completed</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Stipend Paid') }}</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600"><i class="bi bi-cash-stack"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-teal-700">৳{{ number_format($stats['stipend_paid'], 0) }}</div>
                    <div class="text-[11px] text-slate-400">৳{{ number_format($stats['stipend_pending'], 0) }} pending</div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ __('Job Placement') }}</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><i class="bi bi-briefcase"></i></span>
                    </div>
                    <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($stats['placements']) }}</div>
                    <div class="text-[11px] text-slate-400">{{ $stats['placement_rate'] }}% of completed trainees</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    @if($project)
                        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-indigo-900 px-5 py-4 text-white">
                                <div>
                                    <h3 class="text-base font-bold">{{ $project->name }}</h3>
                                    <p class="text-xs text-indigo-200">
                                        {{ $project->code ?: __('No project code') }}
                                        · {{ ucfirst($project->status) }}
                                        @if($project->donor_name)
                                            · {{ $project->donor_name }}
                                        @endif
                                    </p>
                                </div>
                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold">
                                    {{ __('Target') }}: {{ number_format($project->target_trainees ?? 0) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-px bg-slate-100 sm:grid-cols-3">
                                <div class="bg-white p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Donor') }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ $project->donor_name ?: '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $project->donor_type ? ucfirst($project->donor_type) : '' }}</div>
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Budget') }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">৳{{ number_format((float) $project->budget_amount, 0) }}</div>
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('MoU') }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ $project->mou_number ?: '—' }}</div>
                                    @if($project->mou_file_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($project->mou_file_path) }}" target="_blank" class="text-[11px] font-semibold text-indigo-600 hover:underline">
                                            <i class="bi bi-paperclip"></i> {{ __('View document') }}
                                        </a>
                                    @endif
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Duration') }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">
                                        {{ $project->start_date?->format('d M Y') ?? '—' }} – {{ $project->end_date?->format('d M Y') ?? '—' }}
                                    </div>
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Coordinator') }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ $project->coordinator_name ?: '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $project->coordinator_contact }}</div>
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('Assets Distributed') }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ number_format($stats['assets_out']) }} / {{ number_format($stats['assets_total']) }}</div>
                                </div>
                            </div>

                            @if($project->target_trainees)
                                @php
                                    $targetProgress = min(100, round(($stats['selected'] / max($project->target_trainees, 1)) * 100, 1));
                                @endphp
                                <div class="border-t border-slate-100 p-5">
                                    <div class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-500">
                                        <span>{{ __('Enrolment against target') }}</span>
                                        <span>{{ $targetProgress }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ $targetProgress }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <i class="bi bi-folder2-open text-xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('No project selected') }}</h3>
                            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                                {{ __('Use the project filter above to open a funded project profile, or create a project with donor, budget and MoU details.') }}
                            </p>
                            <a href="{{ route('projects.index', ['create' => 1]) }}" class="btn btn-primary rounded-3 mt-4">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Create Project') }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="space-y-5">
                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                        <h4 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-800">
                            <i class="bi bi-cash-coin text-teal-500"></i> {{ __('Recent Stipends') }}
                        </h4>
                        @forelse($recentStipends as $stipend)
                            <div class="flex items-center justify-between border-b border-slate-100 py-2 last:border-0">
                                <div class="min-w-0">
                                    <div class="truncate text-xs font-semibold text-slate-700">{{ $stipend->student?->name_en ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ ucfirst($stipend->type) }} · {{ $stipend->periodLabel() }}</div>
                                </div>
                                <span class="badge {{ $stipend->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">৳{{ number_format($stipend->amount, 0) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">{{ __('No stipend records yet.') }}</p>
                        @endforelse
                        <a href="{{ route('pbtm.stipends', array_filter(['project_id' => $project?->id])) }}" class="mt-3 inline-block text-xs font-bold text-indigo-600 hover:underline">
                            {{ __('Manage stipends') }} →
                        </a>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                        <h4 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-800">
                            <i class="bi bi-briefcase text-amber-500"></i> {{ __('Recent Placements') }}
                        </h4>
                        @forelse($recentPlacements as $placement)
                            <div class="flex items-center justify-between border-b border-slate-100 py-2 last:border-0">
                                <div class="min-w-0">
                                    <div class="truncate text-xs font-semibold text-slate-700">{{ $placement->student?->name_en ?? '—' }}</div>
                                    <div class="truncate text-[11px] text-slate-400">{{ $placement->employer_name }} · {{ $placement->job_title }}</div>
                                </div>
                                <span class="badge {{ $placement->status === 'dropped' ? 'bg-danger' : 'bg-success' }}">{{ ucfirst($placement->status) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">{{ __('No job placements recorded yet.') }}</p>
                        @endforelse
                        <a href="{{ route('pbtm.placements', array_filter(['project_id' => $project?->id])) }}" class="mt-3 inline-block text-xs font-bold text-indigo-600 hover:underline">
                            {{ __('Manage placements') }} →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
