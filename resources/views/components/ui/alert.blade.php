@props(['type' => 'info', 'title' => null, 'dismissible' => false])

@php
    $styles = [
        'info' => ['bg-brand-50 border-brand-200 text-brand-800', 'exclamation'],
        'success' => ['bg-success-50 border-success-500/30 text-success-700', 'check'],
        'danger' => ['bg-danger-50 border-danger-500/30 text-danger-700', 'exclamation'],
        'warning' => ['bg-warning-50 border-warning-500/30 text-warning-700', 'exclamation'],
    ];
    [$style, $icon] = $styles[$type] ?? $styles['info'];
@endphp

<div
    @if ($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm ' . $style]) }}
    role="alert"
>
    <x-dynamic-component :component="'icon.' . $icon" class="mt-0.5 h-5 w-5 shrink-0" />
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" x-on:click="show = false" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Tutup">
            <x-icon.x class="h-4 w-4" />
        </button>
    @endif
</div>
