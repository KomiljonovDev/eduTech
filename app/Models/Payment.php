<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'amount',
        'teacher_share',
        'school_share',
        'paid_at',
        'period',
        'method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'teacher_share' => 'decimal:2',
            'school_share' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if ($payment->enrollment_id && (! $payment->teacher_share || ! $payment->school_share)) {
                $teacher = $payment->enrollment->group->teacher;

                // Fixed salary bo'lsa, teacher_share 0 (chunki ular alohida to'lanadi)
                if ($teacher->salary_type === Teacher::SALARY_TYPE_FIXED) {
                    $payment->teacher_share = 0;
                    $payment->school_share = $payment->amount;
                } else {
                    $percentage = $teacher->payment_percentage / 100;
                    $payment->teacher_share = $payment->amount * $percentage;
                    $payment->school_share = $payment->amount - $payment->teacher_share;
                }
            }
        });
    }
}
