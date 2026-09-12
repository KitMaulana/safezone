<div class="space-y-4">
    @section('header', 'Pelanggaran')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $violations->total() }} catatan pelanggaran</p>
        <x-ui.button wire:click="create">
            <x-icon.plus class="h-5 w-5" /> Catat Pelanggaran
        </x-ui.button>
    </div>

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-ui.input wire:model.live.debounce.400ms="search" label="Cari siswa" placeholder="Nama atau kelas…" />
            <x-ui.select wire:model.live="category" label="Kategori">
                <option value="">Semua kategori</option>
                @foreach ($categories as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    @if ($violations->count())
        <x-ui.table :headers="['Waktu', 'Siswa', 'Kategori', 'Keterangan', 'Poin', 'Pencatat', 'Aksi']">
            @foreach ($violations as $violation)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $violation->occurred_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.students.show', $violation->student_id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $violation->student->name }}</a>
                        <p class="text-xs text-slate-500">{{ $violation->student->class_room }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $violation->category->label() }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ Str::limit($violation->description, 60) ?: '—' }}</td>
                    <td class="px-4 py-3"><x-ui.badge color="warning">{{ $violation->points }}</x-ui.badge></td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $violation->reporter?->name ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="edit({{ $violation->id }})" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600">
                                <x-icon.pencil class="h-4 w-4" />
                            </button>
                            <button type="button" wire:click="delete({{ $violation->id }})" wire:confirm="Hapus catatan pelanggaran ini?" class="rounded p-1.5 text-slate-400 hover:bg-danger-50 hover:text-danger-600">
                                <x-icon.trash class="h-4 w-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $violations->links() }}</div>
    @else
        <x-ui.empty-state icon="flag" title="Belum ada pelanggaran" description="Catatan pelanggaran siswa akan muncul di sini." />
    @endif

    <x-ui.modal name="pelanggaran" :title="$editingId ? 'Ubah Pelanggaran' : 'Catat Pelanggaran'" maxWidth="max-w-2xl">
        <form wire:submit="save" class="space-y-4">
            <x-ui.select wire:model="studentId" name="studentId" label="Siswa" required>
                <option value="">— Pilih siswa —</option>
                @foreach ($students as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->class_room }}</option>
                @endforeach
            </x-ui.select>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.select wire:model.live="form_category" name="form_category" label="Kategori" required>
                    @foreach ($categories as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input wire:model="points" name="points" type="number" label="Poin" required />
            </div>

            <x-ui.input wire:model="occurred_at" name="occurred_at" type="datetime-local" label="Waktu kejadian" required />

            <x-ui.textarea wire:model="description" name="description" label="Keterangan" rows="3" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Foto bukti (opsional)</label>
                <input type="file" wire:model="evidence" accept="image/*"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700">
                @error('evidence') <p class="mt-1 text-xs font-medium text-danger-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
