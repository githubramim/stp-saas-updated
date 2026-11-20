@if ($paginator->hasPages())
    @php
        $area = request()->route()->getName();
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max(1, $current - 2);
        $end = min($last, $current + 2);
        // Re-send the current filters so changing the page does not drop them.
        $carried = collect(request()->all())
            ->except(['_token', '_method', 'page'])
            ->filter(fn ($v) => is_scalar($v) && $v !== '')
            ->map(fn ($v, $k) => $k.':'.rawurlencode((string) $v))
            ->implode('/');
        $link = fn (int $page) => route('ui.set', [$area, trim($carried.'/page:'.$page, '/')]);
    @endphp
    <nav class="flex flex-wrap items-center justify-between gap-2 py-3" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">{{ __('Previous') }}</span>
        @else
            <a href="{{ $link($current - 1) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('Previous') }}</a>
        @endif

        <div class="flex flex-wrap items-center gap-1">
            @if ($start > 1)
                <a href="{{ $link(1) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-500">1</a>
                @if ($start > 2)<span class="px-1 text-xs text-slate-400">…</span>@endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page === $current)
                    <span class="rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-bold text-white">{{ $page }}</span>
                @else
                    <a href="{{ $link($page) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-500 hover:bg-slate-100">{{ $page }}</a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)<span class="px-1 text-xs text-slate-400">…</span>@endif
                <a href="{{ $link($last) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-500">{{ $last }}</a>
            @endif
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $link($current + 1) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('Next') }}</a>
        @else
            <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">{{ __('Next') }}</span>
        @endif
    </nav>
@endif
