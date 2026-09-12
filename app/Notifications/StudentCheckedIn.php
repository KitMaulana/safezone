<?php

namespace App\Notifications;

use App\Models\AttendanceLog;

class StudentCheckedIn extends BaseSszNotification
{
    public function __construct(public AttendanceLog $log) {}

    public function emoji(): string
    {
        return '✅';
    }

    public function title(object $notifiable): string
    {
        return 'Sudah tiba di sekolah';
    }

    public function body(object $notifiable): string
    {
        $time = $this->log->scanned_at->timezone('Asia/Jakarta')->format('H.i');
        $gate = $this->log->gate?->name ? ' ('.$this->log->gate->name.')' : '';

        return 'Ananda '.$this->log->student->name.' sudah tiba di sekolah pukul '.$time.$gate.'.';
    }

    public function url(object $notifiable): string
    {
        return url('/ortu/anak/'.$this->log->student_id);
    }

    public function tag(object $notifiable): string
    {
        return 'checkin-'.$this->log->student_id.'-'.$this->log->scanned_at->format('Ymd');
    }
}
