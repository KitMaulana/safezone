<div class="max-w-4xl space-y-4">
    @section('header', 'Pengaturan')

    <form wire:submit="save" class="space-y-4">
        <x-ui.card title="Identitas sekolah">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-ui.input wire:model="form.school_name" name="form.school_name" label="Nama sekolah" required />
                </div>
                <div class="sm:col-span-2">
                    <x-ui.textarea wire:model="form.school_address" name="form.school_address" label="Alamat" rows="2" />
                </div>
                <x-ui.input wire:model="form.school_phone" name="form.school_phone" label="Telepon sekolah" />
                <x-ui.input wire:model="form.whatsapp_admin" name="form.whatsapp_admin" label="WhatsApp admin" />
                <x-ui.input wire:model="form.police_phone" name="form.police_phone" label="Nomor darurat Kepolisian RI"
                            hint="Tampil di stiker dan halaman QR. Kosongkan untuk menyembunyikan." />
                <div class="sm:col-span-2">
                    <x-ui.input wire:model="form.headmaster_name" name="form.headmaster_name" label="Nama kepala sekolah"
                                hint="Dicetak pada halaman belakang stiker." />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Jam & aturan">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-ui.input wire:model="form.checkin_open_time" name="form.checkin_open_time" type="time" label="Gerbang dibuka" required
                            hint="Scan sebelum jam ini ditolak lembut." />
                <x-ui.input wire:model="form.checkout_min_time" name="form.checkout_min_time" type="time" label="Cek out paling awal" required
                            hint="Cek out sebelum jam ini ditandai keluar lebih awal." />
                <x-ui.input wire:model="form.late_alert_time" name="form.late_alert_time" type="time" label="Pengingat belum cek in" required
                            hint="Waktu pengiriman notifikasi ke orang tua." />
                <x-ui.input wire:model="form.violation_block_threshold" name="form.violation_block_threshold" type="number" label="Ambang poin blokir" required />
            </div>

            <label class="mt-4 flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                <input type="checkbox" wire:model="form.auto_activate_on_print" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Aktifkan stiker otomatis setelah dicetak
            </label>
        </x-ui.card>

        <x-ui.card title="Privasi & stiker">
            <div class="space-y-2">
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                    <input type="checkbox" wire:model="form.public_show_emergency_phone" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Tampilkan nomor kontak darurat sebagai teks di halaman publik
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                    <input type="checkbox" wire:model="form.sticker_show_class" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Cetak kelas siswa pada stiker
                </label>
                <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm text-slate-700">
                    <input type="checkbox" wire:model="form.sticker_print_back" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Cetak halaman belakang stiker (tata tertib)
                </label>
            </div>

            <div class="mt-4">
                <x-ui.input wire:model="form.sticker_footer_text" name="form.sticker_footer_text" label="Teks kaki stiker" />
            </div>
        </x-ui.card>

        <x-ui.button type="submit" size="lg">Simpan pengaturan</x-ui.button>
    </form>

    {{-- Tahun ajaran --}}
    <x-ui.card title="Tahun ajaran">
        <x-slot:actions>
            <x-ui.button size="sm" wire:click="newYear"><x-icon.plus class="h-4 w-4" /> Tambah</x-ui.button>
        </x-slot:actions>

        @foreach ($years as $year)
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-50 py-3 last:border-0">
                <div>
                    <p class="text-sm font-medium text-slate-900">
                        {{ $year->name }}
                        @if ($year->is_active)
                            <x-ui.badge color="success" size="sm" class="ml-1">Aktif</x-ui.badge>
                        @endif
                    </p>
                    <p class="text-xs text-slate-500">Kode {{ $year->code }} &middot; {{ $year->start_date->format('d M Y') }} – {{ $year->end_date->format('d M Y') }}</p>
                </div>
                <div class="flex gap-1">
                    <x-ui.button size="sm" variant="secondary" wire:click="editYear({{ $year->id }})">Ubah</x-ui.button>
                    @if (! $year->is_active)
                        <x-ui.button size="sm" wire:click="activateYear({{ $year->id }})">Aktifkan</x-ui.button>
                    @endif
                </div>
            </div>
        @endforeach
    </x-ui.card>

    {{-- Gerbang --}}
    <x-ui.card title="Gerbang">
        <x-slot:actions>
            <x-ui.button size="sm" wire:click="newGate"><x-icon.plus class="h-4 w-4" /> Tambah</x-ui.button>
        </x-slot:actions>

        @foreach ($gates as $gate)
            <div class="flex items-center justify-between gap-3 border-b border-slate-50 py-3 last:border-0">
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ $gate->name }}</p>
                    <x-ui.badge :color="$gate->is_active ? 'success' : 'slate'" size="sm">{{ $gate->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                </div>
                <div class="flex gap-1">
                    <x-ui.button size="sm" variant="secondary" wire:click="editGate({{ $gate->id }})">Ubah</x-ui.button>
                    <x-ui.button size="sm" variant="ghost" wire:click="toggleGate({{ $gate->id }})">{{ $gate->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-ui.button>
                </div>
            </div>
        @endforeach
    </x-ui.card>

    {{-- Cadangan data --}}
    <x-ui.card title="Cadangan data" subtitle="Berkas cadangan disimpan di storage/app/backups dan dijadwalkan mingguan.">
        <x-ui.button variant="secondary" :href="route('admin.backup.download')">
            <x-icon.download class="h-5 w-5" /> Buat &amp; unduh cadangan sekarang
        </x-ui.button>
    </x-ui.card>

    <x-ui.modal name="tahun" title="Tahun ajaran">
        <form wire:submit="saveYear" class="space-y-4">
            <x-ui.input wire:model="yearName" name="yearName" label="Nama" placeholder="2026/2027" required />
            <x-ui.input wire:model="yearCode" name="yearCode" label="Kode" placeholder="2627" required
                        hint="Dipakai pada nomor stiker: SSZ-2627-0001." />
            <div class="grid grid-cols-2 gap-3">
                <x-ui.input wire:model="yearStart" name="yearStart" type="date" label="Mulai" required />
                <x-ui.input wire:model="yearEnd" name="yearEnd" type="date" label="Selesai" required
                            hint="Menjadi masa berlaku stiker." />
            </div>
            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal name="gerbang" title="Gerbang">
        <form wire:submit="saveGate" class="space-y-4">
            <x-ui.input wire:model="gateName" name="gateName" label="Nama gerbang" required />
            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
