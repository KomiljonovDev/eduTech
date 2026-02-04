<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'home_phone',
        'course_id',
        'source',
        'status',
        'preferred_time',
        'notes',
        'contacted_at',
        'converted_student_id',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function convertToStudent(): Student
    {
        $student = Student::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'home_phone' => $this->home_phone,
            'source' => $this->source,
            'notes' => $this->notes,
        ]);

        $this->update([
            'status' => 'enrolled',
            'converted_student_id' => $student->id,
        ]);

        return $student;
    }
}
