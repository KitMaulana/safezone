<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentGuardian extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'user_id', 'name', 'phone', 'phone_alt', 'relationship', 'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function getRelationshipLabelAttribute(): string
    {
        return match ($this->relationship) {
            'ayah' => 'Ayah',
            'ibu' => 'Ibu',
            default => 'Wali',
        };
    }

    /** Normalisasi nomor HP Indonesia ke bentuk 08xxxxxxxxxx. */
    public static function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = self::normalizePhone($value);
    }

    public function setPhoneAltAttribute($value): void
    {
        $this->attributes['phone_alt'] = self::normalizePhone($value);
    }
}
