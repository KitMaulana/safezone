<div class="space-y-6">
    @section('header', 'Dashboard')

    {{-- Kartu statistik --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
        <x-ui.stat label="Kendaraan terdaftar" :value="$stats['vehicles']" icon="truck" color="brand" />
        <x-ui.stat label="Stiker aktif" :value="$stats['activePermits']" icon="qr-code" color="success" />
        <x-ui.stat label="Cek in hari ini" :value="$stats['checkedIn']" icon="check-circle" color="success" />
        <x-ui.stat label="Belum cek out" :value="$stats['notCheckedOut']" icon="clock" color="warning" />
        <x-ui.stat label="Ditolak hari ini" :value="$stats['deniedToday']" icon="ban" color="danger" />
        <x-ui.stat label="Siswa diblokir" :value="$stats['blockedStudents']" icon="exclamation" color="danger" />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Grafik 7 hari --}}
        <x-ui.card title="Cek in 7 hari terakhir" class="xl:col-span-2">
            @php $max = max(1, collect($chart)->max('value')); @endphp
            <div class="flex h-48 items-end gap-2 sm:gap-4">
                @foreach ($chart as $day)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <span class="text-xs font-semibold text-slate-700">{{ $day['value'] }}</span>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t-md bg-brand-500 transition-all"
                                 style="height: {{ max(4, round($day['value'] / $max * 100)) }}%"
                                 title="{{ $day['date'] }}: {{ $day['value'] }} cek in"></div>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- Perlu perhatian --}}
        <div class="space-y-4">
            <x-ui.card title="Perlu perhatian">
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center justify-between gap-3">
                        <span class="text-slate-600">Pengajuan data menunggu</span>
                        <x-ui.badge :color="$pendingRequests > 0 ? 'warning' : 'slate'">{{ $pendingRequests }}</x-ui.badge>
                    </li>
                    <li class="flex items-center justify-between gap-3">
                        <span class="text-slate-600">Stiker kedaluwarsa &lt; 30 hari</span>
                        <x-ui.badge :color="$expiringPermits->count() > 0 ? 'warning' : 'slate'">{{ $expiringPermits->count() }}</x-ui.badge>
                    </li>
                    <li class="flex items-center justify-between gap-3">
                        <span class="text-slate-600">Siswa diblokir</span>
                        <x-ui.badge :color="$stats['blockedStudents'] > 0 ? 'danger' : 'slate'">{{ $stats['blockedStudents'] }}</x-ui.badge>
                    </li>
                </ul>
            </x-ui.card>

            <x-ui.card title="Stiker akan kedaluwarsa">
                @forelse ($expiringPermits as $permit)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-50 py-2 last:border-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $permit->vehicle->student->name }}</p>
                            <p class="text-xs text-slate-500">{{ $permit->permit_number }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-medium text-warning-600">{{ $permit->expires_at->format('d M Y') }}</span>
                    </div>
                @empty
                    <p class="py-2 text-sm text-slate-500">Tidak ada stiker yang akan kedaluwarsa dalam 30 hari.</p>
                @endforelse
            </x-ui.card>
        </div>
    </div>

    {{-- 10 scan terakhir --}}
    <x-ui.card title="10 scan terakhir" subtitle="Diperbarui otomatis tiap 30 detik" padding="p-0" wire:poll.30s>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kelas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Gerbang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentScans as $log)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->scanned_at->timezone('Asia/Jakarta')->format('d M · H.i') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $log->student->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->student->class_room }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :color="$log->type === 'in' ? 'success' : 'brand'">{{ $log->typeLabel() }}</x-ui.badge>
                                @if ($log->is_early_leave)
                                    <x-ui.badge color="warning" class="ml-1">Keluar lebih awal</x-ui.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->gate?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada aktivitas scan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
