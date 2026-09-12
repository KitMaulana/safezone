<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use App\Services\StudentBlockService;
use Illuminate\Console\Command;

class AutoUnblock extends Command
{
    protected $signature = 'ssz:auto-unblock';

    protected $description = 'Buka blokir siswa yang tanggal berakhirnya sudah lewat';

    public function handle(StudentBlockService $blocks): int
    {
        $actor = User::where('role', 'admin')->first();

        $students = Student::blocked()
            ->whereNotNull('blocked_until')
            ->whereDate('blocked_until', '<', now('Asia/Jakarta')->toDateString())
            ->get();

        foreach ($students as $student) {
            $blocks->unblock($student, $actor ?? $student->user);
        }

        $this->info($students->count().' siswa dibuka blokirnya.');

        return self::SUCCESS;
    }
}
