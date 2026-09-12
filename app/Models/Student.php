<?php

namespace App\Models;

use App\Enums\PermitStatus;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'nisn', 'nis', 'name', 'gender', 'birth_date', 'class_room',
        'academic_year_id', 'address', 'phone', 'photo_path', 'status',
        'blocked_reason', 'blocked_type', 'blocked_at', 'blocked_until', 'blocked_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'blocked_at' => 'datetime',
            'blocked_until' => 'date',
            'status' => StudentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentGuardian::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function permits(): HasManyThrough
    {
        return $this->hasManyThrough(VehiclePermit::class, Vehicle::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function dataChangeRequests(): HasMany
    {
        return $this->hasMany(DataChangeRequest::class);
    }

    /** Orang tua yang ditandai sebagai kontak utama (dicetak di stiker). */
    public function primaryParent(): ?ParentGuardian
    {
        return $this->parents->firstWhere('pivot.is_primary', true) ?? $this->parents->first();
    }

    public function activePermits()
    {
        return $this->permits()->where('vehicle_permits.status', PermitStatus::Active->value);
    }

    public function isBlocked(): bool
    {
        return $this->status === StudentStatus::Blocked;
    }

    public function violationPoints(): int
    {
        return (int) $this->violations()->sum('points');
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', StudentStatus::Blocked->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', StudentStatus::Active->value);
    }

    /** Semua akun yang harus menerima notifikasi tentang siswa ini. */
    public function notifiableParents()
    {
        return $this->parents->pluck('user')->filter();
    }
}
