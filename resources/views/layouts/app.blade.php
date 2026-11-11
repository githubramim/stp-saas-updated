<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Bootstrap is still used by a few legacy screens. Keep it before Tailwind so the app shell wins. -->
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
              integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
              crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- DataTables + flatpickr (list tables and date fields) -->
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/monthSelect/style.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="h-full font-sans antialiased bg-slate-100 text-slate-800">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            @include('layouts.sidebar')

            <div class="flex min-h-screen flex-col lg:pl-64">
                <header class="sticky top-0 z-30 flex min-h-[4rem] shrink-0 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 py-2 backdrop-blur sm:px-6">
                    <button type="button"
                            @click="sidebarOpen = true"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 lg:hidden"
                            aria-label="{{ __('Open navigation') }}">
                        <i class="bi bi-list text-2xl"></i>
                    </button>

                    <div class="min-w-0 flex-1">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h1 class="truncate text-lg font-semibold text-slate-800">
                                {{ Auth::user()->institute?->name ?? config('app.name') }}
                            </h1>
                        @endisset
                    </div>

                    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                        <a href="{{ route('portal.index') }}"
                           target="_blank"
                           class="hidden items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 sm:inline-flex">
                            <i class="bi bi-globe2"></i>
                            <span>{{ __('Portal') }}</span>
                        </a>

                        <a href="{{ route('profile.edit') }}"
                           class="hidden items-center gap-3 rounded-xl border border-transparent px-1 py-1 text-left transition hover:border-slate-200 hover:bg-slate-50 sm:flex">
                            <span class="hidden text-right lg:block">
                                <span class="block max-w-[12rem] truncate text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</span>
                                <span class="block text-[11px] font-medium uppercase tracking-wide text-slate-400">
                                    {{ Auth::user()->institute?->code ?? __('Platform') }}
                                </span>
                            </span>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 text-sm font-bold text-white shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit"
                                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                                    title="{{ __('Log out') }}">
                                <i class="bi bi-box-arrow-right"></i>
                                <span class="hidden sm:inline">{{ __('Logout') }}</span>
                            </button>
                        </form>
                    </div>
                </header>

                <main id="app-main" class="flex-1 px-4 sm:px-0">
                    @hasSection('content')
                        <div class="py-4">
                            @yield('content')
                        </div>
                    @endif
                    {{ $slot ?? '' }}
                </main>

                <footer class="border-t border-slate-200 bg-white px-4 py-4 text-center text-xs text-slate-400 sm:px-6">
                    {{ config('app.name') }} &middot; {{ now()->format('Y') }}
                </footer>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
                integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
                crossorigin="anonymous"></script>

        <!-- DataTables needs jQuery; load it only for the admin shell. -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/monthSelect/index.js"></script>

        <script>
            (function () {
                function initDataTables() {
                    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
                        return;
                    }

                    var $ = window.jQuery;

                    $('table.datatable').each(function () {
                        if ($.fn.DataTable.isDataTable(this)) {
                            return;
                        }

                        var $table = $(this);
                        var paging = $table.data('dtPaging') !== false;
                        var searching = $table.data('dtSearching') !== false;
                        var info = $table.data('dtInfo') !== false && paging;
                        var pageLength = parseInt($table.data('dtPageLength'), 10) || 25;

                        $table.DataTable({
                            pageLength: pageLength,
                            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                            order: [],
                            autoWidth: false,
                            paging: paging,
                            searching: searching,
                            info: info,
                            language: {
                                search: '',
                                searchPlaceholder: '{{ __('Search…') }}',
                                lengthMenu: '{{ __('Show _MENU_ entries') }}',
                                info: '{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}',
                                infoEmpty: '{{ __('No entries') }}',
                                infoFiltered: '{{ __('(filtered from _MAX_ total entries)') }}',
                                zeroRecords: '{{ __('No matching records found') }}',
                                emptyTable: '{{ __('No records available') }}',
                                paginate: {
                                    previous: '&lsaquo;',
                                    next: '&rsaquo;'
                                }
                            }
                        });
                    });
                }

                function initDatePickers(root) {
                    if (!window.flatpickr) {
                        return;
                    }

                    var scope = root || document;
                    var monthPlugin = window.monthSelectPlugin
                        ? [new window.monthSelectPlugin({ shorthand: false, dateFormat: 'Y-m', altFormat: 'F Y' })]
                        : null;

                    scope.querySelectorAll('input[type="date"], input[data-datepicker]').forEach(function (input) {
                        if (input.dataset.pluginReady === '1' || input.disabled || input.dataset.noDatepicker !== undefined) {
                            return;
                        }

                        flatpickr(input, {
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                            disableMobile: true
                        });
                        input.dataset.pluginReady = '1';
                    });

                    scope.querySelectorAll('input[type="datetime-local"], input[data-datetimepicker]').forEach(function (input) {
                        if (input.dataset.pluginReady === '1' || input.disabled || input.dataset.noDatepicker !== undefined) {
                            return;
                        }

                        flatpickr(input, {
                            enableTime: true,
                            time_24hr: true,
                            dateFormat: 'Y-m-d\\TH:i',
                            allowInput: true,
                            disableMobile: true
                        });
                        input.dataset.pluginReady = '1';
                    });

                    if (monthPlugin) {
                        scope.querySelectorAll('input[type="month"], input[data-monthpicker]').forEach(function (input) {
                            if (input.dataset.pluginReady === '1' || input.disabled || input.dataset.noDatepicker !== undefined) {
                                return;
                            }

                            flatpickr(input, {
                                plugins: monthPlugin,
                                allowInput: true,
                                disableMobile: true
                            });
                            input.dataset.pluginReady = '1';
                        });
                    }
                }

                function initPlugins() {
                    initDataTables();
                    initDatePickers();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initPlugins);
                } else {
                    initPlugins();
                }

                // Bootstrap modals can be opened more than once; run the same
                // initialisation on the modal contents each time it is shown.
                document.addEventListener('shown.bs.modal', function (event) {
                    initDatePickers(event.target);
                });
            })();
        </script>

        <script>
            function confirmDelete(event) {
                if (!window.confirm('{{ __('Are you sure you want to delete this record? This action cannot be undone.') }}')) {
                    if (event) event.preventDefault();
                    return false;
                }
                return true;
            }
        </script>
        @stack('scripts')
    </body>
</html>
