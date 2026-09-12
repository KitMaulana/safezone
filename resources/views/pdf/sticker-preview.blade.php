@extends('layouts.public')

@section('title', 'Pratinjau Stiker ' . $card['permit_number'])

@section('content')
    <div class="text-center">
        <h1 class="text-lg font-bold text-slate-900">Pratinjau Stiker</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $card['permit_number'] }} &middot; {{ $card['plate'] }}</p>
    </div>

    @include('pdf._sticker-styles')

    <div class="mt-5 flex justify-center overflow-x-auto">
        <div class="shadow-lg">
            @include('pdf._sticker-card', ['card' => $card])
        </div>
    </div>

    <div class="no-print mt-6 flex flex-col gap-2">
        <x-ui.button :href="route('admin.stiker.single', $permit)" size="lg" class="w-full">
            <x-icon.printer class="h-5 w-5" /> Cetak PDF (90 × 55 mm)
        </x-ui.button>
        <x-ui.button variant="secondary" :href="route('admin.stiker.single', [$permit, 'a4' => 1])" size="lg" class="w-full">
            <x-icon.download class="h-5 w-5" /> Cetak PDF di kertas A4
        </x-ui.button>
        <x-ui.button variant="ghost" :href="route('admin.permits.index')" size="lg" class="w-full">Kembali</x-ui.button>
    </div>
@endsection
