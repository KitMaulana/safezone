<?php

namespace App\Livewire\Admin\Students;

use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Services\AccountService;
use App\Services\AuditService;
use App\Services\PhotoService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Formulir Siswa')]
class Form extends Component
{
    use WithFileUploads;

    public ?Student $student = null;

    public string $nisn = '';

    public string $nis = '';

    public string $name = '';

    public string $gender = 'L';

    public ?string $birth_date = null;

    public string $class_room = '';

    public string $address = '';

    public string $phone = '';

    public string $status = 'active';

    public $photo = null;

    public function mount(?Student $student = null): void
    {
        if ($student?->exists) {
            $this->authorize('update', $student);

            $this->student = $student;
            $this->nisn = $student->nisn;
            $this->nis = (string) $student->nis;
            $this->name = $student->name;
            $this->gender = $student->gender;
            $this->birth_date = $student->birth_date?->format('Y-m-d');
            $this->class_room = $student->class_room;
            $this->address = (string) $student->address;
            $this->phone = (string) $student->phone;
            $this->status = $student->status->value;
        } else {
            $this->authorize('create', Student::class);
        }
    }

    protected function rules(): array
    {
        return [
            'nisn' => ['required', 'digits_between:6,10', Rule::unique('students', 'nisn')->ignore($this->student?->id)],
            'nis' => ['nullable', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'class_room' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,blocked,graduated,inactive'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nisn' => 'NISN', 'nis' => 'NIS', 'name' => 'nama', 'gender' => 'jenis kelamin',
            'birth_date' => 'tanggal lahir', 'class_room' => 'kelas', 'address' => 'alamat',
            'phone' => 'nomor HP', 'status' => 'status', 'photo' => 'foto',
        ];
    }

    public function save(AccountService $accounts, AuditService $audit, PhotoService $photos)
    {
        $data = $this->validate();

        $isNew = ! $this->student?->exists;
        $before = $isNew ? null : $this->student->only(array_keys($data));

        $payload = [
            'nisn' => $this->nisn,
            'nis' => $this->nis ?: null,
            'name' => $this->name,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date ?: null,
            'class_room' => $this->class_room,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'status' => $this->status,
        ];

        if ($isNew) {
            $payload['academic_year_id'] = AcademicYear::current()?->id;
            $student = Student::create($payload);
        } else {
            $student = $this->student;
            $student->update($payload);
        }

        if ($this->photo) {
            $student->update([
                'photo_path' => $photos->store($this->photo, 'students', $student->photo_path),
            ]);
        }

        // Akun siswa dibuat otomatis: username = NISN, password awal = tanggal lahir ddmmyyyy.
        if ($isNew) {
            $accounts->ensureForStudent($student->fresh());
        }

        $audit->log($isNew ? 'student.create' : 'student.update', $student, $before, $student->only(array_keys($payload)));

        session()->flash('success', $isNew
            ? 'Siswa ditambahkan. Akun login dibuat dengan username '.$student->nisn.'.'
            : 'Data siswa diperbarui.');

        return $this->redirectRoute('admin.students.show', $student, navigate: false);
    }

    public function render()
    {
        return view('livewire.admin.students.form', [
            'statuses' => StudentStatus::cases(),
        ]);
    }
}
