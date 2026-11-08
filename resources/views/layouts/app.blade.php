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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="h-full font-sans antialiased bg-slate-100 text-slate-800">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            @include('layouts.sidebar')

            <div class="flex min-h-screen flex-col lg:pl-64">
                <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6">
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

        @stack('scripts')
    </body>
</html>
