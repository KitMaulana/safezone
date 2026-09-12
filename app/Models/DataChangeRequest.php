<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'field', 'old_value', 'new_value', 'status',
        'reviewed_by', 'reviewed_at', 'note',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Label ramah pengguna untuk kolom yang diajukan. */
    public function fieldLabel(): string
    {
        return match ($this->field) {
            'address' => 'Alamat siswa',
            'phone' => 'Nomor HP siswa',
            'parent_phone' => 'Nomor HP orang tua',
            'parent_name' => 'Nama orang tua',
            default => $this->field,
        };
    }
}
