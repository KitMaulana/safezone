<div x-data="{ open: false }" class="relative" wire:poll.30s>
    <button type="button" x-on:click="open = !open" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Notifikasi">
        <x-icon.bell class="h-6 w-6" />
        @if ($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-danger-500 px-1 text-[10px] font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-on:click.outside="open = false" x-transition
         class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="text-sm font-semibold text-slate-900">Notifikasi</p>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllRead" class="text-xs font-medium text-brand-600 hover:text-brand-700">Tandai dibaca</button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($items as $item)
                @php $data = $item->data; @endphp
                <a href="{{ $data['url'] ?? '#' }}"
                   wire:click="markRead('{{ $item->id }}')"
                   class="flex gap-3 border-b border-slate-50 px-4 py-3 hover:bg-slate-50 {{ $item->read_at ? '' : 'bg-brand-50/40' }}">
                    <span class="mt-0.5 text-lg leading-none">{{ $data['emoji'] ?? '🔔' }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-slate-900">{{ $data['title'] ?? 'Notifikasi' }}</span>
                        <span class="mt-0.5 block text-xs text-slate-600">{{ $data['body'] ?? '' }}</span>
                        <span class="mt-1 block text-[11px] text-slate-400">{{ $item->created_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</span>
                    </span>
                </a>
            @empty
                <p class="px-4 py-8 text-center text-sm text-slate-500">Belum ada notifikasi.</p>
            @endforelse
        </div>
    </div>
</div>
