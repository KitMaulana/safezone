<div class="space-y-4">
    @section('header', 'Siswa Diblokir')

    <p class="text-sm text-slate-500">{{ $students->total() }} siswa sedang diblokir</p>

    @if ($students->count())
        <x-ui.table :headers="['Siswa', 'Kelas', 'Alasan', 'Sejak', 'Berakhir', 'Aksi']">
            @foreach ($students as $student)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.students.show', $student) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $student->name }}</a>
                        <p class="text-xs text-slate-500">{{ $student->nisn }}</p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $student->class_room }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $student->blocked_reason }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $student->blocked_at?->timezone('Asia/Jakarta')->format('d M Y') ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if ($student->blocked_until)
                            <x-ui.badge color="warning">{{ $student->blocked_until->format('d M Y') }}</x-ui.badge>
                        @else
                            <x-ui.badge color="slate">Manual</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <x-ui.button size="sm" variant="success" wire:click="unblock({{ $student->id }})" wire:confirm="Buka blokir {{ $student->name }}?">
                            Buka Blokir
                        </x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div>{{ $students->links() }}</div>
    @else
        <x-ui.empty-state icon="check-circle" title="Tidak ada siswa diblokir" description="Semua siswa dapat menggunakan kartu masuknya." />
    @endif
</div>
