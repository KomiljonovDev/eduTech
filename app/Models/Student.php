<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Get all phone numbers with their owners.
     *
     * @return array<int, array{number: string, owner: string|null}>
     */
    public function getAllPhones(): array
    {
        $phones = [];

        if ($this->phone) {
            $phones[] = ['number' => $this->phone, 'owner' => null];
        }

        if ($this->home_phone) {
            $phones[] = ['number' => $this->home_phone, 'owner' => 'Uy'];
        }

        foreach ($this->phones as $phone) {
            $phones[] = ['number' => $phone->number, 'owner' => $phone->owner];
        }

        return $phones;
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

    public function hasCompletedFoundation(): bool
    {
        return $this->enrollments()
            ->where('status', 'completed')
            ->whereHas('group.course', function ($query) {
                $query->where('code', 'KS');
            })
            ->exists();
    }

    public function completedCourses()
    {
        return Course::whereHas('groups.enrollments', function ($query) {
            $query->where('student_id', $this->id)
                ->where('status', 'completed');
        })->get();
    }

    public function isEnrolledIn(Group $group): bool
    {
        return $this->enrollments()->where('group_id', $group->id)->exists();
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class)
            ->withPivot(['valid_from', 'valid_until', 'notes'])
            ->withTimestamps();
    }

    public function activeDiscounts(): BelongsToMany
    {
        return $this->discounts()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('discount_student.valid_until')
                    ->orWhere('discount_student.valid_until', '>=', now());
            });
    }

    public function calculateTotalDiscount(float $amount): float
    {
        $totalDiscount = 0;

        foreach ($this->activeDiscounts as $discount) {
            $totalDiscount += $discount->calculateDiscount($amount);
        }

        return min($totalDiscount, $amount);
    }
}
