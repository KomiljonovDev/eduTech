<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'next_contact_date',
        'converted_student_id',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'next_contact_date' => 'date',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('contacted_at');
    }

    public function getAttemptsCountAttribute(): int
    {
        return $this->activities()->count();
    }

    public function getLastActivityAttribute(): ?LeadActivity
    {
        return $this->activities()->first();
    }

    public function logActivity(
        string $outcome,
        ?string $notes = null,
        ?string $nextContactDate = null,
        ?string $phoneCalled = null,
        ?string $phoneOwner = null
    ): LeadActivity {
        $activity = $this->activities()->create([
            'user_id' => auth()->id(),
            'outcome' => $outcome,
            'phone_called' => $phoneCalled,
            'phone_owner' => $phoneOwner,
            'notes' => $notes,
            'next_contact_date' => $nextContactDate,
            'contacted_at' => now(),
        ]);

        $this->update([
            'contacted_at' => now(),
            'next_contact_date' => $nextContactDate,
        ]);

        return $activity;
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

        // Copy additional phones
        foreach ($this->phones as $phone) {
            $student->phones()->create([
                'number' => $phone->number,
                'owner' => $phone->owner,
            ]);
        }

        $this->update([
            'status' => 'enrolled',
            'converted_student_id' => $student->id,
        ]);

        return $student;
    }
}
