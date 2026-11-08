<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Projects & Schemes') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Manage every project under your institute.') }}</p>
            </div>
            <a href="{{ route('projects.create') }}" class="btn-solid-indigo" aria-label="{{ __('New Project') }}">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">{{ __('New Project') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @if($projects->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <i class="bi bi-folder2-open text-2xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-800">{{ __('No projects yet') }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                        {{ __('Create your first project to organise courses, batches, admissions and students.') }}
                    </p>
                    <a href="{{ route('projects.create') }}" class="btn-solid-indigo mt-5">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('Create New Project') }}</span>
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($projects as $p)
                        <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-slate-800">{{ $p->name }}</h3>
                                    @if($p->code)
                                        <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">{{ $p->code }}</span>
                                    @endif
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $p->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </div>

                            <div class="grid flex-1 grid-cols-3 divide-x divide-slate-100 text-center">
                                <div class="p-3">
                                    <div class="text-lg font-bold text-slate-800">{{ $p->courses()->count() }}</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Courses') }}</div>
                                </div>
                                <div class="p-3">
                                    <div class="text-lg font-bold text-slate-800">{{ $p->batches()->count() }}</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Batches') }}</div>
                                </div>
                                <div class="p-3">
                                    <div class="text-lg font-bold text-slate-800">{{ $p->students()->count() }}</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Students') }}</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/60 p-4">
                                <a href="{{ route('projects.dashboard', $p) }}" class="btn-outline-indigo">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>{{ __('Dashboard') }}</span>
                                </a>
                                <a href="{{ route('projects.edit', $p) }}" class="btn-outline-gray">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>{{ __('Edit') }}</span>
                                </a>
                                <form method="POST" action="{{ route('projects.destroy', $p) }}" class="ms-auto" onsubmit="return confirm('Delete this project?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline-rose">
                                        <i class="bi bi-trash3"></i>
                                        <span>{{ __('Delete') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
