<?php

namespace App\Notifications;

use App\Models\AttendanceLog;

class StudentEarlyLeave extends BaseSszNotification
{
    public function __construct(public AttendanceLog $log) {}

    public function emoji(): string
    {
        return '⚠️';
    }

    public function title(object $notifiable): string
    {
        return 'Keluar sekolah lebih awal';
    }

    public function body(object $notifiable): string
    {
        $time = $this->log->scanned_at->timezone('Asia/Jakarta')->format('H.i');

        return 'Ananda '.$this->log->student->name.' keluar sekolah pukul '.$time.' (lebih awal). Hubungi wali kelas bila tidak mengetahui.';
    }

    public function url(object $notifiable): string
    {
        return url('/ortu/anak/'.$this->log->student_id);
    }

    public function tag(object $notifiable): string
    {
        return 'early-'.$this->log->student_id.'-'.$this->log->scanned_at->format('Ymd');
    }
}
