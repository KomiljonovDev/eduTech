<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all phone numbers (polymorphic relationship).
     */
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Get the primary phone number.
     */
    public function primaryPhone(): MorphOne
    {
        return $this->morphOne(Phone::class, 'phoneable')->where('is_primary', true);
    }

    /**
     * Get the home phone number.
     */
    public function homePhone(): MorphOne
    {
        return $this->morphOne(Phone::class, 'phoneable')->where('owner', 'Uy');
    }

    /**
     * Get extra phone numbers (non-primary, non-home).
     */
    public function extraPhones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'phoneable')
            ->where('is_primary', false)
            ->where(function ($query) {
                $query->whereNull('owner')
                    ->orWhere('owner', '!=', 'Uy');
            });
    }

    /**
     * Get all phone numbers with their owners.
     *
     * @return array<int, array{number: string, owner: string|null, is_primary: bool}>
     */
    public function getAllPhones(): array
    {
        return $this->phones->map(fn (Phone $phone) => [
            'number' => $phone->number,
            'owner' => $phone->owner,
            'is_primary' => $phone->is_primary,
        ])->toArray();
    }

    /**
     * Get the display phone number (primary phone or first available).
     */
    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->primaryPhone?->number ?? $this->phones->first()?->number;
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
