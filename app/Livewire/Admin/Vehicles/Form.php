<?php

namespace App\Livewire\Admin\Vehicles;

use App\Models\Student;
use App\Models\Vehicle;
use App\Services\AuditService;
use App\Services\PhotoService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Formulir Kendaraan')]
class Form extends Component
{
    use WithFileUploads;

    public ?Vehicle $vehicle = null;

    public ?int $student_id = null;

    public string $plate_number = '';

    public string $brand = '';

    public string $model = '';

    public string $color = '';

    public ?string $year = null;

    public string $stnk_owner_name = '';

    public string $sim_number = '';

    public string $sim_type = 'tidak_ada';

    public array $requirements = [
        'surat_izin_ortu' => false,
        'fotokopi_stnk' => false,
        'fotokopi_sim' => false,
        'pernyataan_tata_tertib' => false,
    ];

    public string $notes = '';

    public bool $is_active = true;

    public $stnk_photo = null;

    public function mount(?Vehicle $vehicle = null): void
    {
        if ($vehicle?->exists) {
            $this->authorize('update', $vehicle);

            $this->vehicle = $vehicle;
            $this->student_id = $vehicle->student_id;
            $this->plate_number = $vehicle->formatted_plate;
            $this->brand = (string) $vehicle->brand;
            $this->model = (string) $vehicle->model;
            $this->color = (string) $vehicle->color;
            $this->year = $vehicle->year ? (string) $vehicle->year : null;
            $this->stnk_owner_name = (string) $vehicle->stnk_owner_name;
            $this->sim_number = (string) $vehicle->sim_number;
            $this->sim_type = $vehicle->sim_type;
            $this->requirements = array_merge($this->requirements, $vehicle->requirements ?? []);
            $this->notes = (string) $vehicle->notes;
            $this->is_active = $vehicle->is_active;
        } else {
            $this->authorize('create', Vehicle::class);

            $this->student_id = request()->integer('student') ?: null;
        }
    }

    protected function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'plate_number' => [
                'required', 'string', 'max:20',
                function ($attribute, $value, $fail) {
                    if (! preg_match(Vehicle::PLATE_REGEX, Vehicle::normalizePlate($value))) {
                        $fail('Format nomor polisi tidak valid. Contoh yang benar: A 1234 XY.');
                    }
                },
                Rule::unique('vehicles', 'plate_number')
                    ->ignore($this->vehicle?->id)
                    ->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'brand' => ['nullable', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'stnk_owner_name' => ['nullable', 'string', 'max:255'],
            'sim_number' => ['nullable', 'string', 'max:30'],
            'sim_type' => ['required', 'in:C,C1,tidak_ada'],
            'notes' => ['nullable', 'string', 'max:500'],
            'stnk_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** Nomor polisi dinormalisasi sebelum disimpan agar unik konsisten. */
    public function updatedPlateNumber(string $value): void
    {
        $this->plate_number = strtoupper($value);
    }

    public function save(AuditService $audit, PhotoService $photos)
    {
        $this->validate();

        $isNew = ! $this->vehicle?->exists;
        $before = $isNew ? null : $this->vehicle->only(['plate_number', 'brand', 'model', 'color', 'year', 'sim_type']);

        $payload = [
            'student_id' => $this->student_id,
            'plate_number' => $this->plate_number,
            'brand' => $this->brand ?: null,
            'model' => $this->model ?: null,
            'color' => $this->color ?: null,
            'year' => $this->year ?: null,
            'stnk_owner_name' => $this->stnk_owner_name ?: null,
            'sim_number' => $this->sim_number ?: null,
            'sim_type' => $this->sim_type,
            'requirements' => $this->requirements,
            'notes' => $this->notes ?: null,
            'is_active' => $this->is_active,
        ];

        $vehicle = $isNew ? Vehicle::create($payload) : tap($this->vehicle)->update($payload);

        if ($this->stnk_photo) {
            $vehicle->update([
                'stnk_photo_path' => $photos->store($this->stnk_photo, 'stnk', $vehicle->stnk_photo_path),
            ]);
        }

        $audit->log($isNew ? 'vehicle.create' : 'vehicle.update', $vehicle, $before, $vehicle->only(array_keys($payload)));

        session()->flash('success', $isNew ? 'Kendaraan ditambahkan.' : 'Data kendaraan diperbarui.');

        return $this->redirectRoute('admin.students.show', ['student' => $vehicle->student_id, 'tab' => 'kendaraan'], navigate: false);
    }

    public function render()
    {
        return view('livewire.admin.vehicles.form', [
            'students' => Student::orderBy('class_room')->orderBy('name')->get(['id', 'name', 'nisn', 'class_room']),
            'requirementLabels' => (new Vehicle)->requirement_labels,
        ]);
    }
}
