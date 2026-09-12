@props(['label' => null, 'name' => null, 'type' => 'text', 'hint' => null, 'required' => false])

<div class="w-full">
    @if ($label)
        <label @if($name) for="{{ $name }}" @endif class="mb-1.5 block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($required)<span class="text-danger-500">*</span>@endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-300 text-sm shadow-sm placeholder:text-slate-400 focus:border-brand-500 focus:ring-brand-500 disabled:bg-slate-100']) }}
    />

    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @if ($name)
        @error($name)
            <p class="mt-1 text-xs font-medium text-danger-600">{{ $message }}</p>
        @enderror
    @endif
</div>
