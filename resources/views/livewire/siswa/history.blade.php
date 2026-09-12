<div class="space-y-4">
    @section('header', 'Riwayat')

    <div class="flex flex-wrap items-end gap-3">
        <div class="min-w-40 flex-1">
            <x-ui.select wire:model.live="month" label="Bulan">
                @foreach ($months as $m)
                    <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <x-ui.button variant="secondary" :href="route('siswa.history.pdf', ['month' => $month])">
            <x-icon.download class="h-5 w-5" /> PDF
        </x-ui.button>
    </div>

    @if ($logs->count())
        <div class="space-y-2">
            @foreach ($logs as $log)
                <x-ui.card padding="p-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <x-ui.badge :color="$log->type === 'in' ? 'success' : 'brand'">{{ $log->typeLabel() }}</x-ui.badge>
                            @if ($log->kind !== 'normal')
                                <x-ui.badge color="slate" size="sm" class="ml-1">{{ $log->kindLabel() }}</x-ui.badge>
                            @endif
                            @if ($log->is_early_leave)
                                <x-ui.badge color="warning" size="sm" class="ml-1">Lebih awal</x-ui.badge>
                            @endif
                            <p class="mt-1 text-sm text-slate-700">{{ $log->scanned_at->timezone('Asia/Jakarta')->translatedFormat('l, d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-slate-900">{{ $log->scanned_at->timezone('Asia/Jakarta')->format('H.i') }}</p>
                            <p class="text-xs text-slate-400">{{ $log->gate?->name ?? '—' }}</p>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div>{{ $logs->links() }}</div>
    @else
        <x-ui.empty-state icon="clock" title="Belum ada riwayat" description="Belum ada catatan cek in/cek out pada bulan ini." />
    @endif
</div>
