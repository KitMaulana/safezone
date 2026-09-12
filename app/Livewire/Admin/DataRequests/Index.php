<?php

namespace App\Livewire\Admin\DataRequests;

use App\Models\DataChangeRequest;
use App\Notifications\DataChangeReviewed;
use App\Services\AuditService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pengajuan Perubahan Data')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    public ?int $rejectId = null;

    public string $rejectNote = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /** Menyetujui langsung menerapkan nilai baru ke data siswa / orang tua. */
    public function approve(int $id, AuditService $audit): void
    {
        $request = DataChangeRequest::with('student.parents')->findOrFail($id);

        $student = $request->student;
        $parent = $student->primaryParent();

        match ($request->field) {
            'address' => $student->update(['address' => $request->new_value]),
            'phone' => $student->update(['phone' => $request->new_value]),
            'parent_phone' => $parent?->update(['phone_alt' => $request->new_value]),
            'parent_name' => $parent?->update(['name' => $request->new_value]),
            default => null,
        };

        $request->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $audit->log('data_request.approve', $request, ['old' => $request->old_value], ['new' => $request->new_value]);

        $student->user?->notify(new DataChangeReviewed($request->fresh()));

        $this->dispatch('toast', type: 'success', message: 'Pengajuan disetujui dan data diperbarui.');
    }

    public function openReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectNote = '';
        $this->resetValidation();
        $this->dispatch('open-modal', 'tolak');
    }

    public function reject(AuditService $audit): void
    {
        $this->validate([
            'rejectId' => ['required', 'integer'],
            'rejectNote' => ['required', 'string', 'min:5', 'max:500'],
        ], attributes: ['rejectNote' => 'catatan']);

        $request = DataChangeRequest::with('student.user')->findOrFail($this->rejectId);

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'note' => $this->rejectNote,
        ]);

        $audit->log('data_request.reject', $request, null, ['note' => $this->rejectNote]);

        $request->student->user?->notify(new DataChangeReviewed($request->fresh()));

        $this->reset('rejectId', 'rejectNote');
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Pengajuan ditolak.');
    }

    public function render()
    {
        return view('livewire.admin.data-requests.index', [
            'requests' => DataChangeRequest::query()
                ->with(['student:id,name,class_room', 'reviewer:id,name'])
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(25),
            'pendingCount' => DataChangeRequest::where('status', 'pending')->count(),
        ]);
    }
}
