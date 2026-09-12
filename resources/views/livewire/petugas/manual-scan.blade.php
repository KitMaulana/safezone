<div class="space-y-4">
    @section('header', 'Pencatatan Manual')

    <x-ui.alert type="warning">
        Gunakan mode ini hanya bila QR rusak atau tidak terbaca. Alasan wajib diisi dan tercatat di log.
    </x-ui.alert>

    <x-ui.card padding="p-4">
        <x-ui.input wire:model.live.debounce.400ms="search" label="Cari siswa / kendaraan" placeholder="Nama, NISN, atau plat nomor…" />

        @if ($vehicles->count())
            <div class="mt-3 divide-y divide-slate-100 rounded-lg border border-slate-200">
                @foreach ($vehicles as $vehicle)
                    <button type="button" wire:click="select({{ $vehicle->id }})"
                            class="flex w-full items-center justify-between gap-3 px-3 py-3 text-left hover:bg-slate-50 {{ $selectedVehicleId === $vehicle->id ? 'bg-brand-50' : '' }}">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $vehicle->student->name }}</p>
                            <p class="text-xs text-slate-500">{{ $vehicle->student->class_room }} &middot; {{ $vehicle->student->nisn }}</p>
                        </div>
                        <span class="ssz-plate shrink-0 font-mono text-sm font-bold text-slate-700">{{ $vehicle->formatted_plate }}</span>
                    </button>
                @endforeach
            </div>
        @elseif (strlen($search) >= 2)
            <p class="mt-3 text-sm text-slate-500">Tidak ada hasil untuk "{{ $search }}".</p>
        @endif

        @error('selectedVehicleId') <p class="mt-2 text-xs font-medium text-danger-600">{{ $message }}</p> @enderror
    </x-ui.card>

    @if ($selected)
        <x-ui.card :title="'Catat untuk ' . $selected->student->name">
            <form wire:submit="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.select wire:model="mode" name="mode" label="Jenis" required>
                        <option value="in">Cek In</option>
                        <option value="out">Cek Out</option>
                    </x-ui.select>

                    <x-ui.select wire:model="gateId" name="gateId" label="Gerbang">
                        @foreach ($gates as $gate)
                            <option value="{{ $gate->id }}">{{ $gate->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.textarea wire:model="note" name="note" label="Alasan pencatatan manual" rows="3" required
                               placeholder="Contoh: QR pada stiker sobek dan tidak terbaca kamera." />

                <x-ui.button type="submit" size="lg" class="w-full">Catat sekarang</x-ui.button>
            </form>
        </x-ui.card>
    @endif

    @if ($result)
        <div class="rounded-xl p-4 text-center
            {{ $result['color'] === 'green' ? 'bg-success-50 text-success-700' : '' }}
            {{ $result['color'] === 'red' ? 'bg-danger-50 text-danger-700' : '' }}
            {{ $result['color'] === 'yellow' ? 'bg-warning-50 text-warning-700' : '' }}
            {{ $result['color'] === 'gray' ? 'bg-slate-100 text-slate-600' : '' }}">
            <p class="text-lg font-bold">{{ $result['message'] }}</p>
            @if ($result['reason'])
                <p class="mt-1 text-sm">{{ $result['reason'] }}</p>
            @endif
        </div>
    @endif
</div>
