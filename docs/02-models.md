# Modellar

Barcha Eloquent modellar va ularning munosabatlari.

## Model Ro'yxati

| Model | Jadval | Tavsif |
|-------|--------|--------|
| User | users | Foydalanuvchilar (admin, manager) |
| Student | students | Talabalar |
| Teacher | teachers | O'qituvchilar |
| Course | courses | Kurslar |
| Group | groups | O'quv guruhlari |
| Room | rooms | O'quv xonalari |
| Enrollment | enrollments | Talaba-guruh bog'lanishi |
| Payment | payments | To'lovlar |
| Attendance | attendances | Davomat |
| Lead | leads | Potensial mijozlar |
| Discount | discounts | Chegirmalar |
| Expense | expenses | Xarajatlar |

---

## Student (Talaba)

**Fayl**: `app/Models/Student.php`
**Jadval**: `students`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | To'liq ism |
| phone | string | Telefon raqam |
| address | text, nullable | Manzil |
| birth_date | date, nullable | Tug'ilgan sana |
| source | enum | Qayerdan kelgan |
| notes | text, nullable | Izohlar |
| created_at | timestamp | Yaratilgan vaqt |
| updated_at | timestamp | Yangilangan vaqt |

### Source Enum
- `instagram` - Instagram
- `telegram` - Telegram
- `referral` - Tanish-bilish
- `walk_in` - O'zi kelgan
- `grand` - Grand
- `other` - Boshqa

### Munosabatlar

```php
// Talabaning barcha ro'yxatga olishlari
public function enrollments(): HasMany

// Faol chegirmalar (hozirgi sanada amal qiluvchi)
public function activeDiscounts(): BelongsToMany

// Barcha chegirmalar
public function discounts(): BelongsToMany
```

### Metodlar

```php
// Chegirma summasini hisoblash
public function calculateTotalDiscount(float $basePrice): float
{
    $totalDiscount = 0;
    foreach ($this->activeDiscounts as $discount) {
        if ($discount->type === 'percentage') {
            $totalDiscount += ($basePrice * $discount->value / 100);
        } else {
            $totalDiscount += $discount->value;
        }
    }
    return min($totalDiscount, $basePrice); // Narxdan oshmasin
}
```

---

## Teacher (O'qituvchi)

**Fayl**: `app/Models/Teacher.php`
**Jadval**: `teachers`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | To'liq ism |
| phone | string | Telefon raqam |
| payment_percentage | integer | To'lov foizi (default: 50) |
| is_active | boolean | Faol holati |
| created_at | timestamp | |
| updated_at | timestamp | |

### Munosabatlar

```php
// O'qituvchining guruhlari
public function groups(): HasMany
```

### Ishlatilishi

```php
// O'qituvchi ulushi hisoblash (Payment modelda)
$teacherShare = $amount * ($teacher->payment_percentage / 100);
$schoolShare = $amount - $teacherShare;
```

---

## Course (Kurs)

**Fayl**: `app/Models/Course.php`
**Jadval**: `courses`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Kurs nomi |
| code | string | Qisqa kod (KS, WD, etc.) |
| description | text, nullable | Tavsif |
| monthly_price | decimal(12,2) | Oylik narx |
| is_active | boolean | Faol holati |
| created_at | timestamp | |
| updated_at | timestamp | |

### Munosabatlar

```php
public function groups(): HasMany
public function leads(): HasMany
```

---

## Group (Guruh)

**Fayl**: `app/Models/Group.php`
**Jadval**: `groups`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Guruh nomi |
| course_id | foreignId | Kurs |
| teacher_id | foreignId | O'qituvchi |
| room_id | foreignId | Xona |
| days | enum | Kun turi: odd/even |
| start_time | time | Boshlanish vaqti |
| end_time | time | Tugash vaqti |
| total_lessons | integer | Jami darslar soni |
| start_date | date | Boshlanish sanasi |
| status | enum | Holat |
| created_at | timestamp | |
| updated_at | timestamp | |

### Days Enum
- `odd` - Toq kunlar (Dushanba, Chorshanba, Juma)
- `even` - Juft kunlar (Seshanba, Payshanba, Shanba)

