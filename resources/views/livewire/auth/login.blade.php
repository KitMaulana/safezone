<div>
    <div class="text-center">
        <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="mx-auto h-20 w-20 object-contain">
        <h1 class="mt-4 text-xl font-bold text-slate-900">Masuk</h1>
        <p class="mt-1 text-sm text-slate-500">School Safe Zone SMA Negeri 1 Ciruas</p>
    </div>

    <x-ui.card class="mt-6">
        <form wire:submit="login" class="space-y-4">
            <x-ui.input
                wire:model="username"
                name="username"
                label="Username / NISN / No. HP"
                autocomplete="username"
                autofocus
                required
                hint="Siswa memakai NISN, orang tua memakai nomor HP."
            />

            <x-ui.input
                wire:model="password"
                name="password"
                type="password"
                label="Kata sandi"
                autocomplete="current-password"
                required
            />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Ingat saya di perangkat ini
            </label>

            <x-ui.button type="submit" size="lg" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login">Memproses…</span>
            </x-ui.button>

            <p class="text-center text-sm">
                <a href="{{ route('password.request') }}" class="font-medium text-brand-600 hover:text-brand-700">Lupa kata sandi?</a>
            </p>
        </form>
    </x-ui.card>

    <p class="mt-4 text-center text-xs text-slate-500">
        Login pertama kali akan diminta mengganti kata sandi.
    </p>
</div>
