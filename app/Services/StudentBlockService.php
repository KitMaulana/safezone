<?php

namespace App\Services;

use App\Enums\PermitStatus;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\User;
use App\Models\VehiclePermit;
use App\Notifications\StudentBlocked;
use App\Notifications\StudentUnblocked;
use Illuminate\Support\Facades\DB;

/**
 * Blokir & buka blokir siswa (SPEC §4.6).
 * Memblokir siswa otomatis menangguhkan seluruh stiker aktif miliknya.
 */
class StudentBlockService
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function block(Student $student, string $reason, string $type, ?string $until, User $actor): Student
    {
        DB::transaction(function () use ($student, $reason, $type, $until, $actor) {
            $before = $student->only(['status', 'blocked_reason', 'blocked_until']);

            $student->update([
                'status' => StudentStatus::Blocked,
                'blocked_reason' => $reason,
                'blocked_type' => $type,
                'blocked_at' => now(),
                'blocked_until' => $until ?: null,
                'blocked_by' => $actor->id,
            ]);

            $this->permitsOf($student)
                ->where('status', PermitStatus::Active->value)
                ->update(['status' => PermitStatus::Suspended->value]);

            $this->audit->log('student.block', $student, $before, [
                'status' => StudentStatus::Blocked->value,
                'reason' => $reason,
                'type' => $type,
                'until' => $until,
            ]);
        });

        $this->notify($student->fresh(), new StudentBlocked($student->fresh()));

        return $student;
    }

    public function unblock(Student $student, User $actor): Student
    {
        DB::transaction(function () use ($student) {
            $before = $student->only(['status', 'blocked_reason', 'blocked_until']);

            $student->update([
                'status' => StudentStatus::Active,
                'blocked_reason' => null,
                'blocked_type' => null,
                'blocked_at' => null,
                'blocked_until' => null,
                'blocked_by' => null,
            ]);

            // Stiker yang ditangguhkan dipulihkan, kecuali sudah lewat masa berlaku.
            $this->permitsOf($student)
                ->where('status', PermitStatus::Suspended->value)
                ->get()
                ->each(function (VehiclePermit $permit) {
                    $permit->update([
                        'status' => $permit->isExpired() ? PermitStatus::Expired : PermitStatus::Active,
                    ]);
                });

            $this->audit->log('student.unblock', $student, $before, ['status' => StudentStatus::Active->value]);
        });

        $this->notify($student->fresh(), new StudentUnblocked($student->fresh()));

        return $student;
    }

    private function permitsOf(Student $student)
    {
        return VehiclePermit::whereIn('vehicle_id', $student->vehicles()->pluck('id'));
    }

    /** Kirim ke akun siswa dan seluruh orang tuanya. */
    private function notify(Student $student, $notification): void
    {
        $student->loadMissing(['user', 'parents.user']);

        $recipients = collect([$student->user])
            ->merge($student->parents->pluck('user'))
            ->filter();

        foreach ($recipients as $user) {
            $user->notify($notification);
        }
    }
}
