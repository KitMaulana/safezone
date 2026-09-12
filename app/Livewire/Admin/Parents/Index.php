<?php

namespace App\Livewire\Admin\Parents;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Services\AccountService;
use App\Services\AuditService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Orang Tua / Wali')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $phone_alt = '';

    public string $relationship = 'ayah';

    public string $address = '';

    /** @var array<int, int> */
    public array $studentIds = [];

    public ?int $primaryStudentId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'phone', 'phone_alt', 'address', 'studentIds', 'primaryStudentId');
        $this->relationship = 'ayah';
        $this->resetValidation();
        $this->dispatch('open-modal', 'ortu');
    }

    public function edit(int $id): void
    {
        $parent = ParentGuardian::with('students:id')->findOrFail($id);

        $this->editingId = $parent->id;
        $this->name = $parent->name;
        $this->phone = $parent->phone;
        $this->phone_alt = (string) $parent->phone_alt;
        $this->relationship = $parent->relationship;
        $this->address = (string) $parent->address;
        $this->studentIds = $parent->students->pluck('id')->all();
        $this->primaryStudentId = $parent->students->firstWhere('pivot.is_primary', true)?->id;

        $this->resetValidation();
        $this->dispatch('open-modal', 'ortu');
    }

    public function save(AccountService $accounts, AuditService $audit): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required', 'string', 'max:20',
                Rule::unique('parents', 'phone')->ignore($this->editingId)->whereNull('deleted_at'),
            ],
            'phone_alt' => ['nullable', 'string', 'max:20'],
            'relationship' => ['required', 'in:ayah,ibu,wali'],
            'address' => ['nullable', 'string', 'max:500'],
            'studentIds' => ['array'],
            'studentIds.*' => ['exists:students,id'],
        ], attributes: [
            'name' => 'nama', 'phone' => 'nomor HP', 'phone_alt' => 'nomor HP alternatif',
            'relationship' => 'hubungan', 'address' => 'alamat', 'studentIds' => 'anak',
        ]);

        $isNew = ! $this->editingId;

        $payload = [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'phone_alt' => $data['phone_alt'] ?: null,
            'relationship' => $data['relationship'],
            'address' => $data['address'] ?: null,
        ];

        $parent = $this->editingId
            ? tap(ParentGuardian::findOrFail($this->editingId))->update($payload)
            : ParentGuardian::create($payload);

        // Akun ortu otomatis: username = nomor HP, password = 6 digit terakhir.
        $accounts->ensureForParent($parent->fresh());

        $sync = [];

        foreach ($this->studentIds as $studentId) {
            $sync[(int) $studentId] = ['is_primary' => (int) $studentId === (int) $this->primaryStudentId];
        }

        $parent->students()->sync($sync);

        $audit->log($isNew ? 'parent.create' : 'parent.update', $parent, null, $parent->only(['name', 'phone', 'relationship']));

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: $isNew ? 'Orang tua ditambahkan.' : 'Data orang tua diperbarui.');
    }

    public function delete(int $id, AuditService $audit): void
    {
        $parent = ParentGuardian::findOrFail($id);

        $audit->log('parent.delete', $parent, $parent->only(['name', 'phone']));
        $parent->delete();

        $this->dispatch('toast', type: 'success', message: 'Data orang tua dihapus.');
    }

    public function render()
    {
        $parents = ParentGuardian::query()
            ->with('students:id,name,class_room')
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.admin.parents.index', [
            'parents' => $parents,
            'allStudents' => Student::orderBy('class_room')->orderBy('name')->get(['id', 'name', 'class_room']),
        ]);
    }
}
