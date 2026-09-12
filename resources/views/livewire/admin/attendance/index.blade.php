<div class="space-y-4">
    @section('header', 'Log Kehadiran')

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <x-ui.input wire:model.live="from" type="date" label="Dari" />
            <x-ui.input wire:model.live="to" type="date" label="Sampai" />
            <x-ui.select wire:model.live="type" label="Jenis">
                <option value="">Semua</option>
                <option value="in">Cek In</option>
                <option value="out">Cek Out</option>
            </x-ui.select>
            <x-ui.select wire:model.live="classRoom" label="Kelas">
                <option value="">Semua kelas</option>
                @foreach ($classRooms as $room)
                    <option value="{{ $room }}">{{ $room }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="gateId" label="Gerbang">
                <option value="">Semua gerbang</option>
                @foreach ($gates as $gate)
                    <option value="{{ $gate->id }}">{{ $gate->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input wire:model.live.debounce.400ms="search" label="Cari siswa" placeholder="Nama / NISN" />
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <x-ui.button size="sm" variant="secondary"
                         :href="route('admin.reports.attendance.export', ['from' => $from, 'to' => $to, 'type' => $type, 'classRoom' => $classRoom, 'gateId' => $gateId, 'format' => 'xlsx'])">
                <x-icon.download class="h-4 w-4" /> Ekspor XLSX
            </x-ui.button>
            <x-ui.button size="sm" variant="secondary"
                         :href="route('admin.reports.attendance.export', ['from' => $from, 'to' => $to, 'type' => $type, 'classRoom' => $classRoom, 'gateId' => $gateId, 'format' => 'pdf'])">
                <x-icon.printer class="h-4 w-4" /> Ekspor PDF
            </x-ui.button>
        </div>
    </x-ui.card>

    @if ($logs->count())
        <x-ui.table :headers="['Waktu', 'Siswa', 'Kelas', 'Plat', 'Jenis', 'Gerbang', 'Petugas']">
            @foreach ($logs as $log)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->scanned_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.students.show', $log->student_id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $log->student->name }}</a>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->student->class_room }}</td>
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600">{{ $log->vehicle?->formatted_plate ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$log->type === 'in' ? 'success' : 'brand'">{{ $log->typeLabel() }}</x-ui.badge>
                        @if ($log->kind !== 'normal')
                            <x-ui.badge color="slate" size="sm" class="ml-1">{{ $log->kindLabel() }}</x-ui.badge>
                        @endif
                        @if ($log->is_early_leave)
                            <x-ui.badge color="warning" size="sm" class="ml-1">Lebih awal</x-ui.badge>
                        @endif
                        @if ($log->is_offline_sync)
                            <x-ui.badge color="slate" size="sm" class="ml-1">Offline</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->gate?->name ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->scannedBy?->name ?? '—' }}</td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $logs->links() }}</div>
    @else
        <x-ui.empty-state icon="clock" title="Tidak ada data" description="Tidak ada catatan kehadiran pada rentang yang dipilih." />
    @endif
</div>
