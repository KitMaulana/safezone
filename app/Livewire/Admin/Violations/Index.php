<?php

namespace App\Livewire\Admin\Violations;

use App\Enums\ViolationCategory;
use App\Models\Student;
use App\Models\Violation;
use App\Services\AuditService;
use App\Services\PhotoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pelanggaran')]
class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    public ?int $editingId = null;

    public ?int $studentId = null;

    public string $form_category = 'tidak_pakai_helm';

    public string $description = '';

    public int $points = 0;

    public string $occurred_at = '';

    public $evidence = null;

    public function mount(): void
    {
        if ($student = request()->integer('student')) {
            $this->studentId = $student;
            $this->create();
        }
    }

    public function create(): void
    {
        $this->reset('editingId', 'description', 'evidence');
        $this->form_category = 'tidak_pakai_helm';
        $this->points = ViolationCategory::TidakPakaiHelm->defaultPoints();
        $this->occurred_at = now('Asia/Jakarta')->format('Y-m-d\TH:i');
        $this->resetValidation();
        $this->dispatch('open-modal', 'pelanggaran');
    }

    public function updatedFormCategory(string $value): void
    {
        $this->points = ViolationCategory::from($value)->defaultPoints();
    }

    public function edit(int $id): void
    {
        $violation = Violation::findOrFail($id);

        $this->editingId = $violation->id;
        $this->studentId = $violation->student_id;
        $this->form_category = $violation->category->value;
        $this->description = (string) $violation->description;
        $this->points = $violation->points;
        $this->occurred_at = $violation->occurred_at->timezone('Asia/Jakarta')->format('Y-m-d\TH:i');

        $this->resetValidation();
        $this->dispatch('open-modal', 'pelanggaran');
    }

    public function save(AuditService $audit, PhotoService $photos): void
    {
        $this->validate([
            'studentId' => ['required', 'exists:students,id'],
            'form_category' => ['required', 'in:'.implode(',', array_column(ViolationCategory::cases(), 'value'))],
            'description' => ['nullable', 'string', 'max:500'],
            'points' => ['required', 'integer', 'min:0', 'max:100'],
            'occurred_at' => ['required', 'date'],
            'evidence' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], attributes: [
            'studentId' => 'siswa', 'form_category' => 'kategori', 'description' => 'keterangan',
            'points' => 'poin', 'occurred_at' => 'waktu kejadian', 'evidence' => 'foto bukti',
        ]);

        $payload = [
            'student_id' => $this->studentId,
            'reported_by' => auth()->id(),
            'category' => $this->form_category,
            'description' => $this->description ?: null,
            'points' => $this->points,
            'occurred_at' => $this->occurred_at,
        ];

        $violation = $this->editingId
            ? tap(Violation::findOrFail($this->editingId))->update($payload)
            : Violation::create($payload);

        if ($this->evidence) {
            $violation->update([
                'evidence_photo_path' => $photos->store($this->evidence, 'violations', $violation->evidence_photo_path),
            ]);
        }

        $audit->log($this->editingId ? 'violation.update' : 'violation.create', $violation, null, $violation->only(['category', 'points']));

        $this->reset('editingId', 'description', 'evidence');
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Pelanggaran disimpan.');
    }

    public function delete(int $id, AuditService $audit): void
    {
        $violation = Violation::findOrFail($id);

        $audit->log('violation.delete', $violation, $violation->only(['category', 'points']));
        $violation->delete();

        $this->dispatch('toast', type: 'success', message: 'Pelanggaran dihapus.');
    }

    public function render()
    {
        $violations = Violation::query()
            ->with(['student:id,name,class_room', 'reporter:id,name'])
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';

                $q->whereHas('student', fn ($s) => $s->where('name', 'like', $term)->orWhere('class_room', 'like', $term));
            })
            ->latest('occurred_at')
            ->paginate(25);

        return view('livewire.admin.violations.index', [
            'violations' => $violations,
            'categories' => ViolationCategory::cases(),
            'students' => Student::orderBy('class_room')->orderBy('name')->get(['id', 'name', 'class_room']),
        ]);
    }
}
