<?php

namespace App\Livewire\Ortu;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
#[Title('Notifikasi')]
class Notifications extends Component
{
    use WithPagination;

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();

        $this->dispatch('toast', type: 'success', message: 'Semua notifikasi ditandai dibaca.');
    }

    public function render()
    {
        return view('livewire.ortu.notifications', [
            'items' => auth()->user()->notifications()->latest()->paginate(20),
            'unread' => auth()->user()->unreadNotifications()->count(),
        ]);
    }
}
