<?php

namespace App\Notifications;

use App\Models\VehiclePermit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PermitExpiring extends BaseSszNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public VehiclePermit $permit) {}

    public function emoji(): string
    {
        return '🗓️';
    }

    public function title(object $notifiable): string
    {
        return 'Stiker akan kedaluwarsa';
    }

    public function body(object $notifiable): string
    {
        return 'Stiker '.$this->permit->permit_number.' berlaku sampai '
            .$this->permit->expires_at->format('d M Y').'. Segera hubungi admin sekolah untuk perpanjangan.';
    }

    public function url(object $notifiable): string
    {
        return url('/');
    }

    public function tag(object $notifiable): string
    {
        return 'permit-expiring-'.$this->permit->id;
    }
}
