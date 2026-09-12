<?php

namespace App\Livewire\Admin;

use App\Enums\PermitStatus;
use App\Enums\ScanResultType;
use App\Models\AttendanceLog;
use App\Models\DataChangeRequest;
use App\Models\ScanAttempt;
use App\Models\Student;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    public function render()
    {
        $today = now('Asia/Jakarta')->toDateString();

        $checkedInToday = AttendanceLog::whereDate('scanned_at', $today)
            ->where('type', 'in')
            ->distinct('student_id')
            ->count('student_id');

        $checkedOutToday = AttendanceLog::whereDate('scanned_at', $today)
            ->where('type', 'out')
            ->distinct('student_id')
            ->count('student_id');

        return view('livewire.admin.dashboard', [
            'stats' => [
                'vehicles' => Vehicle::count(),
                'activePermits' => VehiclePermit::active()->count(),
                'checkedIn' => $checkedInToday,
                'notCheckedOut' => max(0, $checkedInToday - $checkedOutToday),
                'deniedToday' => ScanAttempt::whereDate('created_at', $today)
                    ->where('result', ScanResultType::DeniedBlocked->value)
                    ->count(),
                'blockedStudents' => Student::blocked()->count(),
            ],
            'chart' => $this->weeklyChart(),
            'recentScans' => AttendanceLog::with(['student:id,name,class_room', 'gate:id,name'])
                ->latest('scanned_at')
                ->limit(10)
                ->get(),
            'expiringPermits' => VehiclePermit::with('vehicle.student:id,name,class_room')
                ->where('status', PermitStatus::Active->value)
                ->whereBetween('expires_at', [$today, now('Asia/Jakarta')->addDays(30)->toDateString()])
                ->orderBy('expires_at')
                ->limit(8)
                ->get(),
            'pendingRequests' => DataChangeRequest::where('status', 'pending')->count(),
            'blockedList' => Student::blocked()->select('id', 'name', 'class_room', 'blocked_reason', 'blocked_until')->limit(5)->get(),
        ]);
    }

    /** Jumlah cek in per hari selama 7 hari terakhir. */
    private function weeklyChart(): array
    {
        $start = now('Asia/Jakarta')->subDays(6)->startOfDay();

        $counts = AttendanceLog::where('type', 'in')
            ->where('scanned_at', '>=', $start->copy()->utc())
            ->get(['scanned_at'])
            ->groupBy(fn ($log) => $log->scanned_at->timezone('Asia/Jakarta')->toDateString())
            ->map->count();

        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now('Asia/Jakarta')->subDays($i);
            $days[] = [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('d M'),
                'value' => (int) ($counts[$date->toDateString()] ?? 0),
            ];
        }

        return $days;
    }
}
