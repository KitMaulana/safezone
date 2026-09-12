<?php

namespace App\Notifications;

use App\Models\AttendanceLog;

class StudentCheckedOut extends BaseSszNotification
{
    public function __construct(public AttendanceLog $log) {}

    public function emoji(): string
    {
        return '🏠';
    }

    public function title(object $notifiable): string
    {
        return 'Sudah cek out';
    }

    public function body(object $notifiable): string
    {
        $time = $this->log->scanned_at->timezone('Asia/Jakarta')->format('H.i');

        return 'Ananda '.$this->log->student->name.' sudah cek out dari sekolah pukul '.$time.'.';
    }

    public function url(object $notifiable): string
    {
        return url('/ortu/anak/'.$this->log->student_id);
    }

    public function tag(object $notifiable): string
    {
        return 'checkout-'.$this->log->student_id.'-'.$this->log->scanned_at->format('Ymd');
    }
}
