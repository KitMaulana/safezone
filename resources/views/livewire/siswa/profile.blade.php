<div class="space-y-4">
    @section('header', 'Profil')

    {{-- Foto --}}
    <x-ui.card title="Foto">
        <div class="flex flex-wrap items-center gap-4">
            @if ($student->photo_path)
                <img src="{{ route('media', ['path' => $student->photo_path]) }}" alt="Foto saya" class="h-24 w-24 rounded-xl object-cover">
            @else
                <x-ui.avatar :name="$student->name" size="xl" class="rounded-xl" />
            @endif

            <div class="min-w-0 flex-1 space-y-2">
                <input type="file" wire:model="photo" accept="image/*"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700">
                @error('photo') <p class="text-xs font-medium text-danger-600">{{ $message }}</p> @enderror
                @if ($photo)
                    <x-ui.button wire:click="savePhoto" size="sm">Simpan foto</x-ui.button>
                @endif
            </div>
        </div>
    </x-ui.card>

    {{-- Data diri --}}
    <x-ui.card title="Data diri">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Nama</dt>
                <dd class="font-medium text-slate-800">{{ $student->name }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">NISN</dt>
                <dd class="font-mono font-medium text-slate-800">{{ $student->nisn }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Kelas</dt>
                <dd class="font-medium text-slate-800">{{ $student->class_room }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Alamat</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->address ?: '—' }}</dd>
            </div>
        </dl>

        <form wire:submit="savePhone" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
            <x-ui.input wire:model="phone" name="phone" label="No. HP saya" hint="Dapat diubah langsung." />
            <x-ui.button type="submit" size="sm">Simpan nomor HP</x-ui.button>
        </form>
    </x-ui.card>

    {{-- Pengajuan perubahan --}}
    <x-ui.card title="Ajukan perubahan data" subtitle="Alamat dan data orang tua perlu persetujuan admin.">
        <form wire:submit="submitRequest" class="space-y-3">
            <x-ui.select wire:model="requestField" name="requestField" label="Data yang diajukan">
                <option value="address">Alamat siswa</option>
                <option value="parent_phone">Nomor HP orang tua</option>
                <option value="parent_name">Nama orang tua</option>
            </x-ui.select>

            <x-ui.input wire:model="requestValue" name="requestValue" label="Nilai baru" required />

            <x-ui.button type="submit" size="sm">Kirim pengajuan</x-ui.button>
        </form>

        @if ($requests->count())
            <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengajuan saya</p>
                @foreach ($requests as $request)
                    <div class="flex items-start justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-800">{{ $request->fieldLabel() }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $request->new_value }}</p>
                            @if ($request->note)
                                <p class="text-xs text-slate-400">Catatan: {{ $request->note }}</p>
                            @endif
                        </div>
                        <x-ui.badge :color="match($request->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' }">
                            {{ match($request->status) { 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Menunggu' } }}
                        </x-ui.badge>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    {{-- Ganti kata sandi --}}
    <x-ui.card title="Ganti kata sandi">
        <form wire:submit="changePassword" class="space-y-3">
            <x-ui.input wire:model="current_password" name="current_password" type="password" label="Kata sandi saat ini" required />
            <x-ui.input wire:model="password" name="password" type="password" label="Kata sandi baru" required />
            <x-ui.input wire:model="password_confirmation" name="password_confirmation" type="password" label="Ulangi kata sandi baru" required />
            <x-ui.button type="submit" size="sm">Simpan kata sandi</x-ui.button>
        </form>
    </x-ui.card>
</div>
