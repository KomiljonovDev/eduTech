# Ro'yxatga Olish (Enrollments)

Talaba va guruh o'rtasidagi bog'lanish. Bir talaba bir vaqtda bir nechta guruhda o'qishi mumkin.

## Model

**Fayl**: `app/Models/Enrollment.php`
**Jadval**: `enrollments`

## Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| student_id | foreignId | Talaba ID |
| group_id | foreignId | Guruh ID |
| enrolled_at | date | Ro'yxatga olingan sana |
| status | enum | Holat |
| dropped_at | date, nullable | Chiqarilgan sana |
| final_balance | decimal(12,2) | Chiqarishdagi qarz |
| drop_reason | string, nullable | Chiqarish sababi |
| notes | text, nullable | Izohlar |

## Holatlar (Status)

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  ┌────────┐    ┌────────┐    ┌───────────┐             │
│  │ active │───►│completed│    │transferred│             │
│  └────────┘    └────────┘    └───────────┘             │
│       │                            ▲                    │
│       │                            │                    │
│       ▼                            │                    │
│  ┌─────────┐                       │                    │
│  │ dropped │───────────────────────┘                    │
│  └─────────┘                                            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### `active` - Faol
- Talaba hozirda shu guruhda o'qiyapti
- To'lovlar va davomat qayd qilinadi
- Hisobotlarda ko'rinadi

### `completed` - Tugallangan
- Talaba kursni muvaffaqiyatli tugatgan
- Yangi guruhga o'tkazish mumkin

### `dropped` - Chiqarilgan
- Talaba guruhdan chiqarilgan
- `dropped_at` - qachon chiqarilgani
- `final_balance` - chiqarishdagi qarzdorlik
- `drop_reason` - sabab (ixtiyoriy)

### `transferred` - O'tkazilgan
- Boshqa guruhga o'tkazilgan
- Eski enrollment saqlanadi

## Enrollment Yaratish

```php
// GroupDetail.php da
public function addStudentDirect(int $studentId): void
{
    // Mavjudligini tekshirish
    $exists = Enrollment::where('student_id', $studentId)
        ->where('group_id', $this->group->id)
        ->whereIn('status', ['active', 'paused'])
        ->exists();

    if ($exists) {
        return; // Allaqachon qo'shilgan
    }

    Enrollment::create([
        'student_id' => $studentId,
        'group_id' => $this->group->id,
        'enrolled_at' => now(),
        'status' => 'active',
    ]);
}
```

## Enrollment Chiqarish

```php
public function unenrollStudent(Enrollment $enrollment): void
{
    // Qarzdorlikni hisoblash
    $enrollment->load(['student.activeDiscounts', 'group.course', 'payments']);

    $currentPeriod = now()->format('Y-m');
    $coursePrice = $enrollment->group->course->monthly_price;
    $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
    $required = $coursePrice - $discount;
    $paid = $enrollment->payments->where('period', $currentPeriod)->sum('amount');
    $remaining = max(0, $required - $paid);

    // Status o'zgartirish
    $enrollment->update([
        'status' => 'dropped',
        'dropped_at' => now(),
        'final_balance' => $remaining,
    ]);
}
```

## Munosabatlar

```php
// Enrollment.php
public function student(): BelongsTo
{
    return $this->belongsTo(Student::class);
}

public function group(): BelongsTo
{
    return $this->belongsTo(Group::class);
}

public function payments(): HasMany
{
    return $this->hasMany(Payment::class);
}

public function attendances(): HasMany
{
    return $this->hasMany(Attendance::class);
}
```

## Query Misollari

### Faol enrollmentlar
```php
Enrollment::where('status', 'active')->get();
```

### Guruhning faol talabalari
```php
Enrollment::where('group_id', $groupId)
    ->where('status', 'active')
    ->with('student')
    ->get();
```

### Talabaning faol guruhlari
```php
Enrollment::where('student_id', $studentId)
    ->where('status', 'active')
    ->with('group.course')
    ->get();
```

### Qarzdor chiqarilganlar
```php
Enrollment::where('status', 'dropped')
    ->where('final_balance', '>', 0)
    ->with(['student', 'group.course'])
    ->get();
```

### Oylik ro'yxatga olish statistikasi
```php
Enrollment::where('enrolled_at', '>=', now()->subMonths(6))
    ->get()
    ->groupBy(fn ($e) => $e->enrolled_at->format('Y-m'))
    ->map(fn ($items) => $items->count());
```

## To'lov Holati

Enrollment orqali to'lov holatini aniqlash:

```php
public function getPaymentStatusForPeriod(Enrollment $enrollment, string $period): array
{
    $coursePrice = $enrollment->group->course->monthly_price;
    $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
    $required = $coursePrice - $discount;
    $paid = $enrollment->payments->where('period', $period)->sum('amount');
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

## Davomat Statistikasi

```php
public function getAttendanceStats(Enrollment $enrollment): array
{
    $total = $enrollment->attendances->count();
    $present = $enrollment->attendances->where('present', true)->count();

    return [
        'total' => $total,
        'present' => $present,
        'absent' => $total - $present,
        'percentage' => $total > 0 ? round(($present / $total) * 100) : 0,
    ];
}
```

## Casts

```php
protected function casts(): array
{
    return [
        'enrolled_at' => 'date',
        'dropped_at' => 'date',
        'final_balance' => 'decimal:2',
    ];
}
```

## Fillable

```php
protected $fillable = [
    'student_id',
    'group_id',
    'enrolled_at',
    'status',
    'dropped_at',
    'final_balance',
    'drop_reason',
    'notes',
];
```

## Unique Constraint

Bir talaba bir guruhda faqat bitta faol enrollment bo'lishi kerak:

```php
// Tekshirish
$exists = Enrollment::where('student_id', $studentId)
    ->where('group_id', $groupId)
    ->whereIn('status', ['active', 'paused'])
    ->exists();
```

## Bog'liq Modullar

- [Talabalar](./03-students.md)
- [Guruhlar](./04-groups.md)
- [To'lovlar](./06-payments.md)
- [Davomat](./07-attendance.md)
