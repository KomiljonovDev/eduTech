# Talabalar Moduli

Talabalarni boshqarish, qidirish, ro'yxatga olish va to'liq profil ko'rish funksionalligi.

## Fayllar

- **Ro'yxat**: `app/Livewire/Admin/Students.php`
- **Detail**: `app/Livewire/Admin/StudentShow.php`
- **Views**: `resources/views/livewire/admin/students.blade.php`, `student-show.blade.php`
- **Model**: `app/Models/Student.php`
- **Routes**:
  - `GET /admin/students` - Ro'yxat
  - `GET /admin/students/{student}` - Detail sahifa

## Talabalar Ro'yxati (Students.php)

### Filterlar

```php
#[Url] public string $search = '';
#[Url] public string $filter = 'all'; // all, waiting, active, completed_ks
```

| Filter | Tavsif |
|--------|--------|
| `all` | Barcha talabalar |
| `waiting` | Kutayotganlar (faol enrollment yo'q) |
| `active` | Faol o'qiyotganlar |
| `completed_ks` | KS kursini tugatganlar |

### Bulk Amallar

```php
// Tanlangan talabalar
public array $selected = [];
public bool $selectAll = false;

// Bulk SMS
public function sendBulkSms(): void
{
    $students = Student::whereIn('id', $this->selected)->get();
    foreach ($students as $student) {
        SendSms::dispatch($student->display_phone, $this->bulkSmsMessage);
    }
}

// Bulk Guruhga qo'shish
public function bulkEnroll(): void
{
    $students = Student::whereIn('id', $this->selected)->get();
    foreach ($students as $student) {
        Enrollment::firstOrCreate([
            'student_id' => $student->id,
            'group_id' => $this->bulkGroupId,
        ], [
            'enrolled_at' => now(),
            'status' => 'active',
        ]);
    }
}
```

### Guruhga Qo'shish Modali

Guruh tanlashda dars vaqti ham ko'rsatiladi:

```blade
<flux:select wire:model="group_id">
    @foreach ($this->availableGroups as $group)
        <flux:select.option value="{{ $group->id }}">
            {{ $group->course->code }} |
            {{ $group->name }} |
            {{ $group->teacher->name }} |
            {{ $group->days_label }} {{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }}
            ({{ $group->enrollments_count }}/{{ $group->room->capacity }})
        </flux:select.option>
    @endforeach
</flux:select>
```

## Talaba Detail Sahifasi (StudentShow.php)

O'quvchi ustiga bosilganda to'liq profil sahifasi ochiladi.

### Mount

```php
public function mount(Student $student): void
{
    $this->student = $student->load([
        'phones',
        'discounts',
        'enrollments.group.course',
        'enrollments.group.teacher',
        'enrollments.payments',
        'enrollments.attendances',
        'lead.course',
        'lead.activities.user',
    ]);
}
```

### Ko'rsatiladigan Ma'lumotlar

#### Statistika
- Faol guruhlar soni
- Tugatgan kurslar soni
- Jami to'langan summa
- Chegirmalar soni

```php
#[Computed]
public function totalPaid(): float
{
    return $this->student->enrollments->sum(fn ($e) => $e->payments->sum('amount'));
}
```

#### Guruhlar Ro'yxati

Barcha enrollmentlar (faol, tugatgan, chiqarilgan):
- Kurs nomi
- Guruh nomi va ustoz
- Jadval (kunlar va vaqt)
- To'langan summa
- Davomat statistikasi

```blade
@foreach ($student->enrollments as $enrollment)
    <a href="{{ route('admin.groups.show', $enrollment->group) }}">
        <span>{{ $enrollment->group->course->name }}</span>
        <flux:badge :color="$enrollment->status === 'active' ? 'green' : 'blue'">
            {{ $enrollment->status }}
        </flux:badge>
        <span>To'langan: {{ $enrollment->payments->sum('amount') }}</span>
    </a>
@endforeach
```

#### To'lovlar Tarixi

Oxirgi 10 ta to'lov:
- Summa
- Kurs va davr
- To'lov usuli
- Sana

```php
$allPayments = $student->enrollments->flatMap->payments->sortByDesc('paid_at');
```

#### Lead Tarixi (agar mavjud bo'lsa)

Agar o'quvchi Lead dan konvert bo'lgan bo'lsa:
- Lead yaratilgan sana
- Qaysi kurs uchun
- LeadActivity ro'yxati (qo'ng'iroqlar tarixi)

```blade
@if ($student->lead->count() > 0)
    @foreach ($student->lead as $lead)
        <div>Lead tarixi - {{ $lead->created_at->format('d.m.Y') }}</div>
        @foreach ($lead->activities as $activity)
            <div>
                <flux:badge>{{ $activity->outcome }}</flux:badge>
                {{ $activity->notes }}
                {{ $activity->contacted_at->format('d.m.Y H:i') }}
            </div>
        @endforeach
    @endforeach
@endif
```

#### Kontakt Ma'lumotlari

- Barcha telefon raqamlari (asosiy, uy, qo'shimcha)
- Manzil
- Manba (Instagram, Telegram, etc.)
- Izoh
- Ro'yxatdan o'tgan sana

#### Chegirmalar

- Mavjud chegirmalar ro'yxati
- Chegirma qo'shish
- Chegirma olib tashlash

```php
public function addDiscount(): void
{
    $this->student->discounts()->attach($this->discount_id, [
        'valid_from' => now(),
    ]);
}

public function removeDiscount(int $discountId): void
{
    $this->student->discounts()->detach($discountId);
}
```

### Havolalar

Jadvalda va guruh ichida talaba nomiga bosish orqali:

```blade
{{-- students.blade.php --}}
<a href="{{ route('admin.students.show', $student) }}" wire:navigate>
    {{ $student->name }}
</a>

{{-- group-detail.blade.php --}}
<a href="{{ route('admin.students.show', $enrollment->student) }}" wire:navigate>
    {{ $enrollment->student->name }}
</a>
```

## Model Munosabatlari

```php
// Student.php
public function enrollments(): HasMany
{
    return $this->hasMany(Enrollment::class);
}

public function lead(): HasMany
{
    return $this->hasMany(Lead::class, 'converted_student_id');
}

public function discounts(): BelongsToMany
{
    return $this->belongsToMany(Discount::class)
        ->withPivot(['valid_from', 'valid_until', 'notes'])
        ->withTimestamps();
}

public function phones(): MorphMany
{
    return $this->morphMany(Phone::class, 'phoneable');
}
```

## Talaba Holatlari

### Faol (Active)
Kamida bitta `active` statusli enrollment mavjud.

### Kutayotgan (Waiting)
Hech qanday `active` enrollment yo'q.

### KS Tugatgan
`completed` statusli enrollment va kurs kodi `KS`.

```php
// Filter
if ($this->filter === 'completed_ks') {
    $query->whereHas('enrollments', function ($q) {
        $q->where('status', 'completed')
            ->whereHas('group.course', fn ($cq) => $cq->where('code', 'KS'));
    });
}
```

## Validatsiya

```php
protected function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'home_phone' => 'nullable|string|max:20',
        'phones' => 'nullable|array|max:4',
        'phones.*.number' => 'nullable|string|max:20',
        'phones.*.owner' => 'nullable|string|max:50',
        'address' => 'nullable|string|max:255',
        'source' => 'required|in:instagram,telegram,referral,walk_in,grand,other',
        'notes' => 'nullable|string',
    ];
}
```

## Bog'liq Modullar

- [Guruhlar](./04-groups.md) - Talabani guruhga qo'shish
- [To'lovlar](./06-payments.md) - Talabadan to'lov qabul qilish
- [Chegirmalar](./09-discounts.md) - Talabaga chegirma berish
- [Davomat](./07-attendance.md) - Talaba davomati
- [Lidlar](./08-leads.md) - Lead tarixi
