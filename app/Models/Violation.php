<?php

namespace App\Models;

use App\Enums\ViolationCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'reported_by', 'category', 'description',
        'points', 'occurred_at', 'evidence_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'category' => ViolationCategory::class,
            'occurred_at' => 'datetime',
            'points' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
