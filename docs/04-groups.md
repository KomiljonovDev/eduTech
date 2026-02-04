# Guruhlar Moduli

O'quv guruhlarini boshqarish, dars jadvali va talabalar ro'yxati.

## Fayllar

- **Ro'yxat**: `app/Livewire/Admin/Groups.php`
- **Detail**: `app/Livewire/Admin/GroupDetail.php`
- **Views**: `resources/views/livewire/admin/groups.blade.php`, `group-detail.blade.php`
- **Model**: `app/Models/Group.php`
- **Routes**:
  - `GET /admin/groups` - Ro'yxat
  - `GET /admin/groups/{group}` - Detail

## Guruhlar Ro'yxati (Groups.php)

### Filterlar

```php
#[Url] public string $filterCourse = '';
#[Url] public string $filterTeacher = '';
#[Url] public string $filterDays = '';      // odd, even
#[Url] public string $filterStatus = '';    // pending, active, completed, cancelled
#[Url] public string $filterRoom = '';
#[Url] public string $search = '';
```

### Query

```php
$query = Group::with(['course', 'teacher', 'room'])
    ->withCount([
        'enrollments',
        'enrollments as active_enrollments_count' => fn ($q) => $q->where('status', 'active')
    ]);
```

### CRUD

```php
// Yaratish
public function create(): void
{
    $this->reset([...]);
    $this->days = 'odd';
    $this->start_time = '09:00';
    $this->end_time = '11:00';
    $this->total_lessons = '12';
    $this->start_date = now()->format('Y-m-d');
    $this->status = 'pending';
    $this->showModal = true;
}

// Saqlash
public function save(): void
{
    $this->validate();
    Group::updateOrCreate(
        ['id' => $this->editingId],
        [
            'name' => $this->name,
            'course_id' => $this->course_id,
            'teacher_id' => $this->teacher_id,
            'room_id' => $this->room_id,
            'days' => $this->days,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_lessons' => $this->total_lessons,
            'start_date' => $this->start_date,
            'status' => $this->status,
        ]
    );
}
```

### Validatsiya

```php
protected function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'course_id' => 'required|exists:courses,id',
        'teacher_id' => 'required|exists:teachers,id',
        'room_id' => 'required|exists:rooms,id',
        'days' => 'required|in:odd,even',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'total_lessons' => 'required|integer|min:1|max:100',
        'start_date' => 'required|date',
        'status' => 'required|in:pending,active,completed,cancelled',
    ];
}
```

## Guruh Detail (GroupDetail.php)

### Mount

```php
public function mount(Group $group): void
{
    $this->group = $group->load(['course', 'teacher', 'room']);
    $this->lesson_date = now()->format('Y-m-d');
    $this->period = now()->format('Y-m');
    $this->loadAttendance();
}
```

### Tablar

1. **Talabalar** (`students`) - Guruh talabalari ro'yxati
2. **Davomat** (`attendance`) - Davomat qayd qilish
3. **Tarix** (`history`) - Barcha enrollmentlar tarixi

### Computed Properties

```php
// Faol enrollmentlar
#[Computed]
public function enrollments()
{
    return Enrollment::with(['student.activeDiscounts', 'payments', 'attendances'])
        ->where('group_id', $this->group->id)
        ->where('status', 'active')
        ->get();
}

// Barcha enrollmentlar (tarix uchun)
#[Computed]
public function allEnrollments()
{
    return Enrollment::with(['student', 'payments'])
        ->where('group_id', $this->group->id)
        ->get();
}

// Dars sanalari
#[Computed]
public function lessonDates()
{
    // Har bir dars raqami uchun sana
    $existingLessons = Attendance::whereHas('enrollment', ...)
        ->select('lesson_number', 'date')
        ->distinct()
        ->keyBy('lesson_number');
    // ...
}
```

### To'lov Qabul Qilish

```php
// Modal ochish
public function openPaymentModal(Enrollment $enrollment): void
{
    $this->paymentEnrollmentId = $enrollment->id;
    $status = $this->getPaymentStatusForPeriod($enrollment);
    $this->amount = $status['remaining'] > 0 ? $status['remaining'] : $status['required'];
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
    $this->dispatch('payment-collected');
}
```

