<div class="space-y-4">
    @section('header', 'Audit Log')

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <x-ui.select wire:model.live="action" label="Aksi">
                <option value="">Semua aksi</option>
                @foreach ($actions as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="userId" label="Pengguna">
                <option value="">Semua pengguna</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input wire:model.live="from" type="date" label="Dari" />
            <x-ui.input wire:model.live="to" type="date" label="Sampai" />
        </div>
    </x-ui.card>

    @if ($logs->count())
        <x-ui.table :headers="['Waktu', 'Pengguna', 'Aksi', 'Objek', 'IP']">
            @foreach ($logs as $log)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->created_at?->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</td>
                    <td class="px-4 py-3 text-slate-800">{{ $log->user?->name ?? 'Sistem' }}</td>
                    <td class="px-4 py-3"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">{{ $log->action }}</code></td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ class_basename($log->subject_type ?? '') }} {{ $log->subject_id ? '#'.$log->subject_id : '' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-400">{{ $log->ip }}</td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $logs->links() }}</div>
    @else
        <x-ui.empty-state icon="shield" title="Belum ada catatan audit" />
    @endif
</div>
