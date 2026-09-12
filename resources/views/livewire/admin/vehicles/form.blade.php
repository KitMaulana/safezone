<div class="max-w-3xl space-y-4">
    @section('header', $vehicle?->exists ? 'Ubah Kendaraan' : 'Tambah Kendaraan')

    <a href="{{ route('admin.vehicles.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
        <x-icon.arrow-left class="h-4 w-4" /> Kembali ke daftar kendaraan
    </a>

    <form wire:submit="save">
        <x-ui.card title="Data kendaraan">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-ui.select wire:model="student_id" name="student_id" label="Pemilik (siswa)" required>
                        <option value="">— Pilih siswa —</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->class_room }} ({{ $s->nisn }})</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.input wire:model.blur="plate_number" name="plate_number" label="Nomor polisi" required
                            placeholder="A 1234 XY" class="ssz-plate uppercase"
                            hint="Disimpan tanpa spasi, contoh A1234XY." />
                <x-ui.input wire:model="brand" name="brand" label="Merek" placeholder="Honda" />
                <x-ui.input wire:model="model" name="model" label="Tipe" placeholder="Beat" />
                <x-ui.input wire:model="color" name="color" label="Warna" placeholder="Hitam" />
                <x-ui.input wire:model="year" name="year" type="number" label="Tahun" placeholder="2019" />
                <x-ui.input wire:model="stnk_owner_name" name="stnk_owner_name" label="Nama pemilik di STNK" />

                <x-ui.select wire:model="sim_type" name="sim_type" label="Jenis SIM">
                    <option value="tidak_ada">Tidak ada</option>
                    <option value="C">SIM C</option>
                    <option value="C1">SIM C1</option>
                </x-ui.select>
                <x-ui.input wire:model="sim_number" name="sim_number" label="Nomor SIM" />

                <div class="sm:col-span-2">
                    <x-ui.textarea wire:model="notes" name="notes" label="Catatan" rows="2" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Kelengkapan syarat" class="mt-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($requirementLabels as $key => $label)
                    <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        <input type="checkbox" wire:model="requirements.{{ $key }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card title="Foto STNK (opsional)" class="mt-4">
            <div class="flex flex-wrap items-center gap-4">
                @if ($stnk_photo)
                    <img src="{{ $stnk_photo->temporaryUrl() }}" alt="Pratinjau STNK" class="h-24 w-32 rounded-lg object-cover">
                @elseif ($vehicle?->stnk_photo_path)
                    <img src="{{ route('media', ['path' => $vehicle->stnk_photo_path]) }}" alt="Foto STNK" class="h-24 w-32 rounded-lg object-cover">
                @endif

                <div class="min-w-0 flex-1">
                    <input type="file" wire:model="stnk_photo" accept="image/*"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    <p class="mt-1 text-xs text-slate-500">Maksimal 2 MB.</p>
                    @error('stnk_photo') <p class="mt-1 text-xs font-medium text-danger-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-ui.card>

        <div class="mt-4 flex flex-wrap gap-2">
            <x-ui.button type="submit" size="lg">Simpan</x-ui.button>
            <x-ui.button variant="secondary" size="lg" :href="route('admin.vehicles.index')">Batal</x-ui.button>
        </div>
    </form>
</div>
