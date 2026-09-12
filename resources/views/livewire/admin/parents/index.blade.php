<div class="space-y-4">
    @section('header', 'Orang Tua / Wali')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $parents->total() }} orang tua terdaftar</p>
        <x-ui.button wire:click="create">
            <x-icon.plus class="h-5 w-5" /> Tambah Orang Tua
        </x-ui.button>
    </div>

    <x-ui.card padding="p-4">
        <x-ui.input wire:model.live.debounce.400ms="search" label="Cari" placeholder="Nama atau nomor HP…" />
    </x-ui.card>

    @if ($parents->count())
        <x-ui.table :headers="['Nama', 'Hubungan', 'No. HP', 'Anak', 'Aksi']">
            @foreach ($parents as $parent)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $parent->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $parent->relationship_label }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <a href="tel:{{ $parent->phone }}" class="font-mono text-xs text-brand-600 hover:underline">{{ $parent->phone }}</a>
                        @if ($parent->phone_alt)
                            <p class="font-mono text-xs text-slate-400">{{ $parent->phone_alt }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @forelse ($parent->students as $child)
                            <span class="mr-1 inline-block rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">
                                {{ $child->name }}@if ($child->pivot->is_primary) ★ @endif
                            </span>
                        @empty
                            <span class="text-xs text-slate-400">—</span>
                        @endforelse
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="edit({{ $parent->id }})" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Ubah">
                                <x-icon.pencil class="h-4 w-4" />
                            </button>
                            <button type="button" wire:click="delete({{ $parent->id }})" wire:confirm="Hapus data {{ $parent->name }}?" class="rounded p-1.5 text-slate-400 hover:bg-danger-50 hover:text-danger-600" title="Hapus">
                                <x-icon.trash class="h-4 w-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $parents->links() }}</div>
    @else
        <x-ui.empty-state icon="user" title="Belum ada orang tua" description="Orang tua dibuat otomatis saat impor siswa, atau tambahkan manual di sini." />
    @endif

    <x-ui.modal name="ortu" :title="$editingId ? 'Ubah Orang Tua' : 'Tambah Orang Tua'" maxWidth="max-w-2xl">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-ui.input wire:model="name" name="name" label="Nama lengkap" required />
                </div>
                <x-ui.input wire:model="phone" name="phone" label="No. HP" required
                            hint="Dipakai sebagai username. Kata sandi awal = 6 digit terakhir." />
                <x-ui.input wire:model="phone_alt" name="phone_alt" label="No. HP alternatif" />
                <x-ui.select wire:model="relationship" name="relationship" label="Hubungan" required>
                    <option value="ayah">Ayah</option>
                    <option value="ibu">Ibu</option>
                    <option value="wali">Wali</option>
                </x-ui.select>
                <div class="sm:col-span-2">
                    <x-ui.textarea wire:model="address" name="address" label="Alamat" rows="2" />
                </div>
            </div>

            <div>
                <p class="mb-1.5 text-sm font-medium text-slate-700">Anak yang ditautkan</p>
                <div class="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-2">
                    @foreach ($allStudents as $child)
                        <label class="flex items-center gap-3 rounded px-2 py-1.5 text-sm hover:bg-slate-50">
                            <input type="checkbox" wire:model.live="studentIds" value="{{ $child->id }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span class="flex-1">{{ $child->name }} <span class="text-slate-400">— {{ $child->class_room }}</span></span>
                            @if (in_array($child->id, $studentIds))
                                <label class="flex items-center gap-1 text-xs text-slate-500">
                                    <input type="radio" wire:model.live="primaryStudentId" value="{{ $child->id }}" name="primary" class="border-slate-300 text-brand-600 focus:ring-brand-500">
                                    kontak utama
                                </label>
                            @endif
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-slate-500">Kontak utama akan tercetak di stiker sebagai nomor darurat.</p>
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
