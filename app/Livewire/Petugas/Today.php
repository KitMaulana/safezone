<?php

namespace App\Livewire\Petugas;

use App\Enums\ScanResultType;
use App\Enums\ViolationCategory;
use App\Models\AttendanceLog;
use App\Models\ScanAttempt;
use App\Models\Violation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Hari Ini')]
class Today extends Component
{
    public string $search = '';

    public ?int $violationStudentId = null;

    public string $violationCategory = 'tidak_pakai_helm';

    public string $violationDescription = '';

    public function openViolation(int $studentId): void
    {
        $this->violationStudentId = $studentId;
        $this->violationCategory = 'tidak_pakai_helm';
        $this->violationDescription = '';
        $this->resetValidation();
        $this->dispatch('open-modal', 'pelanggaran');
    }

    /** Petugas hanya boleh mencatat kategori ringan (SPEC Fase 7). */
    public function saveViolation(): void
    {
        $allowed = array_map(fn (ViolationCategory $c) => $c->value, ViolationCategory::lightCategories());

        $this->validate([
            'violationStudentId' => ['required', 'exists:students,id'],
            'violationCategory' => ['required', 'in:'.implode(',', $allowed)],
            'violationDescription' => ['required', 'string', 'min:5', 'max:500'],
        ], attributes: [
            'violationCategory' => 'kategori',
            'violationDescription' => 'keterangan',
        ]);

        $category = ViolationCategory::from($this->violationCategory);

        Violation::create([
            'student_id' => $this->violationStudentId,
            'reported_by' => auth()->id(),
            'category' => $category,
            'description' => $this->violationDescription,
            'points' => $category->defaultPoints(),
            'occurred_at' => now(),
        ]);

        $this->reset('violationStudentId', 'violationDescription');
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Pelanggaran dicatat.');
    }

    public function render()
    {
        $today = now('Asia/Jakarta')->toDateString();

        $logs = AttendanceLog::with(['student:id,name,class_room', 'vehicle:id,plate_number', 'gate:id,name'])
            ->whereDate('scanned_at', $today)
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(fn ($q) => $q
                    ->whereHas('student', fn ($s) => $s->where('name', 'like', $term)->orWhere('class_room', 'like', $term))
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', $term)));
            })
            ->orderBy('scanned_at')
            ->get();

        // Satu baris per siswa: jam masuk, jam keluar, status terkini.
        $rows = $logs->groupBy('student_id')->map(function ($studentLogs) {
            $first = $studentLogs->first();
            $in = $studentLogs->firstWhere('type', 'in');
            $out = $studentLogs->where('type', 'out')->last();
            $last = $studentLogs->last();

            return [
                'student' => $first->student,
                'plate' => $first->vehicle?->formatted_plate ?? '—',
                'in' => $in?->scanned_at,
                'out' => $out?->scanned_at,
                'gate' => $last->gate?->name,
                'status' => $last->type === 'in' ? 'Di sekolah' : 'Sudah pulang',
                'early' => (bool) $out?->is_early_leave,
            ];
        })->sortBy(fn ($row) => optional($row['in'])->timestamp ?? PHP_INT_MAX)->values();

        return view('livewire.petugas.today', [
            'rows' => $rows,
            'totalIn' => $rows->whereNotNull('in')->count(),
            'atSchool' => $rows->where('status', 'Di sekolah')->count(),
            'goneHome' => $rows->where('status', 'Sudah pulang')->count(),
            'denied' => ScanAttempt::whereDate('created_at', $today)
                ->whereIn('result', [ScanResultType::DeniedBlocked->value, ScanResultType::DeniedExpired->value, ScanResultType::DeniedRevoked->value])
                ->count(),
            'lightCategories' => ViolationCategory::lightCategories(),
        ]);
    }
}
