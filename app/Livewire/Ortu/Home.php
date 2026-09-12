<?php

namespace App\Livewire\Ortu;

use App\Models\AttendanceLog;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Beranda Orang Tua')]
class Home extends Component
{
    public ?int $selectedStudentId = null;

    public function mount(?int $student = null): void
    {
        $children = $this->children();

        abort_if($children->isEmpty(), 404, 'Belum ada data anak yang tertaut dengan akun ini. Hubungi admin sekolah.');

        $this->selectedStudentId = $student && $children->contains('id', $student)
            ? $student
            : $children->first()->id;
    }

    public function selectChild(int $id): void
    {
        if ($this->children()->contains('id', $id)) {
            $this->selectedStudentId = $id;
        }
    }

    /** @return Collection<int, Student> */
    private function children()
    {
        $parent = ParentGuardian::with('students')->where('user_id', auth()->id())->first();

        return $parent?->students ?? collect();
    }

    public function render()
    {
        $children = $this->children();
        $student = Student::with(['vehicles.permits' => fn ($q) => $q->orderByDesc('id')])
            ->find($this->selectedStudentId);

        $today = now('Asia/Jakarta')->toDateString();

        $todayLogs = $student
            ? AttendanceLog::where('student_id', $student->id)->whereDate('scanned_at', $today)->orderBy('scanned_at')->get()
            : collect();

        $checkIn = $todayLogs->firstWhere('type', 'in');
        $checkOut = $todayLogs->where('type', 'out')->last();

        $statusText = match (true) {
            $checkOut !== null => 'Sudah pulang '.$checkOut->scanned_at->timezone('Asia/Jakarta')->format('H.i'),
            $checkIn !== null => 'Sudah di sekolah sejak '.$checkIn->scanned_at->timezone('Asia/Jakarta')->format('H.i'),
            default => 'Belum cek in hari ini',
        };

        return view('livewire.ortu.home', [
            'children' => $children,
            'student' => $student,
            'vehicle' => $student?->vehicles->first(),
            'permit' => $student?->vehicles->flatMap->permits->first(),
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'statusText' => $statusText,
            'statusColor' => $checkOut ? 'slate' : ($checkIn ? 'success' : 'warning'),
        ]);
    }
}
