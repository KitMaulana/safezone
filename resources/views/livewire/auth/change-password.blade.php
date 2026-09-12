<div>
    <div class="text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-600">
            <x-icon.lock class="h-7 w-7" />
        </span>
        <h1 class="mt-4 text-xl font-bold text-slate-900">Ganti Kata Sandi</h1>
        <p class="mt-1 text-sm text-slate-500">Buat kata sandi baru minimal 8 karakter.</p>
    </div>

    @if (session('info'))
        <x-ui.alert type="warning" class="mt-4">{{ session('info') }}</x-ui.alert>
    @endif

    <x-ui.card class="mt-6">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input wire:model="current_password" name="current_password" type="password" label="Kata sandi saat ini" autocomplete="current-password" required />
            <x-ui.input wire:model="password" name="password" type="password" label="Kata sandi baru" autocomplete="new-password" required />
            <x-ui.input wire:model="password_confirmation" name="password_confirmation" type="password" label="Ulangi kata sandi baru" autocomplete="new-password" required />

            <x-ui.button type="submit" size="lg" class="w-full">Simpan kata sandi</x-ui.button>
        </form>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-medium text-slate-500 hover:text-slate-700">Keluar</button>
    </form>
</div>
