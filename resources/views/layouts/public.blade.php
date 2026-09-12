<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    @include('layouts.partials.head')
</head>
<body class="flex min-h-full flex-col bg-slate-50">
    <main class="flex flex-1 flex-col items-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-md">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white px-4 py-5">
        <div class="mx-auto flex max-w-md flex-col items-center gap-2 text-center">
            <div class="flex items-center gap-3">
                <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="h-8 w-8 object-contain">
                <span class="h-6 w-px bg-slate-200"></span>
                <img src="/images/logo-polres-serang.png" alt="Logo Satlantas Polres Serang" class="h-8 w-8 object-contain">
            </div>
            <p class="text-xs text-slate-500">Didukung oleh Satlantas Polres Serang</p>
            <p class="text-xs text-slate-400">
                &copy; {{ now()->year }} SMA Negeri 1 Ciruas &middot;
                <a href="{{ route('privasi') }}" class="underline hover:text-slate-600">Kebijakan Privasi</a>
            </p>
        </div>
    </footer>

    @include("layouts.partials.install-banner")
    <x-ui.toast />
    @livewireScripts
    @stack('scripts')
</body>
</html>
