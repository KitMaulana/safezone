<div class="max-w-3xl space-y-4">
    @section('header', $student?->exists ? 'Ubah Siswa' : 'Tambah Siswa')

    <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
        <x-icon.arrow-left class="h-4 w-4" /> Kembali ke daftar siswa
    </a>

    <form wire:submit="save">
        <x-ui.card title="Identitas siswa">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input wire:model="nisn" name="nisn" label="NISN" required hint="Dipakai sebagai username siswa." />
                <x-ui.input wire:model="nis" name="nis" label="NIS" />
                <div class="sm:col-span-2">
                    <x-ui.input wire:model="name" name="name" label="Nama lengkap" required />
                </div>
                <x-ui.select wire:model="gender" name="gender" label="Jenis kelamin" required>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </x-ui.select>
                <x-ui.input wire:model="birth_date" name="birth_date" type="date" label="Tanggal lahir" hint="Menjadi kata sandi awal (ddmmyyyy)." />
                <x-ui.input wire:model="class_room" name="class_room" label="Kelas" required placeholder="XII IPA 1" />
                <x-ui.input wire:model="phone" name="phone" label="No. HP siswa" placeholder="08xxxxxxxxxx" />
                <div class="sm:col-span-2">
                    <x-ui.textarea wire:model="address" name="address" label="Alamat" rows="2" />
                </div>
                <x-ui.select wire:model="status" name="status" label="Status" required>
                    @foreach ($statuses as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </x-ui.card>

        <x-ui.card title="Foto siswa" class="mt-4">
            <div class="flex flex-wrap items-center gap-4">
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="Pratinjau foto" class="h-24 w-24 rounded-lg object-cover">
                @elseif ($student?->photo_path)
                    <img src="{{ route('media', ['path' => $student->photo_path]) }}" alt="Foto {{ $student->name }}" class="h-24 w-24 rounded-lg object-cover">
                @else
                    <x-ui.avatar :name="$name" size="xl" class="rounded-lg" />
                @endif

                <div class="min-w-0 flex-1">
                    <input type="file" wire:model="photo" accept="image/*"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    <p class="mt-1 text-xs text-slate-500">JPG/PNG/WEBP, maksimal 2 MB. Foto otomatis diperkecil ke 600 px.</p>
                    <div wire:loading wire:target="photo" class="mt-1 text-xs text-brand-600">Mengunggah…</div>
                    @error('photo') <p class="mt-1 text-xs font-medium text-danger-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-ui.card>

        <div class="mt-4 flex flex-wrap gap-2">
            <x-ui.button type="submit" size="lg" wire:loading.attr="disabled">Simpan</x-ui.button>
            <x-ui.button variant="secondary" size="lg" :href="route('admin.students.index')">Batal</x-ui.button>
        </div>
    </form>
</div>
