@props(['color' => 'slate', 'size' => 'md'])

@php
    $colors = [
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-200',
        'success' => 'bg-success-50 text-success-700 ring-success-500/20',
        'danger' => 'bg-danger-50 text-danger-700 ring-danger-500/20',
        'warning' => 'bg-warning-50 text-warning-700 ring-warning-500/20',
        'accent' => 'bg-accent-50 text-accent-600 ring-accent-400/30',
    ];
    $sizes = ['sm' => 'px-2 py-0.5 text-xs', 'md' => 'px-2.5 py-1 text-xs'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full font-semibold ring-1 ring-inset ' . ($colors[$color] ?? $colors['slate']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    {{ $slot }}
</span>
