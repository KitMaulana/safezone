<div class="space-y-4">
    @section('header', 'Kartu Digital')

    @if ($card)
        <x-ui.alert type="info">
            Naikkan kecerahan layar agar QR mudah dipindai petugas. Kartu ini berlaku sama dengan stiker di kendaraan.
        </x-ui.alert>

        @include('pdf._sticker-styles')

        <div class="flex justify-center overflow-x-auto py-2">
            <div class="shadow-xl">
                @include('pdf._sticker-card', ['card' => $card])
            </div>
        </div>

        <x-ui.card title="Rincian">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">No. Stiker</dt>
                    <dd class="font-mono font-medium text-slate-800">{{ $permit->permit_number }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Status</dt>
                    <dd><x-ui.badge :color="$permit->status->badgeColor()">{{ $permit->status->label() }}</x-ui.badge></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Berlaku s.d.</dt>
                    <dd class="font-medium text-slate-800">{{ $permit->expires_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </x-ui.card>
    @else
        <x-ui.empty-state icon="id-card" title="Belum ada kartu digital"
                          description="Kartu digital muncul setelah kendaraan Anda didaftarkan dan stikernya diterbitkan admin." />
    @endif
</div>
