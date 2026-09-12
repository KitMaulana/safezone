<div class="max-w-5xl space-y-4">
    @section('header', 'Impor Siswa')

    <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
        <x-icon.arrow-left class="h-4 w-4" /> Kembali ke daftar siswa
    </a>

    <x-ui.card title="1. Siapkan berkas" subtitle="Format CSV atau XLSX dengan baris pertama sebagai judul kolom.">
        <x-slot:actions>
            <x-ui.button size="sm" variant="secondary" wire:click="downloadTemplate">
                <x-icon.download class="h-4 w-4" /> Unduh template
            </x-ui.button>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="bg-slate-50">
                        @foreach ($columns as $col)
                            <th class="whitespace-nowrap px-3 py-2 text-left font-mono font-semibold text-slate-600">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            <strong>nisn</strong>, <strong>nama</strong>, <strong>jk</strong> (L/P), dan <strong>kelas</strong> wajib diisi.
            <strong>tgl_lahir</strong> memakai format YYYY-MM-DD dan menjadi kata sandi awal siswa (ddmmyyyy).
            Bila <strong>hp_ortu</strong> diisi, akun orang tua dibuat otomatis.
        </p>
    </x-ui.card>

    <x-ui.card title="2. Unggah berkas">
        <input type="file" wire:model="file" accept=".csv,.xlsx,.xls,text/csv"
               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
        <div wire:loading wire:target="file" class="mt-2 text-sm text-brand-600">Membaca berkas…</div>
        @error('file') <p class="mt-1 text-xs font-medium text-danger-600">{{ $message }}</p> @enderror
    </x-ui.card>

    @if ($summary)
        <x-ui.alert :type="$summary['skipped'] > 0 ? 'warning' : 'success'" title="Hasil impor">
            {{ $summary['created'] }} siswa baru, {{ $summary['updated'] }} diperbarui, {{ $summary['skipped'] }} dilewati.
            @if ($summary['errors'])
                <ul class="mt-2 list-disc space-y-0.5 pl-5 text-xs">
                    @foreach (array_slice($summary['errors'], 0, 10) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </x-ui.alert>
    @endif

    @if (count($rows))
        <x-ui.card :title="'3. Pratinjau (' . count($rows) . ' baris, ' . $validCount . ' valid)'" padding="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-500">#</th>
                            @foreach ($columns as $col)
                                <th class="whitespace-nowrap px-3 py-2 text-left font-semibold text-slate-500">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($preview as $index => $row)
                            <tr class="{{ isset($rowErrors[$index]) ? 'bg-danger-50' : '' }}">
                                <td class="px-3 py-2 text-slate-400">{{ $index + 2 }}</td>
                                @foreach ($columns as $col)
                                    <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $row[$col] ?? '—' }}</td>
                                @endforeach
                            </tr>
                            @if (isset($rowErrors[$index]))
                                <tr class="bg-danger-50">
                                    <td></td>
                                    <td colspan="{{ count($columns) }}" class="px-3 pb-2 text-xs text-danger-600">
                                        {{ implode(' ', $rowErrors[$index]) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ count($preview) }} dari {{ count($rows) }} baris.
                    Baris bermasalah akan dilewati saat impor.
                </p>
                <x-ui.button wire:click="import" wire:loading.attr="disabled" :disabled="$validCount === 0">
                    <x-icon.upload class="h-5 w-5" /> Impor {{ $validCount }} baris
                </x-ui.button>
            </div>
        </x-ui.card>
    @endif
</div>
