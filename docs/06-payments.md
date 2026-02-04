# To'lovlar Moduli

Talabalardan to'lov qabul qilish, o'qituvchi va markaz ulushini hisoblash.

## Model

**Fayl**: `app/Models/Payment.php`
**Jadval**: `payments`

## Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| enrollment_id | foreignId | Enrollment ID |
| amount | decimal(12,2) | To'lov summasi |
| teacher_share | decimal(12,2) | O'qituvchi ulushi |
| school_share | decimal(12,2) | Markaz ulushi |
| paid_at | datetime | To'langan vaqt |
| period | string | Davr (YYYY-MM) |
| method | enum | To'lov usuli |
| notes | text, nullable | Izohlar |

## To'lov Usullari (Method)

| Qiymat | Tavsif |
|--------|--------|
| `cash` | Naqd pul |
| `card` | Plastik karta |
| `transfer` | Bank o'tkazmasi |

## Ulush Avtomatik Hisoblash

Payment yaratilganda `teacher_share` va `school_share` avtomatik hisoblanadi:

```php
// Payment.php - boot method
protected static function boot()
{
    parent::boot();

    static::creating(function (Payment $payment) {
        $teacher = $payment->enrollment->group->teacher;
        $percentage = $teacher->payment_percentage ?? 50;

        $payment->teacher_share = $payment->amount * ($percentage / 100);
        $payment->school_share = $payment->amount - $payment->teacher_share;
    });
}
```

### Hisoblash Formulasi

```
teacher_share = amount × (teacher.payment_percentage / 100)
school_share = amount - teacher_share

Misol: amount = 500,000, percentage = 50%
- teacher_share = 500,000 × 0.5 = 250,000
- school_share = 500,000 - 250,000 = 250,000
```

## To'lov Qabul Qilish

### GroupDetail.php da

```php
// Modal ochish
public function openPaymentModal(Enrollment $enrollment): void
{
    $this->paymentEnrollmentId = $enrollment->id;
    $status = $this->getPaymentStatusForPeriod($enrollment);

    // Qarz bo'lsa qarz summasini, bo'lmasa to'liq narxni ko'rsat
    $this->amount = $status['remaining'] > 0
        ? (string) $status['remaining']
        : (string) $status['required'];

    $this->method = 'cash';
    $this->payment_notes = '';
    $this->showPaymentModal = true;
}

// To'lov yaratish
public function collectPayment(): void
{
    $this->validate([
        'amount' => 'required|numeric|min:1000',
        'method' => 'required|in:cash,card,transfer',
        'period' => 'required|date_format:Y-m',
    ]);

    Payment::create([
        'enrollment_id' => $this->paymentEnrollmentId,
        'amount' => $this->amount,
        'paid_at' => now(),
        'period' => $this->period,
        'method' => $this->method,
        'notes' => $this->payment_notes ?: null,
    ]);

    $this->showPaymentModal = false;
    $this->reset(['paymentEnrollmentId', 'amount', 'payment_notes']);
    $this->dispatch('payment-collected');
}
```

## To'lov Holati Hisoblash

```php
public function getPaymentStatusForPeriod(Enrollment $enrollment, ?string $period = null): array
{
    $period = $period ?? now()->format('Y-m');

    // Kurs narxi
    $coursePrice = $enrollment->group->course->monthly_price;

    // Talaba chegirmasi
    $discount = $enrollment->student->calculateTotalDiscount($coursePrice);

    // Kerakli summa
    $required = $coursePrice - $discount;

    // To'langan summa
    $paid = $enrollment->payments->where('period', $period)->sum('amount');

    // Qoldiq
    $remaining = max(0, $required - $paid);

    return [
        'course_price' => $coursePrice,
        'discount' => $discount,
        'required' => $required,
        'paid' => $paid,
        'remaining' => $remaining,
        'status' => $paid >= $required ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
    ];
}
```

### Status Qiymatlari

| Status | Tavsif |
|--------|--------|
| `paid` | To'liq to'langan |
| `partial` | Qisman to'langan |
| `unpaid` | To'lanmagan |

## Moliyaviy Hisobotlar

### Oylik Daromad

