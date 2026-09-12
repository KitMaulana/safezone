<?php

namespace App\Console\Commands;

use App\Enums\ScanResultType;
use App\Models\AttendanceLog;
use App\Models\ScanAttempt;
use App\Models\User;
use App\Notifications\DailySummaryAdmin;
use Illuminate\Console\Command;

class DailySummary extends Command
{
    protected $signature = 'ssz:daily-summary';

    protected $description = 'Kirim ringkasan harian ke seluruh admin';

    public function handle(): int
    {
        $now = now('Asia/Jakarta');

        if ($now->isWeekend()) {
            return self::SUCCESS;
        }

        $today = $now->toDateString();

        $checkedIn = AttendanceLog::whereDate('scanned_at', $today)->where('type', 'in')->distinct('student_id')->count('student_id');
        $checkedOut = AttendanceLog::whereDate('scanned_at', $today)->where('type', 'out')->distinct('student_id')->count('student_id');
        $denied = ScanAttempt::whereDate('created_at', $today)->where('result', ScanResultType::DeniedBlocked->value)->count();

        $notification = new DailySummaryAdmin($checkedIn, max(0, $checkedIn - $checkedOut), $denied);

        foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
            $admin->notify($notification);
        }

        $this->info('Ringkasan harian terkirim.');

        return self::SUCCESS;
    }
}
