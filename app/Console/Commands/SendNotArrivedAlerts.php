<?php

namespace App\Console\Commands;

use App\Enums\PermitStatus;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Notifications\StudentNotArrived;
use App\Services\SettingService;
use Illuminate\Console\Command;

class SendNotArrivedAlerts extends Command
{
    protected $signature = 'ssz:not-arrived-alerts {--force : Abaikan pemeriksaan jam}';

    protected $description = 'Kirim pengingat ke orang tua bila anak belum tercatat masuk';

    public function handle(SettingService $settings): int
    {
        $now = now('Asia/Jakarta');

        if ($now->isWeekend()) {
            $this->info('Akhir pekan — pengingat dilewati.');

            return self::SUCCESS;
        }

        $alertTime = $settings->get('late_alert_time', '07:30');

        // Scheduler berjalan tiap menit; hanya kirim tepat pada menit yang diatur.
        if (! $this->option('force') && $now->format('H:i') !== $alertTime) {
            return self::SUCCESS;
        }

        $today = $now->toDateString();

        $checkedIn = AttendanceLog::whereDate('scanned_at', $today)
            ->where('type', 'in')
            ->pluck('student_id')
            ->unique();

        $students = Student::query()
            ->with('parents.user')
            ->active()
            ->whereNotIn('id', $checkedIn)
            ->whereHas('vehicles.permits', fn ($q) => $q->where('status', PermitStatus::Active->value))
            ->get();

        $sent = 0;

        foreach ($students as $student) {
            foreach ($student->parents->pluck('user')->filter() as $user) {
                $user->notify(new StudentNotArrived($student, str_replace(':', '.', $alertTime)));
                $sent++;
            }
        }

        $this->info("{$sent} pengingat dikirim untuk {$students->count()} siswa.");

        return self::SUCCESS;
    }
}
