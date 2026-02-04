<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'home_phone',
        'address',
        'source',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function groups(): HasManyThrough
    {
        return $this->hasManyThrough(Group::class, Enrollment::class, 'student_id', 'id', 'id', 'group_id');
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Enrollment::class);
    }

    public function lead(): HasMany
    {
        return $this->hasMany(Lead::class, 'converted_student_id');
    }
}
