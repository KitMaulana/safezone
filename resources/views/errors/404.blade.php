@extends('layouts.public')

@section('title', '404 — Halaman tidak ditemukan')

@section('content')
    <div class="text-center">
        <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="mx-auto h-16 w-16 object-contain">

        <span class="mx-auto mt-6 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
            <x-icon.search class="h-8 w-8" />
        </span>

        <p class="mt-4 text-5xl font-extrabold text-slate-300">404</p>
        <h1 class="mt-2 text-xl font-bold text-slate-900">Halaman tidak ditemukan</h1>
        <p class="mt-2 text-sm text-slate-600">{{ $exception?->getMessage() ?: 'Alamat yang Anda tuju tidak tersedia atau sudah dipindahkan.' }}</p>

        <div class="mt-6">
            <x-ui.button :href="url('/')" size="lg" class="w-full">Kembali ke beranda</x-ui.button>
        </div>
    </div>
@endsection
