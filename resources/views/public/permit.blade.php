@extends('layouts.public')

@section('title', 'Kendaraan terdaftar SSZ SMAN 1 Ciruas')

@push('head')
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="Kendaraan terdaftar SSZ SMAN 1 Ciruas">
    <meta property="og:description" content="Kendaraan ini terdaftar dalam program School Safe Zone SMA Negeri 1 Ciruas.">
@endpush

@section('content')
    <div class="text-center">
        <div class="flex items-center justify-center gap-4">
            <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="h-14 w-14 object-contain">
            <img src="/images/logo-polres-serang.png" alt="Logo Satlantas Polres Serang" class="h-14 w-14 object-contain">
        </div>
        <p class="mt-4 text-sm font-medium text-slate-600">
            Kendaraan ini terdaftar di<br>
            <span class="font-bold text-slate-900">School Safe Zone {{ $schoolName }}</span>
        </p>
    </div>

    <div class="mt-6 rounded-2xl border-2 border-brand-600 bg-white p-6 text-center shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor Polisi</p>
        <p class="ssz-plate mt-1 text-4xl font-extrabold leading-tight text-slate-900">{{ $plate }}</p>

        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
            <x-ui.badge :color="$statusColor" size="md">Stiker {{ $statusLabel }}</x-ui.badge>
            <x-ui.badge color="slate" size="md">{{ $permit->permit_number }}</x-ui.badge>
        </div>
    </div>

    @if ($isOwner)
        <x-ui.alert type="info" class="mt-4">
            Ini kendaraan Anda / anak Anda.
            <a href="{{ route('login') }}" class="font-semibold underline">Buka dashboard</a>
        </x-ui.alert>
    @endif

    <div class="mt-6 space-y-3">
        @if ($emergencyPhone)
            <a href="tel:{{ $emergencyPhone }}"
               class="flex min-h-touch w-full items-center justify-center gap-2 rounded-xl bg-danger-500 px-5 py-4 text-base font-bold text-white shadow-sm transition hover:bg-danger-600">
                <x-icon.phone class="h-6 w-6" />
                Hubungi Kontak Darurat
            </a>
            @if ($showEmergencyPhone)
                <p class="text-center text-sm font-medium text-slate-600">{{ $emergencyPhone }}</p>
            @endif
        @endif

        @if ($policePhone)
            <a href="tel:{{ $policePhone }}"
               class="flex min-h-touch w-full items-center justify-between gap-3 rounded-xl border-2 border-slate-900 bg-slate-900 px-5 py-4 text-white shadow-sm transition hover:bg-slate-800">
                <span class="flex items-center gap-2 text-base font-bold">
                    <x-icon.shield class="h-6 w-6 text-brand-400" />
                    Kepolisian RI
                </span>
                <span class="flex items-center gap-2 text-2xl font-extrabold leading-none">
                    <x-icon.phone class="h-6 w-6 text-red-400" />
                    {{ $policePhone }}
                </span>
            </a>
        @endif

        @if ($schoolPhone)
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $schoolPhone) }}"
               class="flex min-h-touch w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-4 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <x-icon.phone class="h-5 w-5" />
                Hubungi Sekolah
            </a>
        @endif
    </div>

    <div class="mt-6 rounded-xl bg-slate-100 p-4">
        <p class="text-xs leading-relaxed text-slate-600">
            Halaman ini sengaja tidak menampilkan nama, NISN, alamat, maupun foto siswa demi keamanan data pribadi.
            Petugas sekolah yang telah masuk akan melihat data lengkap.
        </p>
    </div>

    <p class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Masuk sebagai petugas</a>
    </p>
@endsection
