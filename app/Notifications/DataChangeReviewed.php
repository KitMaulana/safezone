<?php

namespace App\Notifications;

use App\Models\DataChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DataChangeReviewed extends BaseSszNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DataChangeRequest $request) {}

    public function emoji(): string
    {
        return $this->request->status === 'approved' ? '✅' : '❌';
    }

    public function title(object $notifiable): string
    {
        return $this->request->status === 'approved'
            ? 'Pengajuan perubahan disetujui'
            : 'Pengajuan perubahan ditolak';
    }

    public function body(object $notifiable): string
    {
        $note = filled($this->request->note) ? ' Catatan: '.$this->request->note : '';

        return 'Pengajuan perubahan '.$this->request->fieldLabel().' telah ditinjau admin.'.$note;
    }

    public function url(object $notifiable): string
    {
        return url('/siswa/profil');
    }

    public function tag(object $notifiable): string
    {
        return 'data-request-'.$this->request->id;
    }
}
