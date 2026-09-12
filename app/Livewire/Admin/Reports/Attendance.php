<?php

namespace App\Livewire\Admin\Reports;

use App\Enums\ScanResultType;
use App\Models\AttendanceLog;
use App\Models\ScanAttempt;
use App\Models\Student;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use App\Models\Violation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan')]
class Attendance extends Component
{
    #[Url]
    public string $tab = 'kehadiran';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $classRoom = '';

    public function mount(): void
    {
        $this->from = $this->from ?: now('Asia/Jakarta')->startOfMonth()->toDateString();
        $this->to = $this->to ?: now('Asia/Jakarta')->toDateString();
    }

    public function render()
    {
        return view('livewire.admin.reports.attendance', array_merge(
            ['classRooms' => Student::query()->distinct()->orderBy('class_room')->pluck('class_room')],
            match ($this->tab) {
                'kendaraan' => $this->vehicleReport(),
                'pelanggaran' => $this->violationReport(),
                'scan' => $this->scanAttemptReport(),
                default => $this->attendanceReport(),
            },
        ));
    }

    private function baseLogs()
    {
        return AttendanceLog::query()
            ->whereDate('scanned_at', '>=', $this->from)
            ->whereDate('scanned_at', '<=', $this->to)
            ->when($this->classRoom, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('class_room', $this->classRoom)));
    }

    private function attendanceReport(): array
    {
        $logs = $this->baseLogs()->with('student:id,name,class_room')->get();

        $ins = $logs->where('type', 'in');

        $avgMinutes = $ins->avg(function ($log) {
            $local = $log->scanned_at->timezone('Asia/Jakarta');

            return $local->hour * 60 + $local->minute;
        });

        $perClass = $logs->where('type', 'in')
            ->groupBy(fn ($log) => $log->student->class_room)
            ->map(fn ($group) => [
                'count' => $group->count(),
                'students' => $group->pluck('student_id')->unique()->count(),
            ])
            ->sortKeys();

        $trend = $ins->groupBy(fn ($log) => $log->scanned_at->timezone('Asia/Jakarta')->toDateString())
            ->map->count()
            ->sortKeys();

        return [
            'summary' => [
                'hadir' => $ins->pluck('student_id')->unique()->count(),
                'totalIn' => $ins->count(),
                'avgIn' => $avgMinutes ? sprintf('%02d.%02d', intdiv((int) $avgMinutes, 60), (int) $avgMinutes % 60) : '—',
                'earlyLeave' => $logs->where('type', 'out')->where('is_early_leave', true)->count(),
                'denied' => ScanAttempt::whereDate('created_at', '>=', $this->from)
                    ->whereDate('created_at', '<=', $this->to)
                    ->whereIn('result', [ScanResultType::DeniedBlocked->value, ScanResultType::DeniedExpired->value, ScanResultType::DeniedRevoked->value])
                    ->count(),
            ],
            'perClass' => $perClass,
            'trend' => $trend,
        ];
    }

    private function vehicleReport(): array
    {
        return [
            'vehiclesPerClass' => Vehicle::query()
                ->join('students', 'students.id', '=', 'vehicles.student_id')
                ->whereNull('vehicles.deleted_at')
                ->selectRaw('students.class_room, count(*) as total')
                ->groupBy('students.class_room')
                ->orderBy('students.class_room')
                ->pluck('total', 'class_room'),
            'permitsPerStatus' => VehiclePermit::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ];
    }

    private function violationReport(): array
    {
        $violations = Violation::query()
            ->with('student:id,class_room')
            ->whereDate('occurred_at', '>=', $this->from)
            ->whereDate('occurred_at', '<=', $this->to)
            ->get();

        return [
            'perCategory' => $violations->groupBy(fn ($v) => $v->category->label())->map->count()->sortDesc(),
            'perClassViolation' => $violations->groupBy(fn ($v) => $v->student->class_room)->map->count()->sortKeys(),
            'totalPoints' => $violations->sum('points'),
        ];
    }

    private function scanAttemptReport(): array
    {
        $attempts = ScanAttempt::query()
            ->whereDate('created_at', '>=', $this->from)
            ->whereDate('created_at', '<=', $this->to)
            ->get(['result', 'created_at']);

        return [
            'perResult' => $attempts->groupBy(fn ($a) => $a->result->label())->map->count()->sortDesc(),
            'perDay' => $attempts
                ->whereNotIn('result', [ScanResultType::Ok])
                ->groupBy(fn ($a) => $a->created_at->timezone('Asia/Jakarta')->toDateString())
                ->map->count()
                ->sortKeys(),
        ];
    }
}
