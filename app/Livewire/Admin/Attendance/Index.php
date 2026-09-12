<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\AttendanceLog;
use App\Models\Gate;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Log Kehadiran')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $classRoom = '';

    #[Url]
    public string $gateId = '';

    public function mount(): void
    {
        $this->from = $this->from ?: now('Asia/Jakarta')->subDays(6)->toDateString();
        $this->to = $this->to ?: now('Asia/Jakarta')->toDateString();
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = $this->query()->paginate(25);

        return view('livewire.admin.attendance.index', [
            'logs' => $logs,
            'classRooms' => Student::query()->distinct()->orderBy('class_room')->pluck('class_room'),
            'gates' => Gate::get(['id', 'name']),
        ]);
    }

    /** Query bersama antara tampilan dan ekspor. */
    public function query()
    {
        return AttendanceLog::query()
            ->with(['student:id,name,class_room,nisn', 'vehicle:id,plate_number', 'gate:id,name', 'scannedBy:id,name'])
            ->when($this->from, fn ($q) => $q->whereDate('scanned_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('scanned_at', '<=', $this->to))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->gateId, fn ($q) => $q->where('gate_id', $this->gateId))
            ->when($this->classRoom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_room', $this->classRoom)))
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';

                $q->whereHas('student', fn ($s) => $s->where('name', 'like', $term)->orWhere('nisn', 'like', $term));
            })
            ->orderByDesc('scanned_at');
    }
}
