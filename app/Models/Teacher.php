<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherFactory> */
    use HasFactory;

    public const SALARY_TYPE_FIXED = 'fixed';

    public const SALARY_TYPE_PERCENT = 'percent';

    public const SALARY_TYPE_HYBRID = 'hybrid';

    protected $fillable = [
        'name',
        'phone',
        'payment_percentage',
        'salary_type',
        'fixed_salary',
        'is_active',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'payment_percentage' => 'decimal:2',
            'fixed_salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function activeGroups(): HasMany
    {
        return $this->groups()->whereIn('status', ['active', 'pending']);
    }

    public function teacherPayments(): HasMany
    {
        return $this->hasMany(TeacherPayment::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            Enrollment::class,
            'group_id',
            'enrollment_id',
            'id',
            'id'
        )->whereHas('enrollment', function ($query) {
            $query->whereIn('group_id', $this->groups()->pluck('id'));
        });
    }

    /**
     * Berilgan davr uchun oylik hisoblash
     */
    public function calculateMonthlyEarnings(string $period): float
    {
        $fixedAmount = 0;
        $percentAmount = 0;

        if (in_array($this->salary_type, [self::SALARY_TYPE_FIXED, self::SALARY_TYPE_HYBRID])) {
            $fixedAmount = (float) $this->fixed_salary;
        }

        if (in_array($this->salary_type, [self::SALARY_TYPE_PERCENT, self::SALARY_TYPE_HYBRID])) {
            $percentAmount = (float) Payment::query()
                ->whereHas('enrollment.group', function ($query) {
                    $query->where('teacher_id', $this->id);
                })
                ->where('period', $period)
                ->sum('teacher_share');
        }

        return $fixedAmount + $percentAmount;
    }

    /**
     * Berilgan davr uchun to'langan summa
     */
    public function getPaidAmount(string $period): float
    {
        return (float) $this->teacherPayments()
            ->where('period', $period)
            ->sum('amount');
    }

    /**
     * Berilgan davr uchun qarzdorlik
     */
    public function getDebt(string $period): float
    {
        return $this->calculateMonthlyEarnings($period) - $this->getPaidAmount($period);
    }

    /**
     * Salary type labels
     */
    public static function salaryTypes(): array
    {
        return [
            self::SALARY_TYPE_FIXED => 'Belgilangan',
            self::SALARY_TYPE_PERCENT => 'Foizli',
            self::SALARY_TYPE_HYBRID => 'Aralash',
        ];
    }
}
