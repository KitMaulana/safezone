<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-full bg-slate-50">
    @php
        $menu = \App\Support\Navigation::admin();
        $mobileMenu = \App\Support\Navigation::adminMobile();
        $user = auth()->user();
    @endphp

    <div x-data="{ sidebar: false }" class="min-h-full">
        {{-- Sidebar desktop --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200 bg-white lg:flex">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="h-9 w-9 object-contain">
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold leading-tight text-slate-900">School Safe Zone</p>
                    <p class="truncate text-xs text-slate-500">SMAN 1 Ciruas</p>
                </div>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
                @foreach ($menu as $item)
                    @php $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                       @if ($active) aria-current="page" @endif>
                        <x-dynamic-component :component="'icon.' . $item['icon']" class="h-5 w-5 shrink-0" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-100 p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-danger-50 hover:text-danger-600">
                        <x-icon.logout class="h-5 w-5" />
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- Sidebar mobile (drawer) --}}
        <div x-show="sidebar" x-cloak class="fixed inset-0 z-50 lg:hidden">
            <div class="fixed inset-0 bg-slate-900/50" x-on:click="sidebar = false"></div>
            <aside x-show="sidebar" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" class="relative flex h-full w-72 flex-col bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="h-9 w-9 object-contain">
                        <p class="text-sm font-bold text-slate-900">School Safe Zone</p>
                    </div>
                    <button type="button" x-on:click="sidebar = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100" aria-label="Tutup menu">
                        <x-icon.x class="h-5 w-5" />
                    </button>
                </div>
                <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
                    @foreach ($menu as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            <x-dynamic-component :component="'icon.' . $item['icon']" class="h-5 w-5 shrink-0" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>
        </div>

        {{-- Konten --}}
        <div class="lg:pl-64">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
                    <button type="button" x-on:click="sidebar = true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                        <x-icon.menu class="h-6 w-6" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-base font-semibold text-slate-900 sm:text-lg">@yield('header', $header ?? 'Dashboard')</h1>
                    </div>

                    @auth
                        @livewire('shared.notification-bell')
                    @endauth

                    <div class="flex items-center gap-2">
                        <x-ui.avatar :name="$user?->name ?? ''" size="sm" />
                        <div class="hidden text-right sm:block">
                            <p class="text-xs font-semibold leading-tight text-slate-900">{{ $user?->name }}</p>
                            <p class="text-xs leading-tight text-slate-500">{{ $user?->role?->label() }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 pb-24 pt-5 sm:px-6 lg:pb-8">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

        {{-- Bottom nav mobile --}}
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden">
            <div class="grid grid-cols-5">
                @foreach ($mobileMenu as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" class="flex min-h-touch flex-col items-center justify-center gap-1 px-1 py-2 text-[11px] font-medium {{ $active ? 'text-brand-600' : 'text-slate-500' }}">
                        <x-dynamic-component :component="'icon.' . $item['icon']" class="h-6 w-6" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    </div>

    @include("layouts.partials.install-banner")
    <x-ui.toast />
    @livewireScripts
    @stack('scripts')
</body>
</html>
