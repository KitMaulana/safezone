<div class="space-y-4">
    @section('header', 'Riwayat')

    <div class="grid grid-cols-2 gap-3">
        @if ($children->count() > 1)
            <x-ui.select wire:model.live="studentId" label="Anak">
                @foreach ($children as $child)
                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                @endforeach
            </x-ui.select>
        @endif

        <x-ui.select wire:model.live="month" label="Bulan">
            @foreach ($months as $m)
                <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
            @endforeach
        </x-ui.select>
    </div>

    <x-ui.button variant="secondary" :href="route('ortu.history.pdf', ['student' => $studentId, 'month' => $month])" class="w-full">
        <x-icon.download class="h-5 w-5" /> Unduh ringkasan bulanan (PDF)
    </x-ui.button>

    @if ($days->count())
        <div class="space-y-2">
            @foreach ($days as $day)
                <x-ui.card padding="p-4">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ \Illuminate\Support\Carbon::parse($day['date'])->translatedFormat('l, d M Y') }}
                    </p>

                    <div class="mt-2 flex items-center gap-6 text-sm">
                        <div>
                            <p class="text-xs text-slate-500">Masuk</p>
                            <p class="font-bold text-slate-800">{{ $day['in']?->timezone('Asia/Jakarta')->format('H.i') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Keluar</p>
                            <p class="font-bold text-slate-800">
                                {{ $day['out']?->timezone('Asia/Jakarta')->format('H.i') ?? '—' }}
                                @if ($day['early'])
                                    <span class="ml-1 text-xs font-medium text-warning-600">lebih awal</span>
                                @endif
                            </p>
                        </div>
                        @if ($day['duration'])
                            <div class="ml-auto text-right">
                                <p class="text-xs text-slate-500">Durasi</p>
                                <p class="font-bold text-slate-800">{{ $day['duration'] }}</p>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @else
        <x-ui.empty-state icon="clock" title="Belum ada catatan" description="Tidak ada riwayat cek in/cek out pada bulan ini." />
    @endif
</div>
