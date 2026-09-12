<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Audit Log')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $action = '';

    #[Url]
    public string $userId = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.audit-logs.index', [
            'logs' => AuditLog::query()
                ->with('user:id,name,role')
                ->when($this->action, fn ($q) => $q->where('action', $this->action))
                ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
                ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
                ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
                ->latest('created_at')
                ->paginate(30),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'users' => User::whereIn('role', ['admin', 'petugas'])->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
