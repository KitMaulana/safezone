<div>
    <div class="text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-600">
            <x-icon.lock class="h-7 w-7" />
        </span>
        <h1 class="mt-4 text-xl font-bold text-slate-900">Atur Ulang Kata Sandi</h1>
    </div>

    <x-ui.card class="mt-6">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input wire:model="email" name="email" type="email" label="Email" required />
            <x-ui.input wire:model="password" name="password" type="password" label="Kata sandi baru" autocomplete="new-password" required />
            <x-ui.input wire:model="password_confirmation" name="password_confirmation" type="password" label="Ulangi kata sandi baru" autocomplete="new-password" required />

            <x-ui.button type="submit" size="lg" class="w-full">Simpan</x-ui.button>
        </form>
    </x-ui.card>
</div>
