<div class="space-y-4" wire:poll.20s>
    @section('header', 'Hari Ini')

    <div class="grid grid-cols-2 gap-2">
        <x-ui.stat label="Cek in" :value="$totalIn" icon="check-circle" color="success" />
        <x-ui.stat label="Di sekolah" :value="$atSchool" icon="home" color="brand" />
        <x-ui.stat label="Sudah pulang" :value="$goneHome" icon="logout" color="slate" />
        <x-ui.stat label="Ditolak" :value="$denied" icon="ban" color="danger" />
    </div>

    <x-ui.input wire:model.live.debounce.400ms="search" label="Cari" placeholder="Nama, kelas, atau plat…" />

    @forelse ($rows as $row)
        <x-ui.card padding="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ $row['student']->name }}</p>
                    <p class="text-xs text-slate-500">{{ $row['student']->class_room }} &middot; <span class="ssz-plate font-mono">{{ $row['plate'] }}</span></p>
                </div>
                <x-ui.badge :color="$row['status'] === 'Di sekolah' ? 'success' : 'slate'">{{ $row['status'] }}</x-ui.badge>
            </div>

            <div class="mt-3 flex items-center gap-4 border-t border-slate-100 pt-3 text-sm">
                <div>
                    <p class="text-xs text-slate-500">Masuk</p>
                    <p class="font-semibold text-slate-800">{{ $row['in']?->timezone('Asia/Jakarta')->format('H.i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Keluar</p>
                    <p class="font-semibold text-slate-800">
                        {{ $row['out']?->timezone('Asia/Jakarta')->format('H.i') ?? '—' }}
                        @if ($row['early'])
                            <span class="ml-1 text-xs font-medium text-warning-600">lebih awal</span>
                        @endif
                    </p>
                </div>
                <div class="ml-auto">
                    <button type="button" wire:click="openViolation({{ $row['student']->id }})"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Catat pelanggaran
                    </button>
                </div>
            </div>
        </x-ui.card>
    @empty
        <x-ui.empty-state icon="clock" title="Belum ada aktivitas" description="Catatan cek in dan cek out hari ini akan muncul di sini." />
    @endforelse

    <x-ui.modal name="pelanggaran" title="Catat pelanggaran">
        <form wire:submit="saveViolation" class="space-y-4">
            <x-ui.select wire:model="violationCategory" name="violationCategory" label="Kategori" required>
                @foreach ($lightCategories as $category)
                    <option value="{{ $category->value }}">{{ $category->label() }} ({{ $category->defaultPoints() }} poin)</option>
                @endforeach
            </x-ui.select>

            <x-ui.textarea wire:model="violationDescription" name="violationDescription" label="Keterangan" rows="3" required
                           placeholder="Jelaskan singkat kejadiannya." />

            <p class="text-xs text-slate-500">Petugas hanya dapat mencatat pelanggaran ringan. Kategori lain dicatat oleh admin.</p>

            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
