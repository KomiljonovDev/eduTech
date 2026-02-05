<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPayment extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'amount',
        'paid_at',
        'period',
        'method',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * To'lov usullari
     */
    public static function methods(): array
    {
        return [
            'cash' => 'Naqd',
            'card' => 'Karta',
            'transfer' => 'O\'tkazma',
        ];
    }
}
