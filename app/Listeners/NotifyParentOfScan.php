<?php

namespace App\Listeners;

use App\Events\VehicleScanned;
use App\Notifications\StudentCheckedIn;
use App\Notifications\StudentCheckedOut;
use App\Notifications\StudentEarlyLeave;

/**
 * Mengirim notifikasi ke orang tua setiap kali anak cek in / cek out.
 * Sengaja dijalankan sinkron (tanpa ShouldQueue) agar push tiba seketika,
 * karena queue di shared hosting hanya berjalan tiap menit lewat cron.
 */
class NotifyParentOfScan
{
    public function handle(VehicleScanned $event): void
    {
        $log = $event->log->loadMissing(['student.parents.user', 'gate']);

        $recipients = $log->student->parents->pluck('user')->filter();

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = match (true) {
            $log->type === 'in' => new StudentCheckedIn($log),
            $log->is_early_leave => new StudentEarlyLeave($log),
            default => new StudentCheckedOut($log),
        };

        foreach ($recipients as $user) {
            $user->notify($notification);
        }
    }
}
