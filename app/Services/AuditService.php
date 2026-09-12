<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /** Catat aksi sensitif: terbit/cabut stiker, blokir, ubah data siswa, reset password, cetak. */
    public function log(string $action, ?Model $subject = null, ?array $before = null, ?array $after = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'before' => $before,
            'after' => $after,
            'ip' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
