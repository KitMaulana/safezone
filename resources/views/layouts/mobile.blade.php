<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-full bg-slate-50">
    @php
        $user = auth()->user();
        $menu = \App\Support\Navigation::forRole($user?->role);
    @endphp

    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
        <div class="flex items-center gap-3 px-4 py-3">
            <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="h-9 w-9 shrink-0 object-contain">
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-semibold leading-tight text-slate-900">@yield('header', $header ?? 'School Safe Zone')</h1>
                <p class="truncate text-xs text-slate-500">{{ $user?->name }} &middot; {{ $user?->role?->label() }}</p>
            </div>

            @auth
                @livewire('shared.notification-bell')
            @endauth

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg p-2 text-slate-400 hover:bg-danger-50 hover:text-danger-600" aria-label="Keluar">
                    <x-icon.logout class="h-5 w-5" />
                </button>
            </form>
        </div>
    </header>

    <main class="px-4 pb-24 pt-4">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @if (count($menu))
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)]">
            <div class="grid" style="grid-template-columns: repeat({{ count($menu) }}, minmax(0, 1fr));">
                @foreach ($menu as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" class="flex min-h-touch flex-col items-center justify-center gap-1 px-1 py-2 text-[11px] font-medium {{ $active ? 'text-brand-600' : 'text-slate-500' }}">
                        <x-dynamic-component :component="'icon.' . $item['icon']" class="h-6 w-6" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    @endif

    @include("layouts.partials.install-banner")
    <x-ui.toast />
    @livewireScripts
    @stack('scripts')
</body>
</html>
