<?php

namespace App\Console\Commands;

use App\Enums\PermitStatus;
use App\Models\VehiclePermit;
use Illuminate\Console\Command;

class ExpirePermits extends Command
{
    protected $signature = 'ssz:expire-permits';

    protected $description = 'Tandai stiker yang sudah lewat masa berlaku menjadi kedaluwarsa';

    public function handle(): int
    {
        $count = VehiclePermit::query()
            ->whereIn('status', [PermitStatus::Active->value, PermitStatus::Printed->value, PermitStatus::Draft->value])
            ->whereDate('expires_at', '<', now('Asia/Jakarta')->toDateString())
            ->update(['status' => PermitStatus::Expired->value]);

        $this->info("{$count} stiker ditandai kedaluwarsa.");

        return self::SUCCESS;
    }
}
