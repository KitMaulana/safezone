@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
])

@php
    $variants = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 border-brand-600',
        'secondary' => 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300',
        'danger' => 'bg-danger-500 text-white hover:bg-danger-600 border-danger-500',
        'success' => 'bg-success-500 text-white hover:bg-success-600 border-success-500',
        'warning' => 'bg-warning-500 text-white hover:bg-warning-600 border-warning-500',
        'accent' => 'bg-accent-400 text-slate-900 hover:bg-accent-500 border-accent-400',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 border-transparent',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm gap-1.5',
        'md' => 'px-4 py-2.5 text-sm gap-2 min-h-touch',
        'lg' => 'px-5 py-3 text-base gap-2 min-h-touch',
    ];
    $classes = 'inline-flex items-center justify-center rounded-lg border font-semibold shadow-sm transition ssz-focus disabled:opacity-50 disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
