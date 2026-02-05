# O'qituvchilar Moduli

O'qituvchilarni boshqarish, to'lov foizlarini sozlash va ustoz profili.

## Fayllar

### Admin Panel
- **Komponent**: `app/Livewire/Admin/Teachers.php`
- **View**: `resources/views/livewire/admin/teachers.blade.php`
- **Route**: `GET /admin/teachers`

### Teacher Panel (Ustoz profili)
- **Dashboard**: `app/Livewire/Teacher/TeacherDashboard.php`
- **Schedule**: `app/Livewire/Teacher/TeacherSchedule.php`
- **GroupDetail**: `app/Livewire/Teacher/TeacherGroupDetail.php`
- **Attendance**: `app/Livewire/Teacher/TeacherAttendance.php`
- **Routes**: `routes/teacher.php`

## Model

**Jadval**: `teachers`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| user_id | bigint | nullable, User bilan bog'lanish |
| name | string | To'liq ism |
| phone | string | Telefon raqam |
| payment_percentage | integer | To'lov foizi (default: 50) |
| is_active | boolean | Faol holati |

### Munosabatlar

```php
// Teacher.php
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
```

```php
// User.php
public function teacher(): HasOne
{
    return $this->hasOne(Teacher::class);
}

public function isTeacher(): bool
{
    return $this->hasRole('teacher') && $this->teacher !== null;
}
```

## Ustoz Akkaunt Tizimi

Ustozlar tizimga kirish uchun User akkauntiga ega bo'lishi kerak.

### Akkaunt Yaratish (Admin)

```php
public function openAccountModal(Teacher $teacher): void
{
    $this->accountTeacherId = $teacher->id;
    $this->email = '';
    $this->password = Str::random(10); // Avtomatik parol
    $this->showAccountModal = true;
}

public function createAccount(): void
{
    $this->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
    ]);

    $teacher = Teacher::find($this->accountTeacherId);

    // User yaratish
    $user = User::create([
        'name' => $teacher->name,
        'email' => $this->email,
        'password' => Hash::make($this->password),
        'email_verified_at' => now(),
    ]);

    // Teacher role berish
    $user->assignRole('teacher');

    // Teacher ga bog'lash
    $teacher->update(['user_id' => $user->id]);

    $this->dispatch('account-created', password: $this->password);
}
```

### Akkauntni O'chirish

```php
public function unlinkAccount(Teacher $teacher): void
{
    if ($teacher->user) {
        $user = $teacher->user;
        $teacher->update(['user_id' => null]);
        $user->delete();
    }
}
```

## Teacher Dashboard

Ustoz o'z dashboardida ko'radi:
- Faol guruhlar soni
- Jami o'quvchilar
- Bu oydagi daromad
- Bugungi davomat

```php
#[Computed]
public function stats(): array
{
    $teacher = $this->teacher;

    $activeGroups = $teacher->groups()->where('status', 'active')->count();

    $totalStudents = Enrollment::whereHas('group', fn ($q) => $q->where('teacher_id', $teacher->id))
        ->where('status', 'active')
        ->count();

    $monthlyEarnings = Payment::whereHas('enrollment.group', fn ($q) => $q->where('teacher_id', $teacher->id))
        ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
        ->sum('teacher_share');

    return [
        'active_groups' => $activeGroups,
        'total_students' => $totalStudents,
        'monthly_earnings' => $monthlyEarnings,
    ];
}
```

### Bugungi Darslar

```php
#[Computed]
public function todayGroups()
{
    $today = now()->dayOfWeek;
    $isOddDay = in_array($today, [1, 3, 5]); // Du, Chor, Ju

    return $this->teacher->groups()
        ->with(['course', 'room'])
        ->whereIn('status', ['active', 'pending'])
        ->where('days', $isOddDay ? 'odd' : 'even')
        ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
        ->orderBy('start_time')
        ->get();
}
```

## Teacher Routes

```php
// routes/teacher.php
Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
        Route::get('/schedule', TeacherSchedule::class)->name('schedule');
        Route::get('/groups/{group}', TeacherGroupDetail::class)->name('groups.show');
        Route::get('/attendance', TeacherAttendance::class)->name('attendance');
    });
```

## Teacher Group Detail

Ustoz o'z guruhlarini ko'radi va davomat belgilaydi. To'lov qabul qilish imkoniyati yo'q.

```php
public function mount(Group $group): void
{
    $teacher = auth()->user()->teacher;

    // Faqat o'z guruhlarini ko'rishi mumkin
    if (! $teacher || $group->teacher_id !== $teacher->id) {
        abort(403, 'Bu guruhga kirishga ruxsat yo\'q');
    }

    $this->group = $group->load(['course', 'teacher', 'room']);
}
```

### Tablar
- **O'quvchilar** - Ro'yxat va davomat statistikasi
- **Davomat** - Davomat belgilash

## To'lov Foizi

O'qituvchi har bir to'lovdan belgilangan foizni oladi:

```
To'lov: 500,000 so'm
O'qituvchi foizi: 50%
O'qituvchi ulushi: 250,000 so'm
Markaz ulushi: 250,000 so'm
```

### Payment Modelda Ishlatilishi

```php
// Payment.php - boot()
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

## Dashboard Redirect

Teacher role bilan login qilgan foydalanuvchi avtomatik teacher dashboard ga yo'naltiriladi:

```php
// Dashboard.php
public function mount(): void
{
    if (auth()->user()->hasRole('teacher')) {
        $this->redirect(route('teacher.dashboard'), navigate: true);
    }
}
```

## Sidebar Menu

```blade
@role('teacher')
<flux:sidebar.group heading="Ustoz paneli" class="grid">
    <flux:sidebar.item icon="home" :href="route('teacher.dashboard')">
        Dashboard
    </flux:sidebar.item>
    <flux:sidebar.item icon="calendar-days" :href="route('teacher.schedule')">
        Dars jadvali
    </flux:sidebar.item>
    <flux:sidebar.item icon="clipboard-document-check" :href="route('teacher.attendance')">
        Davomat
    </flux:sidebar.item>
</flux:sidebar.group>
@endrole
```

## Admin View (Ustozlar jadvali)

| Ustun | Tavsif |
|-------|--------|
| Ism | Ustoz ismi |
| Telefon | Aloqa raqami |
| To'lov % | Foiz ulushi |
| Holat | Faol/Nofaol |
| Akkaunt | Email yoki "Yo'q" |
| Amallar | Akkaunt yaratish/o'chirish, Tahrirlash, O'chirish |

## Migration

```php
// 2026_02_05_110000_add_user_id_to_teachers_table.php
Schema::table('teachers', function (Blueprint $table) {
    $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
});
```

## Bog'liq Modullar

- [Guruhlar](./04-groups.md) - O'qituvchi tayinlash
- [To'lovlar](./06-payments.md) - Ulush hisoblash
- [Hisobotlar](./14-reports.md) - O'qituvchi daromadi
- [Dars jadvali](./18-schedule.md) - Teacher schedule
