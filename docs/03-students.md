# Talabalar Moduli

Talabalarni boshqarish, qidirish va ro'yxatga olish funksionalligi.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Students.php`
- **View**: `resources/views/livewire/admin/students.blade.php`
- **Model**: `app/Models/Student.php`
- **Route**: `GET /admin/students`

## Funksionallik

### Talabalar Ro'yxati

```php
// Filterlash va qidirish
#[Url] public string $search = '';
#[Url] public string $filterSource = '';
#[Url] public string $filterStatus = ''; // active, waiting

// Eager loading
$query = Student::with(['enrollments.group.course', 'activeDiscounts'])
    ->withCount([
        'enrollments',
        'enrollments as active_enrollments_count' => fn ($q) => $q->where('status', 'active')
    ]);
```

### Filterlar

| Filter | Tavsif |
|--------|--------|
| `search` | Ism yoki telefon bo'yicha qidirish |
| `filterSource` | Manba bo'yicha (instagram, telegram, etc.) |
| `filterStatus` | `active` - faol o'qiyotganlar, `waiting` - kutayotganlar |

### CRUD Operatsiyalari

#### Yaratish
```php
public function create(): void
{
    $this->reset(['editingId', 'name', 'phone', 'address', 'birth_date', 'source', 'notes']);
    $this->source = 'walk_in';
    $this->showModal = true;
}
```

#### Tahrirlash
```php
public function edit(Student $student): void
{
    $this->editingId = $student->id;
    $this->name = $student->name;
    $this->phone = $student->phone;
    // ...
    $this->showModal = true;
}
```

#### Saqlash
```php
public function save(): void
{
    $this->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'source' => 'required|in:instagram,telegram,referral,walk_in,grand,other',
    ]);

    Student::updateOrCreate(
        ['id' => $this->editingId],
        [
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address ?: null,
            'birth_date' => $this->birth_date ?: null,
            'source' => $this->source,
            'notes' => $this->notes ?: null,
        ]
    );

    $this->showModal = false;
}
```

#### O'chirish
```php
public function delete(Student $student): void
{
    $student->delete();
}
```

## Talaba Holatlari

### Faol (Active)
Kamida bitta `active` statusli enrollment mavjud.

```php
// Faol talabalarni olish
Student::whereHas('enrollments', fn ($q) => $q->where('status', 'active'))->get();
```

### Kutayotgan (Waiting)
Hech qanday `active` enrollment yo'q - yangi yoki kursni tugatgan.

```php
// Kutayotgan talabalarni olish
Student::whereDoesntHave('enrollments', fn ($q) => $q->where('status', 'active'))->get();
```

## Chegirma Bog'lash

Talabaga chegirma biriktirish `discount_student` pivot jadvali orqali.

```php
// Chegirma qo'shish
$student->discounts()->attach($discountId, [
    'valid_from' => now(),
    'valid_until' => now()->addMonths(6), // yoki null (cheksiz)
]);

// Faol chegirmalarni olish
$student->activeDiscounts; // Hozirgi sanada amal qiluvchilar
```

## Manba (Source) Statistikasi

```php
// Manba bo'yicha statistika
Student::select('source', DB::raw('count(*) as count'))
    ->groupBy('source')
    ->pluck('count', 'source');
```

## View Strukturasi

```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading>Talabalar</flux:heading>
        <flux:button wire:click="create">Yangi talaba</flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex gap-4">
        <flux:input wire:model.live.debounce="search" placeholder="Qidirish..." />
        <flux:select wire:model.live="filterSource">
            <option value="">Barcha manbalar</option>
            <option value="instagram">Instagram</option>
            ...
        </flux:select>
        <flux:select wire:model.live="filterStatus">
            <option value="">Barcha holatlar</option>
            <option value="active">Faol</option>
            <option value="waiting">Kutayotgan</option>
        </flux:select>
    </div>

    {{-- Table --}}
    <table>
        @foreach ($students as $student)
            <tr>
                <td>{{ $student->name }}</td>
                <td>{{ $student->phone }}</td>
                <td>{{ $student->active_enrollments_count }}</td>
                ...
            </tr>
        @endforeach
    </table>

    {{-- Modal --}}
    <flux:modal wire:model="showModal">
        <form wire:submit="save">
            <flux:input wire:model="name" label="Ism" />
            <flux:input wire:model="phone" label="Telefon" />
            ...
            <flux:button type="submit">Saqlash</flux:button>
        </form>
    </flux:modal>
</div>
```

## Talaba Detali

Alohida sahifa yo'q - `GroupDetail` orqali guruh ichida ko'rinadi.

### Talaba haqida ma'lumot olish:
- Faol guruhlar
- To'lov tarixi
- Davomat statistikasi
- Chegirmalar

```php
// Talabaning to'liq ma'lumotlari
$student->load([
    'enrollments.group.course',
    'enrollments.payments',
    'enrollments.attendances',
    'activeDiscounts',
]);
```

## Validatsiya

```php
protected function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:500',
        'birth_date' => 'nullable|date',
        'source' => 'required|in:instagram,telegram,referral,walk_in,grand,other',
        'notes' => 'nullable|string|max:1000',
    ];
}
```

## Bog'liq Modullar

- [Guruhlar](./04-groups.md) - Talabani guruhga qo'shish
- [To'lovlar](./06-payments.md) - Talabadan to'lov qabul qilish
- [Chegirmalar](./09-discounts.md) - Talabaga chegirma berish
- [Davomat](./07-attendance.md) - Talaba davomati
