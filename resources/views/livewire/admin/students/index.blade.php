<div class="space-y-4">
    @section('header', 'Data Siswa')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $students->total() }} siswa terdaftar</p>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="secondary" :href="route('admin.students.import')">
                <x-icon.upload class="h-5 w-5" /> Impor CSV
            </x-ui.button>
            <x-ui.button :href="route('admin.students.create')">
                <x-icon.plus class="h-5 w-5" /> Tambah Siswa
            </x-ui.button>
        </div>
    </div>

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <x-ui.input wire:model.live.debounce.400ms="search" label="Cari" placeholder="Nama, NISN, atau kelas…" />
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

        @if ($search || $status || $classRoom)
            <button type="button" wire:click="resetFilters" class="mt-3 text-xs font-medium text-brand-600 hover:text-brand-700">Bersihkan filter</button>
        @endif
    </x-ui.card>

    @if ($students->count())
        <x-ui.table :headers="['Nama', 'NISN', 'Kelas', 'Kendaraan', 'Status', 'Aksi']">
            @foreach ($students as $student)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.students.show', $student) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $student->name }}</a>
                        <p class="text-xs text-slate-500">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600">{{ $student->nisn }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $student->class_room }}</td>
                    <td class="px-4 py-3">
                        @forelse ($student->vehicles as $vehicle)
                            <span class="ssz-plate mr-1 inline-block rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-700">{{ $vehicle->formatted_plate }}</span>
                        @empty
                            <span class="text-xs text-slate-400">—</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$student->status->badgeColor()">{{ $student->status->label() }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.students.show', $student) }}" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Lihat">
                                <x-icon.eye class="h-4 w-4" />
                            </a>
                            <a href="{{ route('admin.students.edit', $student) }}" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Ubah">
                                <x-icon.pencil class="h-4 w-4" />
                            </a>
                            <button type="button"
                                    wire:click="delete({{ $student->id }})"
                                    wire:confirm="Hapus data {{ $student->name }}? Data dapat dipulihkan oleh admin."
                                    class="rounded p-1.5 text-slate-400 hover:bg-danger-50 hover:text-danger-600" title="Hapus">
                                <x-icon.trash class="h-4 w-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $students->links() }}</div>
    @else
        <x-ui.empty-state icon="users" title="Belum ada siswa" description="Tambahkan siswa satu per satu atau impor dari berkas CSV.">
            <x-slot:action>
                <x-ui.button :href="route('admin.students.create')"><x-icon.plus class="h-5 w-5" /> Tambah Siswa</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @endif
</div>
