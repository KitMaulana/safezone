<?php

namespace App\Livewire\Ortu;

use App\Models\AttendanceLog;
use App\Models\ParentGuardian;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Riwayat Anak')]
class History extends Component
{
    public ?int $studentId = null;

    public string $month = '';

    public function mount(?int $student = null): void
    {
        $children = $this->children();

        abort_if($children->isEmpty(), 404, 'Belum ada data anak yang tertaut dengan akun ini.');

        $this->studentId = $student && $children->contains('id', $student) ? $student : $children->first()->id;
        $this->month = now('Asia/Jakarta')->format('Y-m');
    }

    private function children()
    {
        return ParentGuardian::with('students')->where('user_id', auth()->id())->first()?->students ?? collect();
    }

    public function render()
    {
        [$year, $month] = array_pad(explode('-', $this->month), 2, null);

        $logs = AttendanceLog::with('gate:id,name')
            ->where('student_id', $this->studentId)
            ->whereYear('scanned_at', $year)
            ->whereMonth('scanned_at', $month)
            ->orderByDesc('scanned_at')
            ->get();

        // Kelompokkan per hari: jam masuk, jam keluar, dan durasi di sekolah.
        $days = $logs->groupBy(fn ($log) => $log->scanned_at->timezone('Asia/Jakarta')->toDateString())
            ->map(function ($dayLogs, $date) {
                $in = $dayLogs->where('type', 'in')->sortBy('scanned_at')->first();
                $out = $dayLogs->where('type', 'out')->sortBy('scanned_at')->last();

                return [
                    'date' => $date,
                    'in' => $in?->scanned_at,
                    'out' => $out?->scanned_at,
                    'early' => (bool) $out?->is_early_leave,
                    'duration' => $in && $out ? $in->scanned_at->diff($out->scanned_at)->format('%hj %im') : null,
                ];
            })
            ->sortKeysDesc()
            ->values();

        return view('livewire.ortu.history', [
            'children' => $this->children(),
            'days' => $days,
            'months' => collect(range(0, 5))->map(fn ($i) => [
                'value' => now('Asia/Jakarta')->subMonths($i)->format('Y-m'),
                'label' => now('Asia/Jakarta')->subMonths($i)->translatedFormat('F Y'),
            ]),
        ]);
    }
}
