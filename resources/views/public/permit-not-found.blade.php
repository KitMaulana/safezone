@extends('layouts.public')

@section('title', 'Stiker tidak dikenal')

@section('content')
    <div class="text-center">
        <div class="flex items-center justify-center gap-4">
            <img src="/images/logo-sman1ciruas.png" alt="Logo SMAN 1 Ciruas" class="h-14 w-14 object-contain">
            <img src="/images/logo-polres-serang.png" alt="Logo Satlantas Polres Serang" class="h-14 w-14 object-contain">
        </div>

        <span class="mx-auto mt-6 flex h-16 w-16 items-center justify-center rounded-full bg-warning-50 text-warning-600">
            <x-icon.exclamation class="h-8 w-8" />
        </span>

        <h1 class="mt-4 text-xl font-bold text-slate-900">Stiker tidak dikenal</h1>
        <p class="mt-2 text-sm text-slate-600">
            Kode pada stiker ini tidak terdaftar di School Safe Zone SMA Negeri 1 Ciruas,
            atau stiker sudah tidak berlaku lagi.
        </p>

        <div class="mt-6">
            <x-ui.button variant="secondary" :href="url('/')" size="lg" class="w-full">Kembali ke beranda</x-ui.button>
        </div>
    </div>
@endsection