### Status Enum
- `pending` - Kutilmoqda (guruh to'lishi kutilmoqda)
- `active` - Faol (darslar o'tilmoqda)
- `completed` - Tugallangan
- `cancelled` - Bekor qilingan

### Munosabatlar

```php
public function course(): BelongsTo
public function teacher(): BelongsTo
public function room(): BelongsTo
public function enrollments(): HasMany
```

---

## Room (Xona)

**Fayl**: `app/Models/Room.php`
**Jadval**: `rooms`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Xona nomi |
| capacity | integer | Sig'im (o'rinlar soni) |
| is_active | boolean | Faol holati |
| created_at | timestamp | |
| updated_at | timestamp | |

### Munosabatlar

```php
public function groups(): HasMany
```

---

## Enrollment (Ro'yxatga Olish)

**Fayl**: `app/Models/Enrollment.php`
**Jadval**: `enrollments`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| student_id | foreignId | Talaba |
| group_id | foreignId | Guruh |
| enrolled_at | date | Ro'yxatga olingan sana |
| status | enum | Holat |
| dropped_at | date, nullable | Chiqarilgan sana |
| final_balance | decimal(12,2) | Chiqarishdagi qarz |
| drop_reason | string, nullable | Chiqarish sababi |
| notes | text, nullable | Izohlar |
| created_at | timestamp | |
| updated_at | timestamp | |

### Status Enum
- `active` - Faol o'qiyapti
- `completed` - Kursni tugatgan
- `dropped` - Chiqarilgan
- `transferred` - Boshqa guruhga o'tkazilgan

### Munosabatlar

```php
public function student(): BelongsTo
public function group(): BelongsTo
public function payments(): HasMany
public function attendances(): HasMany
```

### Chiqarish Logikasi

```php
// Guruhdan chiqarishda qarzdorlik saqlanadi
public function unenrollStudent(Enrollment $enrollment): void
{
    $coursePrice = $enrollment->group->course->monthly_price;
    $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
    $required = $coursePrice - $discount;
    $paid = $enrollment->payments->where('period', now()->format('Y-m'))->sum('amount');
    $remaining = max(0, $required - $paid);

    $enrollment->update([
        'status' => 'dropped',
        'dropped_at' => now(),
        'final_balance' => $remaining,
    ]);
}
```

---

## Payment (To'lov)

**Fayl**: `app/Models/Payment.php`
**Jadval**: `payments`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| enrollment_id | foreignId | Ro'yxatga olish |
| amount | decimal(12,2) | Summa |
| teacher_share | decimal(12,2) | O'qituvchi ulushi |
| school_share | decimal(12,2) | Markaz ulushi |
| paid_at | datetime | To'langan vaqt |
| period | string | Davr (YYYY-MM format) |
| method | enum | To'lov usuli |
| notes | text, nullable | Izohlar |
| created_at | timestamp | |
| updated_at | timestamp | |

### Method Enum
- `cash` - Naqd
- `card` - Karta
- `transfer` - O'tkazma

### Boot Method (Auto-calculate shares)

```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($payment) {
        $teacher = $payment->enrollment->group->teacher;
        $percentage = $teacher->payment_percentage ?? 50;

        $payment->teacher_share = $payment->amount * ($percentage / 100);
        $payment->school_share = $payment->amount - $payment->teacher_share;
    });
}
```

### Munosabatlar

```php
public function enrollment(): BelongsTo
```

---

## Attendance (Davomat)

**Fayl**: `app/Models/Attendance.php`
**Jadval**: `attendances`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| enrollment_id | foreignId | Ro'yxatga olish |
| lesson_number | integer | Dars raqami |
| date | date | Sana |
| present | boolean | Keldi/Kelmadi |
| notes | text, nullable | Izohlar |
| created_at | timestamp | |
| updated_at | timestamp | |

### Unique Constraint
`enrollment_id` + `lesson_number` = unique

### Munosabatlar

```php
public function enrollment(): BelongsTo
```

---

## Lead (Lid)

**Fayl**: `app/Models/Lead.php`
**Jadval**: `leads`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Ism |
| phone | string | Telefon |
| course_id | foreignId, nullable | Qiziqtirgan kurs |
| source | enum | Manba |
| status | enum | Holat |
| preferred_time | string, nullable | Qulay vaqt |
| notes | text, nullable | Izohlar |
| contacted_at | datetime, nullable | Bog'lanilgan vaqt |
| converted_student_id | foreignId, nullable | Aylangan talaba |
| created_at | timestamp | |
| updated_at | timestamp | |

### Source Enum
- `instagram`, `telegram`, `google_form`, `referral`, `walk_in`, `phone_call`, `other`

### Status Enum
- `new` - Yangi
- `contacted` - Bog'lanildi
- `interested` - Qiziqdi
- `enrolled` - Ro'yxatga olindi
- `not_interested` - Qiziqmadi
- `no_answer` - Javob yo'q

### Munosabatlar

```php
public function course(): BelongsTo
public function convertedStudent(): BelongsTo
```

---

## Discount (Chegirma)

**Fayl**: `app/Models/Discount.php`
**Jadval**: `discounts`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Chegirma nomi |
| type | enum | Turi: percentage/fixed |
| value | decimal(12,2) | Qiymat (% yoki summa) |
| is_active | boolean | Faol holati |
| created_at | timestamp | |
| updated_at | timestamp | |

### Pivot jadval: `discount_student`

| Ustun | Tavsif |
|-------|--------|
| discount_id | Chegirma |
| student_id | Talaba |
| valid_from | Boshlanish sanasi |
| valid_until | Tugash sanasi (nullable) |

### Munosabatlar

```php
public function students(): BelongsToMany
```

---

## Expense (Xarajat)

**Fayl**: `app/Models/Expense.php`
**Jadval**: `expenses`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| category | enum | Kategoriya |
| amount | decimal(12,2) | Summa |
| period | string | Davr (YYYY-MM) |
| description | text, nullable | Tavsif |
| user_id | foreignId | Kim qo'shgan |
| created_at | timestamp | |
| updated_at | timestamp | |

### Category Enum
- `rent` - Ijara
- `utilities` - Kommunal xizmatlar
- `supplies` - Ta'minot
- `salary` - Oylik (admin)
- `marketing` - Reklama
- `maintenance` - Ta'mirlash
- `other` - Boshqa

### Munosabatlar

```php
public function user(): BelongsTo
```
