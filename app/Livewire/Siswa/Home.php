<?php

namespace App\Livewire\Siswa;

use App\Models\AttendanceLog;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Beranda Siswa')]
class Home extends Component
{
    public function render()
    {
        $student = Student::with(['vehicles.permits' => fn ($q) => $q->orderByDesc('id')])
            ->where('user_id', auth()->id())
            ->first();

        abort_if(! $student, 404, 'Data siswa untuk akun ini belum tersedia. Hubungi admin sekolah.');

        $today = now('Asia/Jakarta')->toDateString();

        $todayLogs = AttendanceLog::where('student_id', $student->id)
            ->whereDate('scanned_at', $today)
            ->orderBy('scanned_at')
            ->get();

        $weekLogs = AttendanceLog::where('student_id', $student->id)
            ->where('type', 'in')
            ->where('scanned_at', '>=', now('Asia/Jakarta')->subDays(6)->startOfDay()->utc())
            ->get(['scanned_at']);

        $permit = $student->vehicles->flatMap->permits->first();

        return view('livewire.siswa.home', [
            'student' => $student,
            'permit' => $permit,
            'vehicle' => $student->vehicles->first(),
            'checkIn' => $todayLogs->firstWhere('type', 'in'),
            'checkOut' => $todayLogs->where('type', 'out')->last(),
            'weekCount' => $weekLogs->count(),
        ]);
    }
}
