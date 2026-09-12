<?php

namespace App\Livewire\Admin\Vehicles;

use App\Models\Student;
use App\Models\Vehicle;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kendaraan')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $classRoom = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'classRoom'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $vehicles = Vehicle::query()
            ->with(['student:id,name,class_room,status', 'permits' => fn ($q) => $q->orderByDesc('id')->limit(1)])
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';
                $plate = '%'.Vehicle::normalizePlate($this->search).'%';

                $query->where(fn ($q) => $q
                    ->where('plate_number', 'like', $plate)
                    ->orWhere('brand', 'like', $term)
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', $term)));
            })
            ->when($this->classRoom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_room', $this->classRoom)))
            ->latest('id')
            ->paginate(25);

        return view('livewire.admin.vehicles.index', [
            'vehicles' => $vehicles,
            'classRooms' => Student::query()->distinct()->orderBy('class_room')->pluck('class_room'),
        ]);
    }
}
