# Chegirmalar Moduli

Talabalar uchun chegirma turlarini boshqarish va qo'llash.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Discounts.php`
- **View**: `resources/views/livewire/admin/discounts.blade.php`
- **Model**: `app/Models/Discount.php`
- **Route**: `GET /admin/discounts`

## Model

**Jadval**: `discounts`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Chegirma nomi |
| type | enum | Turi: percentage/fixed |
| value | decimal(12,2) | Qiymat |
| is_active | boolean | Faol holati |

### Pivot jadval: `discount_student`

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| discount_id | foreignId | Chegirma ID |
| student_id | foreignId | Talaba ID |
| valid_from | date | Boshlanish sanasi |
| valid_until | date, nullable | Tugash sanasi (null = cheksiz) |

## Chegirma Turlari

### `percentage` - Foizli
Kurs narxidan foiz hisoblanadi.

```
Kurs narxi: 500,000 so'm
Chegirma: 20%
Chegirma summasi: 500,000 × 0.20 = 100,000 so'm
To'lov: 500,000 - 100,000 = 400,000 so'm
```

### `fixed` - Belgilangan summa
Aniq summa chegirma.

```
Kurs narxi: 500,000 so'm
Chegirma: 50,000 so'm
To'lov: 500,000 - 50,000 = 450,000 so'm
```

## Discounts.php Komponent

### Properties

```php
public bool $showModal = false;
public ?int $editingId = null;
public string $name = '';
public string $type = 'percentage';
public string $value = '';
public bool $is_active = true;
```

### CRUD

```php
// Yaratish
public function create(): void
{
    $this->reset(['editingId', 'name', 'type', 'value', 'is_active']);
    $this->type = 'percentage';
    $this->is_active = true;
    $this->showModal = true;
}

// Saqlash
public function save(): void
{
    $this->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:percentage,fixed',
        'value' => 'required|numeric|min:0',
    ]);

    // Foiz uchun maksimum 100%
    if ($this->type === 'percentage' && $this->value > 100) {
        $this->addError('value', 'Foiz 100 dan oshmasligi kerak');
        return;
    }

    Discount::updateOrCreate(
        ['id' => $this->editingId],
        [
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'is_active' => $this->is_active,
        ]
    );

    $this->showModal = false;
}
```

### Faol Chegirmalar

```php
#[Computed]
public function discounts()
{
    return Discount::withCount('students')->get();
}
```

## Chegirmani Talabaga Biriktirish

```php
// Student modelda
public function discounts(): BelongsToMany
{
    return $this->belongsToMany(Discount::class)
        ->withPivot(['valid_from', 'valid_until'])
        ->withTimestamps();
}

// Faol chegirmalar (hozirgi sanada amal qiluvchi)
public function activeDiscounts(): BelongsToMany
{
    return $this->belongsToMany(Discount::class)
        ->withPivot(['valid_from', 'valid_until'])
        ->wherePivot('valid_from', '<=', now())
        ->where(function ($query) {
            $query->whereNull('discount_student.valid_until')
                ->orWhere('discount_student.valid_until', '>=', now());
        })
        ->where('is_active', true);
}
```

### Chegirma Qo'shish

```php
// Chegirma biriktirish
$student->discounts()->attach($discountId, [
    'valid_from' => now(),
    'valid_until' => now()->addMonths(6), // yoki null (cheksiz)
]);

// Chegirma olib tashlash
$student->discounts()->detach($discountId);
```

## Chegirma Hisoblash

```php
// Student.php
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

    // Chegirma narxdan oshmasin
    return min($totalDiscount, $basePrice);
}
```

### Bir nechta chegirma

Talabada bir nechta faol chegirma bo'lishi mumkin:

```
Kurs narxi: 500,000 so'm
Chegirma 1: 10% = 50,000 so'm
Chegirma 2: 30,000 so'm (fixed)
Jami chegirma: 80,000 so'm
To'lov: 420,000 so'm
```

## To'lovda Ishlatilishi

```php
// GroupDetail.php - getPaymentStatusForPeriod()
public function getPaymentStatusForPeriod(Enrollment $enrollment, ?string $period = null): array
{
    $coursePrice = $this->group->course->monthly_price;

    // Talaba chegirmasini hisoblash
    $discount = $enrollment->student->calculateTotalDiscount($coursePrice);

    // Kerakli summa
    $required = $coursePrice - $discount;

    // ...
}
```

## View Strukturasi

```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading>Chegirmalar</flux:heading>
        <flux:button wire:click="create">Yangi chegirma</flux:button>
    </div>

    {{-- Table --}}
    <table>
        @foreach ($this->discounts as $discount)
            <tr>
                <td>{{ $discount->name }}</td>
                <td>
                    @if ($discount->type === 'percentage')
                        {{ $discount->value }}%
                    @else
                        {{ number_format($discount->value, 0, '', ' ') }} so'm
                    @endif
                </td>
                <td>{{ $discount->students_count }} ta talaba</td>
                <td>
                    <flux:badge :color="$discount->is_active ? 'green' : 'red'">
                        {{ $discount->is_active ? 'Faol' : 'Nofaol' }}
                    </flux:badge>
                </td>
            </tr>
        @endforeach
    </table>

    {{-- Modal --}}
    <flux:modal wire:model="showModal">
        <flux:input wire:model="name" label="Nomi" />

        <flux:select wire:model.live="type" label="Turi">
            <option value="percentage">Foiz</option>
            <option value="fixed">Belgilangan summa</option>
        </flux:select>

        @if ($type === 'percentage')
            <flux:input wire:model="value" type="number" label="Qiymat" suffix="%" />
        @else
            <flux:input wire:model="value" type="number" label="Qiymat" suffix="so'm" />
        @endif

        <flux:checkbox wire:model="is_active" label="Faol" />

        <flux:button wire:click="save">Saqlash</flux:button>
    </flux:modal>
</div>
```

## Chegirma Namunalari

| Nomi | Turi | Qiymat | Tavsif |
|------|------|--------|--------|
| Aka-uka chegirmasi | percentage | 10% | Ikkinchi farzand uchun |
| Yetim bolalar | percentage | 100% | To'liq chegirma |
| Bir martalik | fixed | 50,000 | Maxsus aksiya |
| Sodiq mijoz | percentage | 15% | 1 yildan ortiq o'qiganlar |

## Munosabatlar

```php
// Discount.php
public function students(): BelongsToMany
{
    return $this->belongsToMany(Student::class)
        ->withPivot(['valid_from', 'valid_until'])
        ->withTimestamps();
}
```

## Bog'liq Modullar

- [Talabalar](./03-students.md) - Chegirma biriktirish
- [To'lovlar](./06-payments.md) - Chegirma qo'llash
- [Guruhlar](./04-groups.md) - To'lov holati hisoblash
