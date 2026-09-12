<?php

namespace App\Models;

use App\Enums\PermitStatus;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehiclePermit extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'permit_number', 'qr_token', 'status', 'issued_at',
        'printed_at', 'activated_at', 'expires_at', 'revoked_at',
        'revoked_reason', 'issued_by', 'print_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => PermitStatus::class,
            'issued_at' => 'datetime',
            'printed_at' => 'datetime',
            'activated_at' => 'datetime',
            'revoked_at' => 'datetime',
            'expires_at' => 'date',
            'print_count' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'permit_id');
    }

    public function scanAttempts(): HasMany
    {
        return $this->hasMany(ScanAttempt::class, 'permit_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PermitStatus::Active->value)
            ->whereDate('expires_at', '>=', now('Asia/Jakarta')->toDateString());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->endOfDay()->isPast();
    }

    /** Stiker boleh dipakai untuk cek in: aktif, belum kedaluwarsa, siswa tidak diblokir. */
    public function isUsable(): bool
    {
        if ($this->status !== PermitStatus::Active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        return $this->vehicle?->student?->status !== StudentStatus::Blocked;
    }

    public function publicUrl(): string
    {
        return url('/q/'.$this->qr_token);
    }
}
