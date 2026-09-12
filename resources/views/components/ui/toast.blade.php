{{-- Toast global. Dipicu dari Livewire: $this->dispatch('toast', type: 'success', message: '...') --}}
<div
    x-data="{ items: [] }"
    x-on:toast.window="
        const id = Date.now() + Math.random();
        items.push({ id, type: $event.detail.type || 'success', message: $event.detail.message });
        setTimeout(() => items = items.filter((i) => i.id !== id), 4000);
    "
    class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-center gap-2 px-4"
    x-cloak
>
    <template x-for="item in items" :key="item.id">
        <div
            x-transition
            class="pointer-events-auto w-full max-w-sm rounded-lg border px-4 py-3 text-sm font-medium shadow-lg"
            :class="{
                'bg-success-50 border-success-500/30 text-success-700': item.type === 'success',
                'bg-danger-50 border-danger-500/30 text-danger-700': item.type === 'danger' || item.type === 'error',
                'bg-warning-50 border-warning-500/30 text-warning-700': item.type === 'warning',
                'bg-brand-50 border-brand-200 text-brand-800': item.type === 'info',
            }"
            x-text="item.message"
        ></div>
    </template>
</div>
