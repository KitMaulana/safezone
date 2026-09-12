<?php

namespace App\Models;

use App\Enums\ScanResultType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'qr_token_raw', 'permit_id', 'result', 'scanned_by', 'ip', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => ScanResultType::class,
            'created_at' => 'datetime',
        ];
    }

    public function permit(): BelongsTo
    {
        return $this->belongsTo(VehiclePermit::class, 'permit_id');
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
