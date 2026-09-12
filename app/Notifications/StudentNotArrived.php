<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class StudentNotArrived extends BaseSszNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Student $student, public string $time) {}

    public function emoji(): string
    {
        return '⏰';
    }

    public function title(object $notifiable): string
    {
        return 'Belum tercatat masuk';
    }

    public function body(object $notifiable): string
    {
        return 'Hingga pukul '.$this->time.', kendaraan Ananda '.$this->student->name.' belum tercatat masuk sekolah.';
    }

    public function url(object $notifiable): string
    {
        return url('/ortu/anak/'.$this->student->id);
    }

    public function tag(object $notifiable): string
    {
        return 'not-arrived-'.$this->student->id.'-'.now('Asia/Jakarta')->format('Ymd');
    }
}
