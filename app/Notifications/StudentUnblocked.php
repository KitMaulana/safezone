<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class StudentUnblocked extends BaseSszNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Student $student) {}

    public function emoji(): string
    {
        return '✅';
    }

    public function title(object $notifiable): string
    {
        return 'Blokir dibuka';
    }

    public function body(object $notifiable): string
    {
        return 'Kartu masuk kendaraan '.$this->student->name.' sudah aktif kembali.';
    }

    public function url(object $notifiable): string
    {
        return url('/');
    }

    public function tag(object $notifiable): string
    {
        return 'unblocked-'.$this->student->id;
    }
}
