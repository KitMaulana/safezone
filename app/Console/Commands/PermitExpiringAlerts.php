<?php

namespace App\Console\Commands;

use App\Enums\PermitStatus;
use App\Models\VehiclePermit;
use App\Notifications\PermitExpiring;
use Illuminate\Console\Command;

class PermitExpiringAlerts extends Command
{
    protected $signature = 'ssz:permit-expiring {--days=14 : Berapa hari sebelum kedaluwarsa}';

    protected $description = 'Ingatkan siswa & orang tua bahwa stiker akan kedaluwarsa';

    public function handle(): int
    {
        $target = now('Asia/Jakarta')->addDays((int) $this->option('days'))->toDateString();

        $permits = VehiclePermit::query()
            ->with('vehicle.student.parents.user', 'vehicle.student.user')
            ->where('status', PermitStatus::Active->value)
            ->whereDate('expires_at', $target)
            ->get();

        $sent = 0;

        foreach ($permits as $permit) {
            $student = $permit->vehicle->student;

            $recipients = collect([$student->user])
                ->merge($student->parents->pluck('user'))
                ->filter();

            foreach ($recipients as $user) {
                $user->notify(new PermitExpiring($permit));
                $sent++;
            }
        }

        $this->info("{$sent} pemberitahuan dikirim untuk {$permits->count()} stiker.");

        return self::SUCCESS;
    }
}
