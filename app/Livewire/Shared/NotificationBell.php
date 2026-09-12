<?php

namespace App\Livewire\Shared;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markRead(string $id): void
    {
        Auth::user()->notifications()->whereKey($id)->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.shared.notification-bell', [
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
            'items' => $user?->notifications()->latest()->limit(10)->get() ?? collect(),
        ]);
    }
}
