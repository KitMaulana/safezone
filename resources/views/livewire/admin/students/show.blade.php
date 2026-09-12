<div class="space-y-4">
    @section('header', 'Detail Siswa')

    <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
        <x-icon.arrow-left class="h-4 w-4" /> Kembali ke daftar siswa
    </a>

    @if (session('success'))
        <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
    @endif

    {{-- Kepala halaman --}}
    <x-ui.card>
        <div class="flex flex-wrap items-start gap-4">
            @if ($student->photo_path)
                <img src="{{ route('media', ['path' => $student->photo_path]) }}" alt="Foto {{ $student->name }}" class="h-20 w-20 rounded-xl object-cover">
            @else
                <x-ui.avatar :name="$student->name" size="xl" class="rounded-xl" />
            @endif

            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-bold text-slate-900">{{ $student->name }}</h2>
                <p class="text-sm text-slate-500">{{ $student->nisn }} &middot; {{ $student->class_room }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-ui.badge :color="$student->status->badgeColor()">{{ $student->status->label() }}</x-ui.badge>
                    @if ($violationPoints > 0)
                        <x-ui.badge :color="$violationPoints >= $blockThreshold ? 'danger' : 'warning'">{{ $violationPoints }} poin pelanggaran</x-ui.badge>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-ui.button variant="secondary" :href="route('admin.students.edit', $student)">
                    <x-icon.pencil class="h-4 w-4" /> Ubah
                </x-ui.button>
                @if ($isBlocked)
                    <x-ui.button variant="success" wire:click="unblock" wire:confirm="Buka blokir {{ $student->name }}?">
                        <x-icon.check class="h-4 w-4" /> Buka Blokir
                    </x-ui.button>
                @else
                    <x-ui.button variant="danger" x-on:click="$dispatch('open-modal', 'blokir')">
                        <x-icon.ban class="h-4 w-4" /> Blokir
                    </x-ui.button>
                @endif
            </div>
        </div>

        @if ($isBlocked)
            <x-ui.alert type="danger" class="mt-4" title="Siswa diblokir">
                {{ $student->blocked_reason }}
                <span class="mt-1 block text-xs">
                    Sejak {{ $student->blocked_at?->timezone('Asia/Jakarta')->format('d M Y · H.i') }}
                    @if ($student->blocked_until) &middot; berakhir {{ $student->blocked_until->format('d M Y') }} @else &middot; sampai dibuka manual @endif
                    @if ($student->blockedBy) &middot; oleh {{ $student->blockedBy->name }} @endif
                </span>
            </x-ui.alert>
        @elseif ($violationPoints >= $blockThreshold)
            <x-ui.alert type="warning" class="mt-4" title="Disarankan blokir">
                Total poin pelanggaran ({{ $violationPoints }}) sudah mencapai ambang batas {{ $blockThreshold }}.
                <button type="button" x-on:click="$dispatch('open-modal', 'blokir')" class="ml-1 font-semibold underline">Blokir sekarang</button>
            </x-ui.alert>
        @endif
    </x-ui.card>

    {{-- Tab --}}
    @php
        $tabs = [
            'profil' => 'Profil',
            'ortu' => 'Orang Tua',
            'kendaraan' => 'Kendaraan & Stiker',
            'riwayat' => 'Riwayat Kehadiran',
            'pelanggaran' => 'Pelanggaran',
        ];
    @endphp

    <div class="ssz-scroll-hide -mx-1 overflow-x-auto">
        <div class="flex gap-1 px-1">
            @foreach ($tabs as $key => $label)
                <button type="button" wire:click="$set('tab', '{{ $key }}')"
                        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition {{ $tab === $key ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Profil --}}
    @if ($tab === 'profil')
        <x-ui.card title="Data pribadi">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                @foreach ([
                    'NISN' => $student->nisn,
                    'NIS' => $student->nis ?: '—',
                    'Jenis kelamin' => $student->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                    'Tanggal lahir' => $student->birth_date?->format('d M Y') ?: '—',
                    'Kelas' => $student->class_room,
                    'No. HP siswa' => $student->phone ?: '—',
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                        <dd class="mt-0.5 text-sm text-slate-800">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Alamat</dt>
                    <dd class="mt-0.5 text-sm text-slate-800">{{ $student->address ?: '—' }}</dd>
                </div>
            </dl>
        </x-ui.card>
    @endif

    {{-- Orang tua --}}
    @if ($tab === 'ortu')
        <x-ui.card title="Orang tua / wali">
            <x-slot:actions>
                <x-ui.button size="sm" variant="secondary" :href="route('admin.parents.index')">Kelola orang tua</x-ui.button>
            </x-slot:actions>

            @forelse ($student->parents as $parent)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-50 py-3 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-slate-900">
                            {{ $parent->name }}
                            @if ($parent->pivot->is_primary)
                                <x-ui.badge color="brand" size="sm" class="ml-1">Kontak utama</x-ui.badge>
                            @endif
                        </p>
                        <p class="text-xs text-slate-500">{{ $parent->relationship_label }} &middot; {{ $parent->phone }}</p>
                    </div>
                    <a href="tel:{{ $parent->phone }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                        <x-icon.phone class="h-4 w-4" /> Telepon
                    </a>
                </div>
            @empty
                <p class="py-2 text-sm text-slate-500">Belum ada data orang tua yang tertaut.</p>
            @endforelse
        </x-ui.card>
    @endif

    {{-- Kendaraan & stiker --}}
    @if ($tab === 'kendaraan')
        <x-ui.card title="Kendaraan & stiker">
            <x-slot:actions>
                <x-ui.button size="sm" :href="route('admin.vehicles.create', ['student' => $student->id])">
                    <x-icon.plus class="h-4 w-4" /> Tambah kendaraan
                </x-ui.button>
            </x-slot:actions>

            @forelse ($student->vehicles as $vehicle)
                @php $permit = $vehicle->permits->first(); @endphp
                <div class="border-b border-slate-100 py-4 last:border-0 last:pb-0 first:pt-0">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="ssz-plate text-lg font-bold text-slate-900">{{ $vehicle->formatted_plate }}</p>
                            <p class="text-sm text-slate-600">{{ $vehicle->brand }} {{ $vehicle->model }} &middot; {{ $vehicle->color }} @if($vehicle->year) &middot; {{ $vehicle->year }} @endif</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                SIM: {{ $vehicle->sim_type === 'tidak_ada' ? 'Tidak ada' : $vehicle->sim_type.' — '.($vehicle->sim_number ?: 'nomor belum diisi') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <x-ui.button size="sm" variant="secondary" :href="route('admin.vehicles.edit', $vehicle)">
                                <x-icon.pencil class="h-4 w-4" /> Ubah
                            </x-ui.button>
                            <x-ui.button size="sm" variant="ghost"
                                         wire:click="deleteVehicle({{ $vehicle->id }})"
                                         wire:confirm="Hapus kendaraan {{ $vehicle->formatted_plate }}?">
                                <x-icon.trash class="h-4 w-4" />
                            </x-ui.button>
                        </div>
                    </div>

                    {{-- Kelengkapan syarat --}}
                    @if ($vehicle->requirements)
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($vehicle->requirement_labels as $key => $label)
                                <x-ui.badge size="sm" :color="($vehicle->requirements[$key] ?? false) ? 'success' : 'slate'">
                                    {{ ($vehicle->requirements[$key] ?? false) ? '✓' : '○' }} {{ $label }}
                                </x-ui.badge>
                            @endforeach
                        </div>
                    @endif

                    {{-- Stiker --}}
                    <div class="mt-3 rounded-lg bg-slate-50 p-3">
                        @if ($permit)
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $permit->permit_number }}</p>
                                    <p class="text-xs text-slate-500">Berlaku s.d. {{ $permit->expires_at->format('d M Y') }} &middot; dicetak {{ $permit->print_count }}×</p>
                                </div>
                                <x-ui.badge :color="$permit->status->badgeColor()">{{ $permit->status->label() }}</x-ui.badge>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <x-ui.button size="sm" :href="route('admin.stiker.single', $permit)" target="_blank">
                                    <x-icon.printer class="h-4 w-4" /> Cetak
                                </x-ui.button>
                                <x-ui.button size="sm" variant="secondary" :href="route('admin.stiker.preview', $permit)" target="_blank">
                                    <x-icon.eye class="h-4 w-4" /> Pratinjau
                                </x-ui.button>
                                @if (in_array($permit->status, $openStatuses, true) && $permit->status->value !== 'active')
                                    <x-ui.button size="sm" variant="success" wire:click="activatePermit({{ $permit->id }})">Aktifkan</x-ui.button>
                                @endif
                                @if ($permit->status->value === 'active')
                                    <x-ui.button size="sm" variant="warning" wire:click="suspendPermit({{ $permit->id }})">Tangguhkan</x-ui.button>
                                @endif
                                @if (in_array($permit->status, $openStatuses, true))
                                    <x-ui.button size="sm" variant="danger"
                                                 x-on:click="$wire.set('revokePermitId', {{ $permit->id }}); $dispatch('open-modal', 'cabut')">
                                        Cabut
                                    </x-ui.button>
                                @endif
                            </div>
                        @else
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm text-slate-600">Kendaraan ini belum memiliki stiker.</p>
                                <x-ui.button size="sm" wire:click="issuePermit({{ $vehicle->id }})">
                                    <x-icon.qr-code class="h-4 w-4" /> Terbitkan Stiker
                                </x-ui.button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="py-2 text-sm text-slate-500">Siswa ini belum mendaftarkan kendaraan.</p>
            @endforelse
        </x-ui.card>
    @endif

    {{-- Riwayat kehadiran --}}
    @if ($tab === 'riwayat')
        <x-ui.card title="30 catatan terakhir" padding="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Gerbang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->scanned_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :color="$log->type === 'in' ? 'success' : 'brand'">{{ $log->typeLabel() }}</x-ui.badge>
                                    @if ($log->kind !== 'normal')
                                        <x-ui.badge color="slate" size="sm" class="ml-1">{{ $log->kindLabel() }}</x-ui.badge>
                                    @endif
                                    @if ($log->is_early_leave)
                                        <x-ui.badge color="warning" size="sm" class="ml-1">Lebih awal</x-ui.badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $log->gate?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $log->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat kehadiran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif

    {{-- Pelanggaran --}}
    @if ($tab === 'pelanggaran')
        <x-ui.card title="Pelanggaran" :subtitle="'Total ' . $violationPoints . ' poin (ambang blokir: ' . $blockThreshold . ')'">
            <x-slot:actions>
                <x-ui.button size="sm" :href="route('admin.violations.create', ['student' => $student->id])">
                    <x-icon.plus class="h-4 w-4" /> Catat pelanggaran
                </x-ui.button>
            </x-slot:actions>

            @forelse ($violations as $violation)
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-50 py-3 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-900">{{ $violation->category->label() }}</p>
                        <p class="text-xs text-slate-600">{{ $violation->description ?: '—' }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ $violation->occurred_at->timezone('Asia/Jakarta')->format('d M Y · H.i') }}
                            @if ($violation->reporter) &middot; dicatat {{ $violation->reporter->name }} @endif
                        </p>
                    </div>
                    <x-ui.badge color="warning">{{ $violation->points }} poin</x-ui.badge>
                </div>
            @empty
                <p class="py-2 text-sm text-slate-500">Tidak ada catatan pelanggaran.</p>
            @endforelse
        </x-ui.card>
    @endif

    {{-- Modal blokir --}}
    <x-ui.modal name="blokir" title="Blokir siswa">
        <form wire:submit="block" class="space-y-4">
            <x-ui.alert type="warning">
                Memblokir siswa akan menangguhkan seluruh stiker aktifnya. Saat dipindai petugas, layar akan menampilkan status MERAH.
            </x-ui.alert>

            <x-ui.select wire:model="blockType" name="blockType" label="Jenis blokir" required>
                <option value="pelanggaran">Pelanggaran</option>
                <option value="administrasi">Administrasi</option>
                <option value="lainnya">Lainnya</option>
            </x-ui.select>

            <x-ui.textarea wire:model="blockReason" name="blockReason" label="Alasan" rows="3" required
                           placeholder="Contoh: Berulang kali tidak memakai helm di area sekolah." />

            <x-ui.input wire:model="blockUntil" name="blockUntil" type="date" label="Berakhir pada"
                        hint="Kosongkan bila blokir berlaku sampai dibuka manual." />

            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit" variant="danger">Blokir siswa</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal cabut stiker --}}
    <x-ui.modal name="cabut" title="Cabut stiker">
        <form wire:submit="revokePermit" class="space-y-4">
            <x-ui.textarea wire:model="revokeReason" name="revokeReason" label="Alasan pencabutan" rows="3" required />

            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-ui.button>
                <x-ui.button type="submit" variant="danger">Cabut stiker</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
