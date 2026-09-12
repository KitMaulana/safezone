<div class="space-y-4">
    @section('header', 'Laporan')

    @php
        $tabs = [
            'kehadiran' => 'Kehadiran',
            'kendaraan' => 'Kendaraan & Stiker',
            'pelanggaran' => 'Pelanggaran',
            'scan' => 'Scan Ditolak',
        ];
    @endphp

    <div class="ssz-scroll-hide -mx-1 overflow-x-auto">
        <div class="flex gap-1 px-1">
            @foreach ($tabs as $key => $label)
                <button type="button" wire:click="$set('tab', '{{ $key }}')"
                        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition {{ $tab === $key ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <x-ui.input wire:model.live="from" type="date" label="Dari" />
            <x-ui.input wire:model.live="to" type="date" label="Sampai" />
            <x-ui.select wire:model.live="classRoom" label="Kelas">
                <option value="">Semua kelas</option>
                @foreach ($classRooms as $room)
                    <option value="{{ $room }}">{{ $room }}</option>
                @endforeach
            </x-ui.select>
            <div class="flex items-end gap-2">
                <x-ui.button size="md" variant="secondary"
                             :href="route('admin.reports.attendance.export', ['from' => $from, 'to' => $to, 'classRoom' => $classRoom, 'format' => 'xlsx'])">
                    <x-icon.download class="h-4 w-4" /> XLSX
                </x-ui.button>
                <x-ui.button size="md" variant="secondary"
                             :href="route('admin.reports.attendance.export', ['from' => $from, 'to' => $to, 'classRoom' => $classRoom, 'format' => 'pdf'])">
                    <x-icon.printer class="h-4 w-4" /> PDF
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    @if ($tab === 'kehadiran')
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.stat label="Siswa hadir" :value="$summary['hadir']" icon="users" color="brand" />
            <x-ui.stat label="Total cek in" :value="$summary['totalIn']" icon="check-circle" color="success" />
            <x-ui.stat label="Rata-rata jam masuk" :value="$summary['avgIn']" icon="clock" color="warning" />
            <x-ui.stat label="Keluar lebih awal" :value="$summary['earlyLeave']" icon="logout" color="warning" />
            <x-ui.stat label="Ditolak" :value="$summary['denied']" icon="ban" color="danger" />
        </div>

        <x-ui.card title="Tren cek in harian">
            @php $maxTrend = max(1, $trend->max() ?? 1); @endphp
            @if ($trend->count())
                <div class="flex h-40 items-end gap-1 overflow-x-auto">
                    @foreach ($trend as $date => $count)
                        <div class="flex min-w-8 flex-1 flex-col items-center gap-1">
                            <span class="text-[10px] font-semibold text-slate-600">{{ $count }}</span>
                            <div class="flex w-full flex-1 items-end">
                                <div class="w-full rounded-t bg-brand-500" style="height: {{ max(4, round($count / $maxTrend * 100)) }}%"></div>
                            </div>
                            <span class="text-[9px] text-slate-400">{{ \Illuminate\Support\Carbon::parse($date)->format('d/m') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Tidak ada data pada rentang ini.</p>
            @endif
        </x-ui.card>

        <x-ui.card title="Rekap per kelas" padding="p-0">
            <x-ui.table :headers="['Kelas', 'Jumlah cek in', 'Siswa unik']">
                @foreach ($perClass as $room => $data)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $room }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $data['count'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $data['students'] }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    @endif

    @if ($tab === 'kendaraan')
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-ui.card title="Kendaraan per kelas" padding="p-0">
                <x-ui.table :headers="['Kelas', 'Jumlah']">
                    @foreach ($vehiclesPerClass as $room => $total)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $room }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $total }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>

            <x-ui.card title="Stiker per status" padding="p-0">
                <x-ui.table :headers="['Status', 'Jumlah']">
                    @foreach ($permitsPerStatus as $status => $total)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ \App\Enums\PermitStatus::from($status)->label() }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $total }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        </div>
    @endif

    @if ($tab === 'pelanggaran')
        <x-ui.stat label="Total poin pelanggaran" :value="$totalPoints" icon="flag" color="warning" />

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-ui.card title="Per kategori" padding="p-0">
                <x-ui.table :headers="['Kategori', 'Jumlah']">
                    @foreach ($perCategory as $label => $total)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $label }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $total }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>

            <x-ui.card title="Per kelas" padding="p-0">
                <x-ui.table :headers="['Kelas', 'Jumlah']">
                    @foreach ($perClassViolation as $room => $total)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $room }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $total }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        </div>
    @endif

    @if ($tab === 'scan')
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-ui.card title="Hasil scan" padding="p-0">
                <x-ui.table :headers="['Hasil', 'Jumlah']">
                    @foreach ($perResult as $label => $total)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $label }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $total }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>

            <x-ui.card title="Scan gagal / ditolak per hari" padding="p-0">
                <x-ui.table :headers="['Tanggal', 'Jumlah']">
                    @foreach ($perDay as $date => $total)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $total }}</td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        </div>
    @endif
</div>
