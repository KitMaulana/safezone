<?php

namespace App\Livewire\Admin\Students;

use App\Enums\PermitStatus;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use App\Services\AuditService;
use App\Services\PermitService;
use App\Services\SettingService;
use App\Services\StudentBlockService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
#[Title('Detail Siswa')]
class Show extends Component
{
    public Student $student;

    #[Url]
    public string $tab = 'profil';

    // Formulir blokir
    public string $blockReason = '';

    public string $blockType = 'pelanggaran';

    public ?string $blockUntil = null;

    // Formulir cabut stiker
    public ?int $revokePermitId = null;

    public string $revokeReason = '';

    public function mount(Student $student): void
    {
        $this->authorize('view', $student);

        $this->student = $student;
    }

    public function issuePermit(int $vehicleId, PermitService $permits): void
    {
        $this->authorize('update', $this->student);

        $vehicle = Vehicle::findOrFail($vehicleId);

        try {
            $permit = $permits->issue($vehicle, auth()->user());
            $this->dispatch('toast', type: 'success', message: 'Stiker '.$permit->permit_number.' diterbitkan.');
        } catch (RuntimeException $e) {
            $this->dispatch('toast', type: 'danger', message: $e->getMessage());
        }
    }

    public function activatePermit(int $permitId, PermitService $permits): void
    {
        $this->authorize('update', $this->student);

        $permits->activate(VehiclePermit::findOrFail($permitId));

        $this->dispatch('toast', type: 'success', message: 'Stiker diaktifkan.');
    }

    public function suspendPermit(int $permitId, PermitService $permits): void
    {
        $this->authorize('update', $this->student);

        $permits->suspend(VehiclePermit::findOrFail($permitId));

        $this->dispatch('toast', type: 'success', message: 'Stiker ditangguhkan.');
    }

    public function revokePermit(PermitService $permits): void
    {
        $this->authorize('update', $this->student);

        $this->validate([
            'revokePermitId' => ['required', 'integer'],
            'revokeReason' => ['required', 'string', 'min:5', 'max:500'],
        ], attributes: ['revokeReason' => 'alasan']);

        $permits->revoke(VehiclePermit::findOrFail($this->revokePermitId), $this->revokeReason);

        $this->reset('revokePermitId', 'revokeReason');
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Stiker dicabut.');
    }

    public function block(StudentBlockService $blocks): void
    {
        $this->authorize('block', $this->student);

        $this->validate([
            'blockReason' => ['required', 'string', 'min:5', 'max:500'],
            'blockType' => ['required', 'in:pelanggaran,administrasi,lainnya'],
            'blockUntil' => ['nullable', 'date', 'after:today'],
        ], attributes: ['blockReason' => 'alasan', 'blockType' => 'jenis', 'blockUntil' => 'tanggal berakhir']);

        $blocks->block($this->student, $this->blockReason, $this->blockType, $this->blockUntil, auth()->user());

        $this->student->refresh();
        $this->reset('blockReason', 'blockUntil');
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Siswa diblokir. Stiker aktifnya ditangguhkan.');
    }

    public function unblock(StudentBlockService $blocks): void
    {
        $this->authorize('block', $this->student);

        $blocks->unblock($this->student, auth()->user());

        $this->student->refresh();
        $this->dispatch('toast', type: 'success', message: 'Blokir dibuka. Stiker dipulihkan.');
    }

    public function deleteVehicle(int $vehicleId, AuditService $audit): void
    {
        $this->authorize('update', $this->student);

        $vehicle = Vehicle::findOrFail($vehicleId);
        $audit->log('vehicle.delete', $vehicle, $vehicle->only(['plate_number', 'brand', 'model']));
        $vehicle->delete();

        $this->dispatch('toast', type: 'success', message: 'Kendaraan dihapus.');
    }

    public function render(SettingService $settings)
    {
        $student = $this->student->load([
            'parents',
            'vehicles.permits' => fn ($q) => $q->orderByDesc('id'),
            'blockedBy:id,name',
        ]);

        return view('livewire.admin.students.show', [
            'student' => $student,
            'logs' => $student->attendanceLogs()->with('gate:id,name')->latest('scanned_at')->limit(30)->get(),
            'violations' => $student->violations()->with('reporter:id,name')->latest('occurred_at')->get(),
            'violationPoints' => $student->violations->sum('points'),
            'blockThreshold' => $settings->int('violation_block_threshold', 10),
            'openStatuses' => PermitStatus::openStatuses(),
            'isBlocked' => $student->status === StudentStatus::Blocked,
        ]);
    }
}
