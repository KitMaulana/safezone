<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'vehicle_id', 'permit_id', 'type', 'kind', 'scanned_at',
        'scanned_by', 'gate_id', 'device_info', 'note', 'pair_id',
        'is_early_leave', 'is_offline_sync', 'offline_id',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'is_early_leave' => 'boolean',
            'is_offline_sync' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function permit(): BelongsTo
    {
        return $this->belongsTo(VehiclePermit::class, 'permit_id');
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function gate(): BelongsTo
    {
        return $this->belongsTo(Gate::class);
    }

    public function pair(): BelongsTo
    {
        return $this->belongsTo(self::class, 'pair_id');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scanned_at', now('Asia/Jakarta')->toDateString());
    }

    public function typeLabel(): string
    {
        return $this->type === 'in' ? 'Cek In' : 'Cek Out';
    }

    public function kindLabel(): string
    {
        return match ($this->kind) {
            're_entry' => 'Masuk kembali',
            'manual' => 'Manual',
            default => 'Normal',
        };
    }
}