### To'lov Holati Hisoblash

```php
public function getPaymentStatusForPeriod(Enrollment $enrollment, ?string $period = null): array
{
    $period = $period ?? $this->period;
    $coursePrice = $this->group->course->monthly_price;
    $payments = $enrollment->payments->where('period', $period);
    $totalPaid = $payments->sum('amount');
    $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
    $required = $coursePrice - $discount;

    return [
        'course_price' => $coursePrice,
        'discount' => $discount,
        'required' => $required,
        'paid' => $totalPaid,
        'remaining' => max(0, $required - $totalPaid),
        'status' => $totalPaid >= $required ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
    ];
}
```

### Talaba Qo'shish

```php
// Mavjud talabalardan qo'shish
#[Computed]
public function availableStudents()
{
    $enrolledStudentIds = $this->group->enrollments()
        ->whereIn('status', ['active', 'paused'])
        ->pluck('student_id');

    return Student::whereNotIn('id', $enrolledStudentIds)
        ->where('name', 'like', "%{$this->studentSearch}%")
        ->limit(20)
        ->get();
}

// Qo'shish
public function addStudentDirect(int $studentId): void
{
    Enrollment::create([
        'student_id' => $studentId,
        'group_id' => $this->group->id,
        'enrolled_at' => now(),
        'status' => 'active',
    ]);
    $this->dispatch('student-added');
}
```

### Talabani Chiqarish

```php
public function unenrollStudent(Enrollment $enrollment): void
{
    // Joriy oydagi qarzdorlikni hisoblash
    $enrollment->load(['student.activeDiscounts', 'group.course', 'payments']);
    $currentPeriod = now()->format('Y-m');
    $coursePrice = $enrollment->group->course->monthly_price;
    $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
    $required = $coursePrice - $discount;
    $paid = $enrollment->payments->where('period', $currentPeriod)->sum('amount');
    $remaining = max(0, $required - $paid);

    $enrollment->update([
        'status' => 'dropped',
        'dropped_at' => now(),
        'final_balance' => $remaining, // Qarz saqlanadi
    ]);

    $this->dispatch('student-removed');
}
```

### Davomat

```php
// Davomatni yuklash
public function loadAttendance(): void
{
    $this->attendance = [];
    foreach ($this->enrollments as $enrollment) {
        $existing = $this->existingAttendance->get($enrollment->id);
        $this->attendance[$enrollment->id] = $existing ? $existing->present : false;
    }
}

// Davomatni saqlash
public function saveAttendance(): void
{
    foreach ($this->enrollments as $enrollment) {
        Attendance::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_number' => $this->lesson_number,
            ],
            [
                'date' => $this->lesson_date,
                'present' => $this->attendance[$enrollment->id] ?? false,
            ]
        );
    }
    $this->dispatch('attendance-saved');
}

// Hammasini keldi/kelmadi qilish
public function markAllPresent(): void
{
    foreach ($this->enrollments as $enrollment) {
        $this->attendance[$enrollment->id] = true;
    }
}
```

## Dars Jadvali (Days)

| Kun turi | Kunlar |
|----------|--------|
| `odd` (Toq) | Dushanba, Chorshanba, Juma |
| `even` (Juft) | Seshanba, Payshanba, Shanba |

```php
// Bugungi guruhlarni olish
$today = now()->dayOfWeek; // 0=Yakshanba, 1=Dushanba, ...
$isOddDay = in_array($today, [1, 3, 5]); // Du, Chor, Ju

$todayGroups = Group::where('status', 'active')
    ->where('days', $isOddDay ? 'odd' : 'even')
    ->get();
```

## Status O'zgarishlari

```
pending ──► active ──► completed
    │           │
    └───► cancelled
            │
            └───► (dropped enrollments)
```

## Bog'liq Modullar

- [Kurslar](./12-courses.md) - Guruh kursi
- [O'qituvchilar](./11-teachers.md) - Guruh o'qituvchisi
- [Xonalar](./13-rooms.md) - Dars o'tiladigan xona
- [Davomat](./07-attendance.md) - Guruh davomati
- [To'lovlar](./06-payments.md) - Guruh to'lovlari
