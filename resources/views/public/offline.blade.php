@extends('layouts.public')

@section('title', 'Tidak ada koneksi')

@section('content')
    <div class="text-center">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
            <x-icon.exclamation class="h-8 w-8" />
        </span>
        <h1 class="mt-5 text-xl font-bold text-slate-900">Tidak ada koneksi</h1>
        <p class="mt-2 text-sm text-slate-600">
            Perangkat Anda sedang tidak terhubung ke internet. Data scan yang belum terkirim akan otomatis dikirim ulang saat koneksi kembali.
        </p>

        <div class="mt-6">
            <x-ui.button onclick="location.reload()" size="lg" class="w-full">
                <x-icon.refresh class="h-5 w-5" />
                Coba lagi
            </x-ui.button>
        </div>
    </div>
@endsection
