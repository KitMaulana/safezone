<div class="space-y-4">
    @section('header', 'Beranda')

    {{-- Status stiker --}}
    @if ($student->status->value === 'blocked')
        <div class="rounded-xl bg-danger-500 p-5 text-white">
            <p class="text-sm font-semibold uppercase tracking-wide opacity-90">Kartu masuk ditangguhkan</p>
            <p class="mt-2 text-base font-medium">{{ $student->blocked_reason }}</p>
            @if ($student->blocked_until)
                <p class="mt-2 text-sm opacity-90">Berlaku sampai {{ $student->blocked_until->format('d M Y') }}.</p>
            @else
                <p class="mt-2 text-sm opacity-90">Hubungi bagian Kesiswaan untuk membuka blokir.</p>
            @endif
        </div>
    @elseif ($permit && $permit->isUsable())
        <div class="rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 p-5 text-white">
            <p class="text-sm font-semibold uppercase tracking-wide opacity-90">Stiker aktif</p>
            <p class="ssz-plate mt-1 text-3xl font-extrabold">{{ $vehicle?->formatted_plate }}</p>
            <p class="mt-2 text-sm opacity-90">{{ $permit->permit_number }} &middot; berlaku s.d. {{ $permit->expires_at->format('d M Y') }}</p>

            <a href="{{ route('siswa.card') }}" class="mt-4 inline-flex min-h-touch items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-sm font-bold">
                <x-icon.id-card class="h-5 w-5" /> Lihat kartu digital
            </a>
        </div>
    @elseif ($permit)
        <x-ui.alert type="warning" title="Stiker tidak aktif">
            Status stiker Anda: {{ $permit->status->label() }}. Hubungi admin sekolah.
        </x-ui.alert>
    @else
        <x-ui.alert type="info" title="Belum punya stiker">
            Daftarkan kendaraan Anda ke bagian Kesiswaan untuk mendapatkan Kartu Masuk ber-QR.
        </x-ui.alert>
    @endif

    {{-- Status hari ini --}}
    <x-ui.card title="Hari ini">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Cek in</p>
                <p class="mt-0.5 text-2xl font-bold text-slate-900">
                    {{ $checkIn?->scanned_at->timezone('Asia/Jakarta')->format('H.i') ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Cek out</p>
                <p class="mt-0.5 text-2xl font-bold text-slate-900">
                    {{ $checkOut?->scanned_at->timezone('Asia/Jakarta')->format('H.i') ?? '—' }}
                </p>
            </div>
        </div>

        @if (! $checkIn)
            <p class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-500">Belum tercatat masuk hari ini.</p>
        @endif
    </x-ui.card>

    <x-ui.stat label="Cek in 7 hari terakhir" :value="$weekCount" icon="chart" color="brand" />

    <x-ui.card title="Data saya">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">NISN</dt>
                <dd class="font-medium text-slate-800">{{ $student->nisn }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Kelas</dt>
                <dd class="font-medium text-slate-800">{{ $student->class_room }}</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Kendaraan</dt>
                <dd class="ssz-plate font-mono font-medium text-slate-800">{{ $vehicle?->formatted_plate ?? '—' }}</dd>
            </div>
        </dl>
    </x-ui.card>
</div>
