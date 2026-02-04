# Davomat Moduli

Talabalar davomatini qayd qilish va hisobot olish.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Attendance.php`
- **View**: `resources/views/livewire/admin/attendance.blade.php`
- **Model**: `app/Models/Attendance.php`
- **Route**: `GET /admin/attendance`

## Model

**Jadval**: `attendances`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| enrollment_id | foreignId | Enrollment ID |
| lesson_number | integer | Dars raqami (1, 2, 3, ...) |
| date | date | Dars sanasi |
| present | boolean | Keldi/Kelmadi |
| notes | text, nullable | Izohlar |

### Unique Constraint
`enrollment_id` + `lesson_number` = unique (bir talaba bir darsda faqat 1 ta yozuv)

## Attendance.php Komponent

### Properties

```php
#[Url] public string $selectedDate = '';  // Tanlangan sana
#[Url] public string $group_id = '';      // Guruh filter

public function mount(): void
{
    $this->selectedDate = now()->format('Y-m-d');
}
```

### Bugungi Guruhlar

```php
#[Computed]
public function todayGroups()
{
    $today = now()->dayOfWeek;
    $isOddDay = in_array($today, [1, 3, 5]); // Du, Chor, Ju

    $query = Group::with(['course', 'teacher', 'room'])
        ->where('status', 'active')
        ->where('days', $isOddDay ? 'odd' : 'even')
        ->whereHas('enrollments', fn ($q) => $q->where('status', 'active'))
        ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')]);

    if ($this->group_id) {
        $query->where('id', $this->group_id);
    }

    return $query->orderBy('start_time')->get();
}
```

### Davomat Yuklash

```php
#[Computed]
public function attendanceData()
{
    $data = [];

    foreach ($this->todayGroups as $group) {
        $enrollments = $group->enrollments()
            ->with('student')
            ->where('status', 'active')
            ->get();

        // Bugungi davomat
        $todayAttendance = Attendance::whereIn('enrollment_id', $enrollments->pluck('id'))
            ->whereDate('date', $this->selectedDate)
            ->get()
            ->keyBy('enrollment_id');

        $data[$group->id] = [
            'group' => $group,
            'enrollments' => $enrollments,
            'attendance' => $todayAttendance,
        ];
    }

    return $data;
}
```

## GroupDetail.php da Davomat

### Properties

```php
public int $lesson_number = 1;
public string $lesson_date = '';
public array $attendance = [];
```

### Dars Sanalari

```php
#[Computed]
public function lessonDates()
{
    $existingLessons = Attendance::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
        ->select('lesson_number', 'date')
        ->distinct()
        ->orderBy('lesson_number')
        ->get()
        ->keyBy('lesson_number');

    $lessons = [];
    $maxLesson = max($existingLessons->keys()->max() ?? 0, $this->lesson_number);

    for ($i = 1; $i <= max($maxLesson, $this->group->total_lessons ?? 12); $i++) {
        $lessons[$i] = $existingLessons->has($i)
            ? $existingLessons[$i]->date->format('d.m.Y')
            : null;
    }

    return $lessons;
}
```

### Mavjud Davomat

```php
#[Computed]
public function existingAttendance()
{
    return Attendance::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
        ->where('lesson_number', $this->lesson_number)
        ->get()
        ->keyBy('enrollment_id');
}
```

### Davomatni Yuklash

```php
public function loadAttendance(): void
{
    $this->attendance = [];

    foreach ($this->enrollments as $enrollment) {
        $existing = $this->existingAttendance->get($enrollment->id);
        $this->attendance[$enrollment->id] = $existing ? $existing->present : false;
    }

    // Agar mavjud bo'lsa, sanasini ham yuklash
    $existingRecord = $this->existingAttendance->first();
    if ($existingRecord) {
        $this->lesson_date = $existingRecord->date->format('Y-m-d');
    }
}

public function updatedLessonNumber(): void
{
    $this->loadAttendance();
}
```

### Davomatni Saqlash

```php
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
```

### Tez Belgilash

```php
// Hammasini "Keldi" qilish
public function markAllPresent(): void
{
    foreach ($this->enrollments as $enrollment) {
        $this->attendance[$enrollment->id] = true;
    }
}

// Hammasini "Kelmadi" qilish
public function markAllAbsent(): void
{
    foreach ($this->enrollments as $enrollment) {
        $this->attendance[$enrollment->id] = false;
    }
}

// Bitta talabani almashtirish
public function toggleAttendance(int $enrollmentId): void
{
    $this->attendance[$enrollmentId] = !($this->attendance[$enrollmentId] ?? false);
}
```

## Davomat Statistikasi

### Talaba uchun

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

### Guruh uchun

```php
// Reports.php - attendanceReport()
Enrollment::with(['student', 'group.course', 'attendances'])
    ->where('status', 'active')
    ->get()
    ->map(function ($enrollment) {
        $total = $enrollment->attendances->count();
        $present = $enrollment->attendances->where('present', true)->count();

        return [
            'enrollment' => $enrollment,
            'student' => $enrollment->student,
            'group' => $enrollment->group,
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100) : 0,
        ];
    })
    ->sortBy('percentage');
```

## Dashboard Statistikasi

```php
// Dashboard.php - attendanceToday()
$today = now()->format('Y-m-d');

return [
    'marked' => Attendance::whereDate('date', $today)
        ->distinct('enrollment_id')
        ->count('enrollment_id'),
    'total' => Enrollment::where('status', 'active')->count(),
];
```

## View Strukturasi (GroupDetail)

```blade
{{-- Dars tanlash --}}
<div class="flex gap-2">
    @foreach ($this->lessonDates as $num => $date)
        <button
            wire:click="$set('lesson_number', {{ $num }})"
            class="{{ $lesson_number === $num ? 'bg-blue-500 text-white' : 'bg-zinc-100' }}"
        >
            {{ $num }}
            @if ($date)
                <span class="text-xs">{{ $date }}</span>
            @endif
        </button>
    @endforeach
</div>

{{-- Sana --}}
<flux:input type="date" wire:model="lesson_date" />

{{-- Talabalar ro'yxati --}}
@foreach ($this->enrollments as $enrollment)
    <div class="flex items-center justify-between">
        <span>{{ $enrollment->student->name }}</span>
        <button
            wire:click="toggleAttendance({{ $enrollment->id }})"
            class="{{ $attendance[$enrollment->id] ?? false ? 'bg-green-500' : 'bg-red-500' }}"
        >
            {{ $attendance[$enrollment->id] ?? false ? 'Keldi' : 'Kelmadi' }}
        </button>
    </div>
@endforeach

{{-- Tez belgilash --}}
<flux:button wire:click="markAllPresent">Barchasi keldi</flux:button>
<flux:button wire:click="markAllAbsent">Barchasi kelmadi</flux:button>

{{-- Saqlash --}}
<flux:button wire:click="saveAttendance">Saqlash</flux:button>
```

## Livewire Events

```blade
{{-- Toast notification --}}
<div
    x-data="{ show: false, message: '' }"
    @attendance-saved.window="show = true; message = 'Davomat saqlandi!'; setTimeout(() => show = false, 3000)"
    x-show="show"
>
    <span x-text="message"></span>
</div>
```

## Casts

```php
protected function casts(): array
{
    return [
        'date' => 'date',
        'present' => 'boolean',
    ];
}
```

## Bog'liq Modullar

- [Ro'yxatga Olish](./05-enrollments.md)
- [Guruhlar](./04-groups.md)
- [Hisobotlar](./14-reports.md)
