<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutstandingDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'original_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'reason',
        'lessons_attended',
        'lessons_total',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'due_date' => 'date',
            'lessons_attended' => 'integer',
            'lessons_total' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student()
    {
        return $this->enrollment->student();
    }

    public function group()
    {
        return $this->enrollment->group();
    }

    /**
     * To'lov qabul qilish
     */
    public function recordPayment(float $amount, ?string $notes = null): void
    {
        $this->paid_amount += $amount;
        $this->remaining_amount = max(0, $this->original_amount - $this->paid_amount);

        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }

        if ($notes) {
            $this->notes = ($this->notes ? $this->notes."\n" : '').$notes;
        }

        $this->save();
    }

    /**
     * Qarzni kechirish
     */
    public function writeOff(?string $reason = null): void
    {
        $this->status = 'written_off';
        $this->notes = ($this->notes ? $this->notes."\n" : '').'Kechirildi: '.($reason ?? 'Sabab ko\'rsatilmagan');
        $this->save();
    }

    /**
     * O'qigan foizi
     */
    public function getAttendancePercentageAttribute(): float
    {
        if ($this->lessons_total <= 0) {
            return 0;
        }

        return round(($this->lessons_attended / $this->lessons_total) * 100, 1);
    }

    /**
     * Status labeli
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Kutilmoqda',
            'partial' => 'Qisman to\'langan',
            'paid' => 'To\'langan',
            'written_off' => 'Kechirilgan',
            default => $this->status,
        };
    }

    /**
     * Reason labeli
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'completed' => 'Kurs tugadi',
            'dropped' => 'Chetlashtirildi',
            'transferred' => 'Ko\'chirildi',
            default => $this->reason,
        };
    }

    /**
     * Scope: faqat ochiq qarzlar
     */
    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Qarz yaratish (enrollment asosida)
     */
    public static function createFromEnrollment(
        Enrollment $enrollment,
        string $reason,
        ?float $amount = null,
        ?int $lessonsAttended = null
    ): ?self {
        $enrollment->load(['student.activeDiscounts', 'group.course', 'payments', 'attendances']);

        $lessonsAttended = $lessonsAttended ?? $enrollment->attendances->where('present', true)->count();
        $lessonsTotal = $enrollment->group->total_lessons;
        $monthlyPrice = $enrollment->group->course->monthly_price;

        // Darslar bo'yicha hisoblash
        if ($lessonsTotal > 0) {
            $attendanceRatio = $lessonsAttended / $lessonsTotal;
        } else {
            $attendanceRatio = 1;
        }

        // Chegirmani hisobga olish
        $discount = $enrollment->student->calculateTotalDiscount($monthlyPrice);
        $netMonthlyPrice = $monthlyPrice - $discount;

        // Kurs davomiyligi (12 dars = 1 oy)
        $courseMonths = max(1, ceil($lessonsTotal / 12));
        $totalCoursePrice = $netMonthlyPrice * $courseMonths;

        // Qarz = (o'qigan darslar / jami darslar) × kurs narxi - to'langan
        if ($amount === null) {
            $requiredAmount = $totalCoursePrice * $attendanceRatio;
            $paidAmount = $enrollment->payments->sum('amount');
            $amount = max(0, $requiredAmount - $paidAmount);
        }

        // Agar qarz yo'q bo'lsa, yaratmaymiz
        if ($amount <= 0) {
            return null;
        }

        // Float qiymatlarni string ga aylantirish (brick/math deprecation fix)
        $amount = (string) round($amount, 2);

        return self::create([
            'enrollment_id' => $enrollment->id,
            'original_amount' => $amount,
            'paid_amount' => '0',
            'remaining_amount' => $amount,
            'status' => 'pending',
            'reason' => $reason,
            'lessons_attended' => $lessonsAttended,
            'lessons_total' => $lessonsTotal,
            'due_date' => now()->addDays(30),
        ]);
    }
}
