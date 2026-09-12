<?php

namespace App\Livewire\Siswa;

use App\Models\AttendanceLog;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
#[Title('Riwayat')]
class History extends Component
{
    use WithPagination;

    public string $month = '';

    public function mount(): void
    {
        $this->month = now('Asia/Jakarta')->format('Y-m');
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        [$year, $month] = array_pad(explode('-', $this->month), 2, null);

        $logs = AttendanceLog::with('gate:id,name')
            ->where('student_id', $student->id)
            ->when($year && $month, function ($q) use ($year, $month) {
                $q->whereYear('scanned_at', $year)->whereMonth('scanned_at', $month);
            })
            ->where('scanned_at', '>=', now('Asia/Jakarta')->subDays(60)->startOfDay()->utc())
            ->latest('scanned_at')
            ->paginate(30);

        return view('livewire.siswa.history', [
            'student' => $student,
            'logs' => $logs,
            'months' => collect(range(0, 3))->map(fn ($i) => [
                'value' => now('Asia/Jakarta')->subMonths($i)->format('Y-m'),
                'label' => now('Asia/Jakarta')->subMonths($i)->translatedFormat('F Y'),
            ]),
        ]);
    }
}
