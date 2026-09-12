<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailySummaryAdmin extends BaseSszNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $checkedIn,
        public int $notCheckedOut,
        public int $denied,
    ) {}

    public function emoji(): string
    {
        return '📊';
    }

    public function title(object $notifiable): string
    {
        return 'Ringkasan hari ini';
    }

    public function body(object $notifiable): string
    {
        return 'Cek in: '.$this->checkedIn.' · Belum cek out: '.$this->notCheckedOut.' · Ditolak: '.$this->denied.'.';
    }

    public function url(object $notifiable): string
    {
        return url('/admin');
    }

    public function tag(object $notifiable): string
    {
        return 'daily-summary-'.now('Asia/Jakarta')->format('Ymd');
    }
}
