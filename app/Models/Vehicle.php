<?php

namespace App\Models;

use App\Enums\PermitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** Regex plat nomor Indonesia sesuai SPEC (setelah dinormalisasi). */
    public const PLATE_REGEX = '/^[A-Z]{1,2}[0-9]{1,4}[A-Z]{0,3}$/';

    protected $fillable = [
        'student_id', 'plate_number', 'brand', 'model', 'color', 'year',
        'stnk_owner_name', 'stnk_photo_path', 'sim_number', 'sim_type',
        'requirements', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'is_active' => 'boolean',
            'year' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function permits(): HasMany
    {
        return $this->hasMany(VehiclePermit::class);
    }

    public function activePermit()
    {
        return $this->hasOne(VehiclePermit::class)->whereIn('status', array_map(
            fn (PermitStatus $s) => $s->value,
            PermitStatus::openStatuses()
        ))->latestOfMany();
    }

    /** Disimpan uppercase tanpa spasi: "b 1234 xyz" -> "B1234XYZ". */
    public function setPlateNumberAttribute($value): void
    {
        $this->attributes['plate_number'] = self::normalizePlate($value);
    }

    public static function normalizePlate(?string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));
    }

    /** Ditampilkan berspasi: "B1234XYZ" -> "B 1234 XYZ". */
    public function getFormattedPlateAttribute(): string
    {
        return self::formatPlate($this->plate_number);
    }

    public static function formatPlate(?string $plate): string
    {
        if (blank($plate)) {
            return '-';
        }

        if (preg_match('/^([A-Z]{1,2})([0-9]{1,4})([A-Z]{0,3})$/', $plate, $m)) {
            return trim($m[1].' '.$m[2].' '.$m[3]);
        }

        return $plate;
    }

    public function getRequirementLabelsAttribute(): array
    {
        return [
            'surat_izin_ortu' => 'Surat izin orang tua',
            'fotokopi_stnk' => 'Fotokopi STNK',
            'fotokopi_sim' => 'Fotokopi SIM',
            'pernyataan_tata_tertib' => 'Pernyataan tata tertib',
        ];
    }
}
