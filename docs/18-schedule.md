# Dars Jadvali Moduli

Barcha guruhlarning haftalik dars jadvalini vizual ko'rinishda ko'rsatish.

## Fayllar

### Admin Panel
- **Komponent**: `app/Livewire/Admin/Schedule.php`
- **View**: `resources/views/livewire/admin/schedule.blade.php`
- **Route**: `GET /admin/schedule`

### Teacher Panel
- **Komponent**: `app/Livewire/Teacher/TeacherSchedule.php`
- **View**: `resources/views/livewire/teacher/schedule.blade.php`
- **Route**: `GET /teacher/schedule`

## Funksionallik

### Jadval Ko'rinishi

Jadval vaqt slotlari (08:00 - 20:00) va kunlar (Du-Chor-Jum, Se-Pay-Shan) bo'yicha tuzilgan.

```
         │  Du-Chor-Jum  │  Se-Pay-Shan  │
─────────┼───────────────┼───────────────┤
  09:00  │  WEB-001      │               │
  10:00  │  KS-002       │  KS-001       │
  15:00  │  WEB-003      │  WEB-004      │
```

### Filterlar (faqat Admin)

```php
#[Url]
public string $days = '';      // odd, even

#[Url]
public string $room_id = '';   // Xona bo'yicha
```

### Computed Properties

```php
#[Computed]
public function groups()
{
    return Group::query()
        ->with(['course', 'teacher', 'room'])
        ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
        ->whereIn('status', ['active', 'pending'])
        ->when($this->days, fn ($q) => $q->where('days', $this->days))
        ->when($this->room_id, fn ($q) => $q->where('room_id', $this->room_id))
        ->orderBy('start_time')
        ->get();
}
```

### Slotga Guruhlarni Olish

```php
public function getGroupsForSlot(string $time, string $days): Collection
{
    return $this->groups->filter(function ($group) use ($time, $days) {
        if ($group->days !== $days) {
            return false;
        }

        $startTime = $group->start_time;
        if (! $startTime) {
            return false;
        }

        // Faqat boshlanish soatida ko'rsatish
        return $startTime->format('H:00') === $time
            || $startTime->format('H:i') === $time;
    });
}
```

## Statistikalar

```php
// Admin schedule
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div>Jami guruhlar: {{ $this->groups->count() }}</div>
    <div>Du-Chor-Jum: {{ $this->oddDayGroups->count() }}</div>
    <div>Se-Pay-Shan: {{ $this->evenDayGroups->count() }}</div>
    <div>Jami o'quvchilar: {{ $this->groups->sum('enrollments_count') }}</div>
</div>
```

## Xonalar Bo'yicha Ko'rinish (Admin)

```blade
@foreach ($this->rooms as $room)
    @php $roomGroups = $this->groups->where('room_id', $room->id); @endphp
    @if ($roomGroups->count() > 0)
        <div class="rounded-lg border">
            <div class="border-b px-4 py-3">
                <flux:heading>{{ $room->name }}</flux:heading>
                <flux:badge>{{ $roomGroups->count() }} guruh</flux:badge>
            </div>
            @foreach ($roomGroups->sortBy('start_time') as $group)
                <a href="{{ route('admin.groups.show', $group) }}">
                    {{ $group->course->name }} |
                    {{ $group->days_label }} |
                    {{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }}
                </a>
            @endforeach
        </div>
    @endif
@endforeach
```

## Status Ranglari

```php
public function getStatusColor(string $status): string
{
    return match ($status) {
        'active' => 'bg-green-100 border-green-300',
        'pending' => 'bg-yellow-100 border-yellow-300',
        default => 'bg-zinc-100 border-zinc-300',
    };
}
```

## Guruh Kartochkasi

Har bir guruh kartochkasida:
- Kurs kodi/nomi
- Guruh nomi
- Vaqt (start - end)
- Xona
- Ustoz (faqat admin)
- O'quvchilar soni

```blade
<a href="{{ route('admin.groups.show', $group) }}" class="block rounded-lg border p-2">
    <div class="font-medium">{{ $group->course->code }}</div>
    <div class="text-xs">{{ $group->name }}</div>
    <div class="text-xs text-zinc-500">
        {{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }} • {{ $group->room->name }}
    </div>
    <div class="text-xs">{{ $group->teacher->name }}</div>
    <div class="flex items-center gap-1 text-xs">
        <flux:icon.users class="size-3" />
        {{ $group->enrollments_count }}
    </div>
</a>
```

## Teacher Schedule

Teacher panel da faqat o'zining guruhlarini ko'radi:

```php
#[Computed]
public function groups()
{
    $teacher = $this->teacher;
    if (! $teacher) {
        return collect();
    }

    return $teacher->groups()
        ->with(['course', 'room'])
        ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
        ->whereIn('status', ['active', 'pending'])
        ->orderBy('start_time')
        ->get();
}
```

## Vaqt Slotlari

```php
public array $timeSlots = [
    '08:00', '09:00', '10:00', '11:00', '12:00',
    '13:00', '14:00', '15:00', '16:00', '17:00',
    '18:00', '19:00', '20:00',
];
```

## Bog'liq Modullar

- [Guruhlar](./04-groups.md) - Guruh tafsilotlari
- [O'qituvchilar](./11-teachers.md) - Ustoz profili
- [Xonalar](./13-rooms.md) - Xona ma'lumotlari
