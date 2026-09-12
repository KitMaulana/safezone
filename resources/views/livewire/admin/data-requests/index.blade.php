<div class="space-y-4">
    @section('header', 'Pengajuan Perubahan Data')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $pendingCount }} pengajuan menunggu persetujuan</p>
        <div class="w-48">
            <x-ui.select wire:model.live="status" label="Status">
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
                <option value="">Semua</option>
            </x-ui.select>
        </div>
    </div>

    @if ($requests->count())
        <x-ui.table :headers="['Diajukan', 'Siswa', 'Data', 'Nilai lama', 'Nilai baru', 'Status', 'Aksi']">
            @foreach ($requests as $request)
                @php
                    $statusColor = match ($request->status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    };
                    $statusLabel = match ($request->status) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'Menunggu',
                    };
                @endphp
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $request->created_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.students.show', $request->student_id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $request->student->name }}</a>
                        <p class="text-xs text-slate-500">{{ $request->student->class_room }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $request->fieldLabel() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $request->old_value ?: '—' }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $request->new_value }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$statusColor">{{ $statusLabel }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if ($request->status === 'pending')
                            <div class="flex gap-1">
                                <x-ui.button size="sm" variant="success" wire:click="approve({{ $request->id }})">Setujui</x-ui.button>
                                <x-ui.button size="sm" variant="danger" wire:click="openReject({{ $request->id }})">Tolak</x-ui.button>
                            </div>
                        @else
                            <span class="text-xs text-slate-400">{{ $request->reviewer?->name ?? '—' }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $requests->links() }}</div>
    @else
        <x-ui.empty-state icon="inbox" title="Tidak ada pengajuan" description="Pengajuan perubahan data dari siswa akan muncul di sini." />
    @endif

    <x-ui.modal name="tolak" title="Tolak pengajuan">
        <form wire:submit="reject" class="space-y-4">
            <x-ui.textarea wire:model="rejectNote" name="rejectNote" label="Alasan penolakan" rows="3" required />
            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit" variant="danger">Tolak pengajuan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
