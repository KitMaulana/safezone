<?php

namespace App\Livewire\Admin\Permits;

use App\Enums\PermitStatus;
use App\Models\Student;
use App\Models\VehiclePermit;
use App\Services\PermitService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.app')]
#[Title('Kendaraan & Stiker')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $classRoom = '';

    /** @var array<int, string> */
    public array $selected = [];

    public ?int $revokeId = null;

    public string $revokeReason = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'classRoom'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function activate(int $id, PermitService $permits): void
    {
        $this->authorize('manage', VehiclePermit::class);

        $permits->activate(VehiclePermit::findOrFail($id));
        $this->dispatch('toast', type: 'success', message: 'Stiker diaktifkan.');
    }

    public function suspend(int $id, PermitService $permits): void
    {
        $this->authorize('manage', VehiclePermit::class);

        $permits->suspend(VehiclePermit::findOrFail($id));
        $this->dispatch('toast', type: 'success', message: 'Stiker ditangguhkan.');
    }

    public function renew(int $id, PermitService $permits): void
    {
        $this->authorize('manage', VehiclePermit::class);

        try {
            $new = $permits->renew(VehiclePermit::with('vehicle')->findOrFail($id), auth()->user());
            $this->dispatch('toast', type: 'success', message: 'Stiker diperpanjang menjadi '.$new->permit_number.'.');
        } catch (RuntimeException $e) {
            $this->dispatch('toast', type: 'danger', message: $e->getMessage());
        }
    }

    public function revoke(PermitService $permits): void
    {
        $this->authorize('manage', VehiclePermit::class);

        $this->validate([
            'revokeId' => ['required', 'integer'],
            'revokeReason' => ['required', 'string', 'min:5', 'max:500'],
        ], attributes: ['revokeReason' => 'alasan']);

        $permits->revoke(VehiclePermit::findOrFail($this->revokeId), $this->revokeReason);

        $this->reset('revokeId', 'revokeReason');
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Stiker dicabut.');
    }

    public function render()
    {
        $permits = VehiclePermit::query()
            ->with(['vehicle.student:id,name,class_room,status'])
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(fn ($q) => $q
                    ->where('permit_number', 'like', $term)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', $term))
                    ->orWhereHas('vehicle.student', fn ($s) => $s->where('name', 'like', $term)));
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->classRoom, fn ($q) => $q->whereHas('vehicle.student', fn ($s) => $s->where('class_room', $this->classRoom)))
            ->orderByDesc('id')
            ->paginate(25);

        return view('livewire.admin.permits.index', [
            'permits' => $permits,
            'statuses' => PermitStatus::cases(),
            'classRooms' => Student::query()->distinct()->orderBy('class_room')->pluck('class_room'),
        ]);
    }
}
