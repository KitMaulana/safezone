<?php

namespace App\Livewire\Petugas;

use App\Models\Gate;
use App\Models\Vehicle;
use App\Services\ScanService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Pencatatan Manual')]
class ManualScan extends Component
{
    public string $search = '';

    public ?int $selectedVehicleId = null;

    public string $mode = 'in';

    public string $note = '';

    public ?array $result = null;

    public ?int $gateId = null;

    public function mount(): void
    {
        $this->gateId = Gate::where('is_active', true)->value('id');
    }

    public function select(int $vehicleId): void
    {
        $this->selectedVehicleId = $vehicleId;
        $this->result = null;
        $this->resetValidation();
    }

    public function submit(ScanService $scans): void
    {
        $this->validate([
            'selectedVehicleId' => ['required', 'exists:vehicles,id'],
            'mode' => ['required', 'in:in,out'],
            'note' => ['required', 'string', 'min:5', 'max:500'],
            'gateId' => ['nullable', 'exists:gates,id'],
        ], attributes: [
            'selectedVehicleId' => 'kendaraan',
            'note' => 'alasan',
        ]);

        $vehicle = Vehicle::with('permits')->findOrFail($this->selectedVehicleId);
        $permit = $vehicle->permits()->orderByDesc('id')->first();

        if (! $permit) {
            $this->dispatch('toast', type: 'danger', message: 'Kendaraan ini belum memiliki stiker.');

            return;
        }

        // Mode manual tetap melalui ScanService agar semua aturan & pencatatan konsisten.
        $result = $scans->handle(
            token: $permit->qr_token,
            officer: auth()->user(),
            gate: $this->gateId ? Gate::find($this->gateId) : null,
            mode: 'manual',
            note: $this->note,
        );

        // ScanService menentukan sendiri in/out; untuk manual, hormati pilihan petugas.
        if ($result->isOk() && $result->log->type !== $this->mode) {
            $result->log->update(['type' => $this->mode]);
        }

        $this->result = $result->toArray();
        $this->reset('note');

        $this->dispatch('toast',
            type: $result->isOk() ? 'success' : 'warning',
            message: $result->message
        );
    }

    public function render()
    {
        $vehicles = collect();

        if (strlen($this->search) >= 2) {
            $term = '%'.$this->search.'%';
            $plate = '%'.Vehicle::normalizePlate($this->search).'%';

            $vehicles = Vehicle::with(['student:id,name,class_room,nisn,status'])
                ->where(fn ($q) => $q
                    ->where('plate_number', 'like', $plate)
                    ->orWhereHas('student', fn ($s) => $s
                        ->where('name', 'like', $term)
                        ->orWhere('nisn', 'like', $term)))
                ->limit(15)
                ->get();
        }

        return view('livewire.petugas.manual-scan', [
            'vehicles' => $vehicles,
            'selected' => $this->selectedVehicleId
                ? Vehicle::with('student:id,name,class_room')->find($this->selectedVehicleId)
                : null,
            'gates' => Gate::where('is_active', true)->get(['id', 'name']),
        ]);
    }
}
