<?php

namespace App\Livewire\Admin\Blocks;

use App\Models\Student;
use App\Services\StudentBlockService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Siswa Diblokir')]
class Index extends Component
{
    use WithPagination;

    public function unblock(int $id, StudentBlockService $blocks): void
    {
        $student = Student::findOrFail($id);
        $this->authorize('block', $student);

        $blocks->unblock($student, auth()->user());

        $this->dispatch('toast', type: 'success', message: 'Blokir dibuka dan stiker dipulihkan.');
    }

    public function render()
    {
        return view('livewire.admin.blocks.index', [
            'students' => Student::blocked()
                ->with('blockedBy:id,name')
                ->orderByDesc('blocked_at')
                ->paginate(25),
        ]);
    }
}