```php
// Reports.php - financialReport()
$startDate = Carbon::parse($this->period.'-01')->startOfMonth();
$endDate = Carbon::parse($this->period.'-01')->endOfMonth();

$payments = Payment::with(['enrollment.student', 'enrollment.group.course', 'enrollment.group.teacher'])
    ->whereBetween('paid_at', [$startDate, $endDate])
    ->get();

$total = $payments->sum('amount');
$teacherTotal = $payments->sum('teacher_share');
$schoolTotal = $payments->sum('school_share');
```

### To'lov Usuli Bo'yicha

```php
$byMethod = $payments->groupBy('method')->map(fn ($items) => $items->sum('amount'));
// ['cash' => 5000000, 'card' => 2000000, 'transfer' => 1000000]
```

### Guruh Bo'yicha

```php
$byGroup = $payments->groupBy('enrollment.group_id')->map(function ($items) {
    $group = $items->first()->enrollment->group;
    return [
        'name' => $group->name,
        'amount' => $items->sum('amount'),
        'teacher_share' => $items->sum('teacher_share'),
        'school_share' => $items->sum('school_share'),
    ];
});
```

### O'qituvchi Bo'yicha

```php
$byTeacher = $payments->groupBy('enrollment.group.teacher_id')->map(function ($items) {
    $teacher = $items->first()->enrollment->group->teacher;
    return [
        'name' => $teacher->name,
        'total' => $items->sum('amount'),
        'share' => $items->sum('teacher_share'),
        'percentage' => $teacher->payment_percentage,
    ];
});
```

## To'lanmagan To'lovlar

```php
// Reports.php - outstandingPayments()
Enrollment::with(['student.activeDiscounts', 'group.course', 'payments'])
    ->where('status', 'active')
    ->get()
    ->map(function ($enrollment) use ($period) {
        $coursePrice = $enrollment->group->course->monthly_price;
        $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
        $required = $coursePrice - $discount;
        $paid = $enrollment->payments->where('period', $period)->sum('amount');
        $remaining = max(0, $required - $paid);

        return [
            'enrollment' => $enrollment,
            'student' => $enrollment->student,
            'group' => $enrollment->group,
            'required' => $required,
            'paid' => $paid,
            'remaining' => $remaining,
            'status' => $paid >= $required ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ];
    })
    ->filter(fn ($item) => $item['remaining'] > 0)
    ->sortByDesc('remaining');
```

## Dashboard Statistikalari

```php
// Dashboard.php - stats()

// Joriy oy daromadi
$currentMonthStart = now()->startOfMonth();
$currentMonthEnd = now()->endOfMonth();
$currentMonthRevenue = Payment::whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd])
    ->sum('amount');

// O'qituvchi ulushi
$currentTeacherShare = Payment::whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd])
    ->sum('teacher_share');

// Sof daromad = Daromad - O'qituvchi ulushi - Xarajatlar
$currentNetIncome = $currentMonthRevenue - $currentTeacherShare - $currentExpenses;
```

## Oxirgi To'lovlar

```php
// Dashboard.php - recentPayments()
Payment::with(['enrollment.student', 'enrollment.group.course'])
    ->latest('paid_at')
    ->take(5)
    ->get();
```

## Haftalik Daromad Grafigi

```php
// Dashboard.php - weeklyRevenue()
$data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = now()->subDays($i);
    $amount = Payment::whereDate('paid_at', $date)->sum('amount');
    $data[] = [
        'day' => $date->format('D'),
        'date' => $date->format('d.m'),
        'amount' => $amount,
    ];
}
return $data;
```

## Casts

```php
protected function casts(): array
{
    return [
        'amount' => 'decimal:2',
        'teacher_share' => 'decimal:2',
        'school_share' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
}
```

## Bog'liq Modullar

- [Ro'yxatga Olish](./05-enrollments.md)
- [Guruhlar](./04-groups.md)
- [O'qituvchilar](./11-teachers.md) - Ulush foizi
- [Chegirmalar](./09-discounts.md) - Talaba chegirmasi
- [Hisobotlar](./14-reports.md) - Moliyaviy hisobotlar
