<div class="space-y-4">
    @section('header', 'Kendaraan & Stiker')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $permits->total() }} stiker</p>

        <form method="POST" action="{{ route('admin.stiker.batch') }}" target="_blank" class="flex flex-wrap gap-2">
            @csrf
            @foreach ($selected as $id)
                <input type="hidden" name="permits[]" value="{{ $id }}">
            @endforeach

            <x-ui.button type="submit" :disabled="count($selected) === 0" variant="{{ count($selected) ? 'primary' : 'secondary' }}">
                <x-icon.printer class="h-5 w-5" />
                Cetak Massal ({{ count($selected) }})
            </x-ui.button>
        </form>
    </div>

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <x-ui.input wire:model.live.debounce.400ms="search" label="Cari" placeholder="Nomor stiker, plat, atau nama siswa…" />
            </div>
            <x-ui.select wire:model.live="status" label="Status">
                <option value="">Semua status</option>
                @foreach ($statuses as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select wire:model.live="classRoom" label="Kelas">
                <option value="">Semua kelas</option>
                @foreach ($classRooms as $room)
                    <option value="{{ $room }}">{{ $room }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    @if ($permits->count())
        <x-ui.table :headers="['', 'No. Stiker', 'Plat', 'Siswa', 'Berlaku s.d.', 'Status', 'Aksi']">
            @foreach ($permits as $permit)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" wire:model.live="selected" value="{{ $permit->id }}"
                               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                               aria-label="Pilih stiker {{ $permit->permit_number }}">
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-slate-800">{{ $permit->permit_number }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="ssz-plate font-mono font-bold text-slate-900">{{ $permit->vehicle->formatted_plate }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.students.show', $permit->vehicle->student_id) }}" class="font-medium text-slate-900 hover:text-brand-600">
                            {{ $permit->vehicle->student->name }}
                        </a>
                        <p class="text-xs text-slate-500">{{ $permit->vehicle->student->class_room }}</p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $permit->expires_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$permit->status->badgeColor()">{{ $permit->status->label() }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex flex-wrap items-center gap-1">
                            <a href="{{ route('admin.stiker.preview', $permit) }}" target="_blank" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Pratinjau">
                                <x-icon.eye class="h-4 w-4" />
                            </a>
                            <a href="{{ route('admin.stiker.single', $permit) }}" target="_blank" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Cetak">
                                <x-icon.printer class="h-4 w-4" />
                            </a>
                            @if ($permit->status->value !== 'active' && in_array($permit->status->value, ['draft', 'printed', 'suspended'], true))
                                <button type="button" wire:click="activate({{ $permit->id }})" class="rounded px-2 py-1 text-xs font-semibold text-success-600 hover:bg-success-50">Aktifkan</button>
                            @endif
                            @if ($permit->status->value === 'active')
                                <button type="button" wire:click="suspend({{ $permit->id }})" class="rounded px-2 py-1 text-xs font-semibold text-warning-600 hover:bg-warning-50">Tangguhkan</button>
                            @endif
                            @if (in_array($permit->status->value, ['expired', 'revoked'], true))
                                <button type="button" wire:click="renew({{ $permit->id }})" class="rounded px-2 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50">Perpanjang</button>
                            @else
                                <button type="button" x-on:click="$wire.set('revokeId', {{ $permit->id }}); $dispatch('open-modal', 'cabut')" class="rounded px-2 py-1 text-xs font-semibold text-danger-600 hover:bg-danger-50">Cabut</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $permits->links() }}</div>
    @else
        <x-ui.empty-state icon="qr-code" title="Belum ada stiker" description="Terbitkan stiker dari halaman detail siswa, tab Kendaraan & Stiker." />
    @endif

    <x-ui.modal name="cabut" title="Cabut stiker">
        <form wire:submit="revoke" class="space-y-4">
            <x-ui.textarea wire:model="revokeReason" name="revokeReason" label="Alasan pencabutan" rows="3" required />
            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit" variant="danger">Cabut stiker</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
