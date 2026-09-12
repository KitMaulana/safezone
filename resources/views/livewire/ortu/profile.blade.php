<div class="space-y-4">
    @section('header', 'Profil')

    <x-ui.card title="Data saya">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Nama</dt>
                <dd class="font-medium text-slate-800">{{ $parent->name }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Hubungan</dt>
                <dd class="font-medium text-slate-800">{{ $parent->relationship_label }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">No. HP (username)</dt>
                <dd class="font-mono font-medium text-slate-800">{{ $parent->phone }}</dd>
            </div>
        </dl>

        <form wire:submit="saveAltPhone" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
            <x-ui.input wire:model="phone_alt" name="phone_alt" label="No. HP alternatif"
                        hint="Nomor cadangan yang bisa dihubungi sekolah." />
            <x-ui.button type="submit" size="sm">Simpan</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card title="Anak">
        @foreach ($children as $child)
            <div class="flex items-center justify-between gap-3 border-b border-slate-50 py-2 last:border-0">
                <p class="text-sm font-medium text-slate-800">{{ $child->name }}</p>
                <span class="text-xs text-slate-500">{{ $child->class_room }}</span>
            </div>
        @endforeach
    </x-ui.card>

    <x-ui.card title="Perangkat notifikasi">
        @forelse ($devices as $device)
            <div class="flex items-center justify-between gap-3 border-b border-slate-50 py-2 last:border-0">
                <p class="min-w-0 flex-1 truncate text-xs text-slate-600">{{ parse_url($device->endpoint, PHP_URL_HOST) }}</p>
                <button type="button" wire:click="removeDevice({{ $device->id }})" class="shrink-0 text-xs font-semibold text-danger-600 hover:underline">Hapus</button>
            </div>
        @empty
            <p class="text-sm text-slate-500">Belum ada perangkat yang berlangganan notifikasi.</p>
        @endforelse
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
