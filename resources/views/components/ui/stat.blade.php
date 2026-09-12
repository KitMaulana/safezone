@props(['label', 'value', 'icon' => null, 'color' => 'brand', 'hint' => null, 'href' => null])

@php
    $colors = [
        'brand' => 'bg-brand-50 text-brand-600',
        'success' => 'bg-success-50 text-success-600',
        'danger' => 'bg-danger-50 text-danger-500',
        'warning' => 'bg-warning-50 text-warning-600',
        'accent' => 'bg-accent-50 text-accent-600',
        'slate' => 'bg-slate-100 text-slate-500',
    ];
    $box = $colors[$color] ?? $colors['brand'];
    $base = 'flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base . ' hover:border-brand-300 hover:shadow']) }}>
        @if ($icon)
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $box }}">
                <x-dynamic-component :component="'icon.' . $icon" class="h-6 w-6" />
            </span>
        @endif
        <span class="min-w-0">
            <span class="block truncate text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</span>
            <span class="mt-0.5 block text-2xl font-bold leading-tight text-slate-900">{{ $value }}</span>
            @if ($hint)
                <span class="mt-0.5 block truncate text-xs text-slate-400">{{ $hint }}</span>
            @endif
        </span>
    </a>
@else
    <div {{ $attributes->merge(['class' => $base]) }}>
        @if ($icon)
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $box }}">
                <x-dynamic-component :component="'icon.' . $icon" class="h-6 w-6" />
            </span>
        @endif
        <div class="min-w-0">
            <p class="truncate text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
            <p class="mt-0.5 text-2xl font-bold leading-tight text-slate-900">{{ $value }}</p>
            @if ($hint)
                <p class="mt-0.5 truncate text-xs text-slate-400">{{ $hint }}</p>
            @endif
        </div>
    </div>
@endif
