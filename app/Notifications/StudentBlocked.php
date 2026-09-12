<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class StudentBlocked extends BaseSszNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Student $student) {}

    public function emoji(): string
    {
        return '🚫';
    }

    public function title(object $notifiable): string
    {
        return 'Kartu masuk ditangguhkan';
    }

    public function body(object $notifiable): string
    {
        $until = $this->student->blocked_until
            ? ' Berlaku sampai '.$this->student->blocked_until->format('d M Y').'.'
            : '';

        return 'Kartu masuk kendaraan '.$this->student->name.' ditangguhkan: '.$this->student->blocked_reason.$until;
    }

    public function url(object $notifiable): string
    {
        return url('/');
    }

    public function tag(object $notifiable): string
    {
        return 'blocked-'.$this->student->id;
    }
}
