<div class="space-y-4">
    @section('header', 'Profil')

    <div class="grid grid-cols-2 gap-2">
        <x-ui.stat label="Scan hari ini" :value="$scanToday" icon="qr-code" color="brand" />
        <x-ui.stat label="Total scan" :value="$scanTotal" icon="chart" color="success" />
    </div>

    <x-ui.card title="Data akun">
        <form wire:submit="saveProfile" class="space-y-3">
            <x-ui.input wire:model="name" name="name" label="Nama" required />
            <x-ui.input wire:model="phone" name="phone" label="No. HP" />
            <x-ui.button type="submit" size="sm">Simpan</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card title="Ganti kata sandi">
        <form wire:submit="changePassword" class="space-y-3">
            <x-ui.input wire:model="current_password" name="current_password" type="password" label="Kata sandi saat ini" required />
            <x-ui.input wire:model="password" name="password" type="password" label="Kata sandi baru" required />
            <x-ui.input wire:model="password_confirmation" name="password_confirmation" type="password" label="Ulangi kata sandi baru" required />
            <x-ui.button type="submit" size="sm">Simpan kata sandi</x-ui.button>
        </form>
    </x-ui.card>
</div>
