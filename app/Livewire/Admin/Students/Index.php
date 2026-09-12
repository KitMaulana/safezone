<?php

namespace App\Livewire\Admin\Students;

use App\Enums\StudentStatus;
use App\Models\Student;
use App\Services\AuditService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Data Siswa')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $classRoom = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'classRoom'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'status', 'classRoom');
        $this->resetPage();
    }

    public function delete(int $id, AuditService $audit): void
    {
        $student = Student::findOrFail($id);
        $this->authorize('delete', $student);

        $audit->log('student.delete', $student, $student->only(['nisn', 'name', 'class_room']));

        $student->delete();

        $this->dispatch('toast', type: 'success', message: 'Data siswa dihapus.');
    }

    public function render()
    {
        $students = Student::query()
            ->with(['vehicles:id,student_id,plate_number'])
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('nisn', 'like', $term)
                    ->orWhere('nis', 'like', $term)
                    ->orWhere('class_room', 'like', $term));
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->classRoom, fn ($q) => $q->where('class_room', $this->classRoom))
            ->orderBy('class_room')
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.admin.students.index', [
            'students' => $students,
            'classRooms' => Student::query()->distinct()->orderBy('class_room')->pluck('class_room'),
            'statuses' => StudentStatus::cases(),
        ]);
    }
}
