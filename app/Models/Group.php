<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'course_id',
        'teacher_id',
        'room_id',
        'days',
        'start_time',
        'end_time',
        'total_lessons',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_lessons' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, Enrollment::class, 'group_id', 'id', 'id', 'student_id');
    }

    public function attendances(): HasManyThrough
    {
        return $this->hasManyThrough(Attendance::class, Enrollment::class);
    }

    public function getDaysLabelAttribute(): string
    {
        return $this->days === 'odd' ? 'Du-Chor-Jum' : 'Se-Pay-Shan';
    }

    public function getScheduleLabelAttribute(): string
    {
        $start = $this->start_time?->format('H:i') ?? '';
        $end = $this->end_time?->format('H:i') ?? '';

        return "{$this->days_label} | {$start}-{$end}";
    }
}
