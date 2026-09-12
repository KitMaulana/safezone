<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Dasar seluruh notifikasi SSZ: tersimpan di lonceng in-app (database)
 * dan dikirim sebagai Web Push bila pengguna sudah berlangganan.
 */
abstract class BaseSszNotification extends Notification
{
    abstract public function emoji(): string;

    abstract public function title(object $notifiable): string;

    abstract public function body(object $notifiable): string;

    abstract public function url(object $notifiable): string;

    /** Tag unik agar notifikasi sejenis tidak menumpuk di HP. */
    abstract public function tag(object $notifiable): string;

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'emoji' => $this->emoji(),
            'title' => $this->title($notifiable),
            'body' => $this->body($notifiable),
            'url' => $this->url($notifiable),
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->emoji().' '.$this->title($notifiable))
            ->body($this->body($notifiable))
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-192.png')
            ->tag($this->tag($notifiable))
            ->data(['url' => $this->url($notifiable)])
            ->options(['TTL' => 3600]);
    }
}
