<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'lesson_number',
        'date',
        'present',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'present' => 'boolean',
            'lesson_number' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
