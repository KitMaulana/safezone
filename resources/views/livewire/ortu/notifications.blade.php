<div class="space-y-4">
    @section('header', 'Notifikasi')

    @if ($unread > 0)
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-slate-500">{{ $unread }} belum dibaca</p>
            <x-ui.button size="sm" variant="secondary" wire:click="markAllRead">Tandai semua dibaca</x-ui.button>
        </div>
    @endif

    @forelse ($items as $item)
        @php $data = $item->data; @endphp
        <x-ui.card padding="p-4" class="{{ $item->read_at ? '' : 'border-brand-200 bg-brand-50/40' }}">
            <div class="flex gap-3">
                <span class="text-2xl leading-none">{{ $data['emoji'] ?? '🔔' }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-900">{{ $data['title'] ?? 'Notifikasi' }}</p>
                    <p class="mt-0.5 text-sm text-slate-600">{{ $data['body'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $item->created_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</p>
                </div>
            </div>
        </x-ui.card>
    @empty
        <x-ui.empty-state icon="bell" title="Belum ada notifikasi" description="Pemberitahuan cek in dan cek out anak akan muncul di sini." />
    @endforelse

    <div>{{ $items->links() }}</div>
</div>
