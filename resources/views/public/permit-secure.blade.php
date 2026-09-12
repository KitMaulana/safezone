@extends('layouts.mobile')

@section('title', 'Verifikasi Kendaraan')
@section('header', 'Verifikasi Kendaraan')

@section('content')
    <div class="space-y-4">
        {{-- Identitas siswa --}}
        <x-ui.card>
            <div class="flex items-start gap-4">
                @if ($student->photo_path)
                    <img src="{{ route('media', ['path' => $student->photo_path]) }}" alt="Foto {{ $student->name }}" class="h-20 w-20 rounded-xl object-cover">
                @else
                    <x-ui.avatar :name="$student->name" size="xl" class="rounded-xl" />
                @endif

                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-bold leading-tight text-slate-900">{{ $student->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $student->nisn }} &middot; {{ $student->class_room }}</p>
                    <div class="mt-2">
                        <x-ui.badge :color="$student->status->badgeColor()">{{ $student->status->label() }}</x-ui.badge>
                    </div>
                </div>
            </div>

            @if ($student->status->value === 'blocked')
                <x-ui.alert type="danger" class="mt-4" title="Siswa diblokir">
                    {{ $student->blocked_reason }}
                </x-ui.alert>
            @endif
        </x-ui.card>

        {{-- Kendaraan & stiker --}}
        <x-ui.card title="Kendaraan">
            <p class="ssz-plate text-2xl font-extrabold text-slate-900">{{ $vehicle->formatted_plate }}</p>
            <p class="text-sm text-slate-600">{{ $vehicle->brand }} {{ $vehicle->model }} &middot; {{ $vehicle->color }} @if ($vehicle->year) &middot; {{ $vehicle->year }} @endif</p>
            <p class="mt-1 text-xs text-slate-500">SIM: {{ $vehicle->sim_type === 'tidak_ada' ? 'Tidak ada' : $vehicle->sim_type }}</p>

            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                <x-ui.badge :color="$permit->status->badgeColor()">{{ $permit->status->label() }}</x-ui.badge>
                <span class="text-xs text-slate-500">{{ $permit->permit_number }} &middot; berlaku s.d. {{ $permit->expires_at->format('d M Y') }}</span>
            </div>
        </x-ui.card>

        {{-- Tombol cek in / cek out --}}
        <x-ui.card title="Catat kehadiran">
            <div x-data="secureScan()" class="space-y-3">
                @if ($gates->count() > 1)
                    <x-ui.select x-model="gateId" label="Gerbang">
                        @foreach ($gates as $gate)
                            <option value="{{ $gate->id }}">{{ $gate->name }}</option>
                        @endforeach
                    </x-ui.select>
                @else
                    <input type="hidden" x-model="gateId" value="{{ $gates->first()?->id }}">
                @endif

                <button type="button" x-on:click="send('auto')" x-bind:disabled="busy"
                        class="flex min-h-touch w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-4 text-base font-bold text-white shadow-sm transition hover:bg-brand-700 disabled:opacity-60">
                    <x-icon.qr-code class="h-6 w-6" />
                    <span x-text="busy ? 'Memproses…' : 'Cek In / Cek Out sekarang'"></span>
                </button>

                <div class="grid grid-cols-2 gap-2">
                    <x-ui.button variant="secondary" x-on:click="send('in')" x-bind:disabled="busy">Paksa Cek In</x-ui.button>
                    <x-ui.button variant="secondary" x-on:click="send('out')" x-bind:disabled="busy">Paksa Cek Out</x-ui.button>
                </div>

                <template x-if="result">
                    <div class="rounded-xl p-4 text-center"
                         :class="{
                            'bg-success-50 text-success-700': result.color === 'green',
                            'bg-danger-50 text-danger-700': result.color === 'red',
                            'bg-warning-50 text-warning-700': result.color === 'yellow',
                            'bg-slate-100 text-slate-600': result.color === 'gray',
                         }">
                        <p class="text-lg font-bold" x-text="result.message"></p>
                        <p class="mt-1 text-sm" x-show="result.reason" x-text="result.reason"></p>
                    </div>
                </template>
            </div>
        </x-ui.card>

        {{-- Orang tua & alamat --}}
        <x-ui.card title="Kontak orang tua">
            @if ($primaryParent)
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $primaryParent->name }}</p>
                        <p class="text-xs text-slate-500">{{ $primaryParent->relationship_label }} &middot; {{ $primaryParent->phone }}</p>
                    </div>
                    <a href="tel:{{ $primaryParent->phone }}" class="inline-flex min-h-touch items-center gap-1 rounded-lg bg-brand-50 px-4 text-sm font-semibold text-brand-700">
                        <x-icon.phone class="h-4 w-4" /> Telepon
                    </a>
                </div>
            @else
                <p class="text-sm text-slate-500">Belum ada kontak orang tua.</p>
            @endif

            <div class="mt-3 border-t border-slate-100 pt-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Alamat</p>
                <p class="mt-0.5 text-sm text-slate-700">{{ $student->address ?: '—' }}</p>
            </div>

            @if ($policePhone)
                <a href="tel:{{ $policePhone }}"
                   class="mt-3 flex min-h-touch items-center justify-between gap-3 rounded-xl bg-slate-900 px-4 py-3 text-white">
                    <span class="flex items-center gap-2 text-sm font-bold">
                        <x-icon.shield class="h-5 w-5 text-brand-400" />
                        Kepolisian RI
                    </span>
                    <span class="flex items-center gap-1.5 text-xl font-extrabold leading-none">
                        <x-icon.phone class="h-4 w-4 text-red-400" />
                        {{ $policePhone }}
                    </span>
                </a>
            @endif
        </x-ui.card>

        {{-- 5 log terakhir --}}
        <x-ui.card title="5 catatan terakhir">
            @forelse ($recentLogs as $log)
                <div class="flex items-center justify-between gap-3 border-b border-slate-50 py-2 last:border-0">
                    <div>
                        <x-ui.badge :color="$log->type === 'in' ? 'success' : 'brand'" size="sm">{{ $log->typeLabel() }}</x-ui.badge>
                        <span class="ml-2 text-sm text-slate-600">{{ $log->scanned_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</span>
                    </div>
                    <span class="shrink-0 text-xs text-slate-400">{{ $log->gate?->name ?? '—' }}</span>
                </div>
            @empty
                <p class="py-2 text-sm text-slate-500">Belum ada catatan.</p>
            @endforelse
        </x-ui.card>
    </div>
@endsection

@push('scripts')
<script>
    function secureScan() {
        return {
            busy: false,
            result: null,
            gateId: @json($gates->first()?->id),

            async send(mode) {
                this.busy = true;
                this.result = null;

                try {
                    const response = await fetch(@json(route('api.scan')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({
                            token: @json($permit->qr_token),
                            mode: mode,
                            gate_id: this.gateId,
                        }),
                    });

                    this.result = await response.json();
                    window.SSZ.vibrate(this.result.color === 'green' ? 80 : [60, 60, 60]);
                    window.SSZ.beep(this.result.color === 'green' ? 980 : 300);
                } catch (e) {
                    this.result = { color: 'yellow', message: 'Gagal menghubungi server.', reason: 'Periksa koneksi internet.' };
                } finally {
                    this.busy = false;
                }
            },
        };
    }
</script>
@endpush
