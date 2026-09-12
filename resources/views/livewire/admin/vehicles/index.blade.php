<div class="space-y-4">
    @section('header', 'Kendaraan')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $vehicles->total() }} kendaraan terdaftar</p>
        <x-ui.button :href="route('admin.vehicles.create')">
            <x-icon.plus class="h-5 w-5" /> Tambah Kendaraan
        </x-ui.button>
    </div>

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <x-ui.input wire:model.live.debounce.400ms="search" label="Cari" placeholder="Plat nomor, merek, atau nama siswa…" />
            </div>
            <x-ui.select wire:model.live="classRoom" label="Kelas">
                <option value="">Semua kelas</option>
                @foreach ($classRooms as $room)
                    <option value="{{ $room }}">{{ $room }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    @if ($vehicles->count())
        <x-ui.table :headers="['Nomor Polisi', 'Kendaraan', 'Pemilik', 'Stiker', 'Aksi']">
            @foreach ($vehicles as $vehicle)
                @php $permit = $vehicle->permits->first(); @endphp
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="ssz-plate font-mono font-bold text-slate-900">{{ $vehicle->formatted_plate }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $vehicle->brand }} {{ $vehicle->model }} <span class="text-slate-400">{{ $vehicle->color }}</span></td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.students.show', $vehicle->student_id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $vehicle->student->name }}</a>
                        <p class="text-xs text-slate-500">{{ $vehicle->student->class_room }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if ($permit)
                            <x-ui.badge :color="$permit->status->badgeColor()">{{ $permit->status->label() }}</x-ui.badge>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $permit->permit_number }}</p>
                        @else
                            <span class="text-xs text-slate-400">Belum ada stiker</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Ubah">
                            <x-icon.pencil class="h-4 w-4" />
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $vehicles->links() }}</div>
    @else
        <x-ui.empty-state icon="truck" title="Belum ada kendaraan" description="Tambahkan kendaraan dari halaman siswa atau langsung dari sini." />
    @endif
</div>
