@props(['name' => null, 'title' => null, 'maxWidth' => 'max-w-lg', 'show' => 'false'])

{{--
    Modal dapat dipicu dari dua arah, dan bentuk payload-nya berbeda:
    - Alpine  : $dispatch('open-modal', 'nama')      -> detail berupa string
    - Livewire: $this->dispatch('open-modal', 'nama') -> detail berupa array ['nama']
                $this->dispatch('open-modal', name: 'nama') -> detail berupa objek {name: 'nama'}
    resolveName() menyeragamkan ketiganya. close-modal tanpa argumen menutup semua modal.
--}}
<div
    x-data="{
        open: {{ $show }},
        modalName: @js($name),
        resolveName(detail) {
            if (detail === null || detail === undefined) return null;
            if (typeof detail === 'string') return detail;
            if (Array.isArray(detail)) return detail.length ? String(detail[0]) : null;
            if (typeof detail === 'object') {
                if (detail.name !== undefined) return String(detail.name);
                if (detail[0] !== undefined) return String(detail[0]);
                return null;
            }
            return String(detail);
        },
    }"
    @if ($name)
        x-on:open-modal.window="if (resolveName($event.detail) === modalName) open = true"
        x-on:close-modal.window="if ([null, modalName].includes(resolveName($event.detail))) open = false"
    @endif
    x-on:keydown.escape.window="open = false"
    x-cloak
>
    @isset($trigger)
        <div x-on:click="open = true">{{ $trigger }}</div>
    @endisset

    <template x-teleport="body">
        <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/50" x-on:click="open = false"></div>

            <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
                <div x-show="open" x-transition class="relative w-full {{ $maxWidth }} rounded-xl bg-white shadow-xl">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                        <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" x-on:click="open = false" aria-label="Tutup">
                            <x-icon.x class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="px-5 py-4">{{ $slot }}</div>

                    @isset($footer)
                        <div class="flex justify-end gap-2 rounded-b-xl border-t border-slate-100 bg-slate-50 px-5 py-3">{{ $footer }}</div>
                    @endisset
                </div>
            </div>
        </div>
    </template>
</div>
