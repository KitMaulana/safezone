@props(['src' => null, 'name' => '', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-base',
        'xl' => 'h-24 w-24 text-2xl',
    ];
    $cls = $sizes[$size] ?? $sizes['md'];
    $initials = collect(explode(' ', trim($name)))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp

@if ($src)
    <img src="{{ $src }}" alt="Foto {{ $name }}" {{ $attributes->merge(['class' => 'shrink-0 rounded-full bg-slate-100 object-cover ring-2 ring-white ' . $cls]) }} />
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-brand-100 font-semibold uppercase text-brand-700 ' . $cls]) }}>
        {{ $initials ?: '?' }}
    </span>
@endif
