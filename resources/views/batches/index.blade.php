<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold leading-tight text-slate-800">{{ __('Batches') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Class batches, shifts and seat capacity.') }}</p>
            </div>
            <a href="{{ route('batches.create') }}" class="btn-solid-indigo" aria-label="{{ __('Add Batch') }}">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">{{ __('Add Batch') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
            @if($batches->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                        <i class="bi bi-people text-2xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-800">{{ __('No batches yet') }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ __('Create a batch under a course to begin admitting students.') }}</p>
                    <a href="{{ route('batches.create') }}" class="btn-solid-indigo mt-5">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('Add Batch') }}</span>
                    </a>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[860px] text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/80 text-left text-[11px] uppercase tracking-wide text-slate-400">
                                    <th class="px-5 py-3 font-semibold">{{ __('Batch') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Course') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Shift') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Duration') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Seats') }}</th>
                                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-right font-semibold">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($batches as $b)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $b->name }}</td>
                                        <td class="px-5 py-4 text-slate-500">{{ $b->course?->name ?? '—' }}</td>
                                        <td class="px-5 py-4 text-slate-500">{{ $b->shift ?? '—' }}</td>
                                        <td class="px-5 py-4 text-xs text-slate-500">
                                            {{ $b->start_date?->format('d M Y') }}
                                            @if($b->end_date)
                                                <span class="text-slate-300">→</span> {{ $b->end_date->format('d M Y') }}
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-slate-500">{{ $b->seat_capacity }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $b->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                                {{ ucfirst($b->status) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('batches.edit', $b) }}" class="btn-outline-gray">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span>{{ __('Edit') }}</span>
                                                </a>
                                                <form method="POST" action="{{ route('batches.destroy', $b) }}" onsubmit="return confirm('Delete this batch?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-outline-rose">
                                                        <i class="bi bi-trash3"></i>
                                                        <span>{{ __('Delete') }}</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
