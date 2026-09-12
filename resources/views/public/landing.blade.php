@extends('layouts.public')

@section('title', 'School Safe Zone SMAN 1 Ciruas')

@section('content')
    <div class="text-center">
        <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="mx-auto h-24 w-24 object-contain">
        <h1 class="mt-5 text-2xl font-bold leading-tight text-slate-900">School Safe Zone</h1>
        <p class="text-lg font-semibold text-brand-600">SMA Negeri 1 Ciruas</p>
        <p class="mt-3 text-sm text-slate-600">Aman berangkat, aman pulang, orang tua tenang.</p>

        <div class="mt-6 flex flex-col gap-3">
            <x-ui.button :href="url('/login')" size="lg" class="w-full">
                <x-icon.login class="h-5 w-5" />
                Masuk
            </x-ui.button>
        </div>
    </div>

    <div class="mt-8 space-y-3">
        <x-ui.card padding="p-4">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <x-icon.qr-code class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Kartu Masuk ber-QR</h2>
                    <p class="mt-0.5 text-sm text-slate-600">Setiap sepeda motor siswa terdaftar memiliki stiker Kartu Masuk dengan QR unik.</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card padding="p-4">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600">
                    <x-icon.clock class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Cek in &amp; cek out tercatat</h2>
                    <p class="mt-0.5 text-sm text-slate-600">Petugas gerbang memindai QR saat siswa datang dan pulang. Orang tua menerima notifikasi.</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card padding="p-4">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger-50 text-danger-500">
                    <x-icon.phone class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Siaga keadaan darurat</h2>
                    <p class="mt-0.5 text-sm text-slate-600">Bila terjadi sesuatu di jalan, siapa pun dapat memindai QR untuk menghubungi kontak darurat — tanpa membuka data pribadi siswa.</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="mt-8 rounded-xl border border-accent-400/40 bg-accent-50 p-4 text-center">
        <p class="text-xs font-semibold uppercase tracking-wide text-accent-600">Program kolaborasi</p>
        <p class="mt-1 text-sm text-slate-700">SMA Negeri 1 Ciruas bersama Satlantas Polres Serang untuk keselamatan berkendara pelajar.</p>
    </div>
@endsection
