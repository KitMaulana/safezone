<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class SszBackup extends Command
{
    protected $signature = 'ssz:backup';

    protected $description = 'Ekspor basis data ke storage/app/backups';

    public function handle(BackupService $backups): int
    {
        $this->info('Cadangan dibuat: '.$backups->create());

        return self::SUCCESS;
    }
}
