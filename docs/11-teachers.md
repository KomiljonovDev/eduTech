# O'qituvchilar Moduli

O'qituvchilarni boshqarish va to'lov foizlarini sozlash.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Teachers.php`
- **View**: `resources/views/livewire/admin/teachers.blade.php`
- **Model**: `app/Models/Teacher.php`
- **Route**: `GET /admin/teachers`

## Model

**Jadval**: `teachers`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | To'liq ism |
| phone | string | Telefon raqam |
| payment_percentage | integer | To'lov foizi (default: 50) |
| is_active | boolean | Faol holati |

## To'lov Foizi

O'qituvchi har bir to'lovdan belgilangan foizni oladi:

```
To'lov: 500,000 so'm
O'qituvchi foizi: 50%
O'qituvchi ulushi: 250,000 so'm
Markaz ulushi: 250,000 so'm
```

### Turli foizlar

| O'qituvchi | Foiz | Sabab |
|------------|------|-------|
| Tajribali | 50% | Standart |
| Yangi | 40% | Sinov muddati |
| Katta tajriba | 60% | Maxsus shartnoma |

## Teachers.php Komponent

### Properties

```php
public bool $showModal = false;
public ?int $editingId = null;
public string $name = '';
public string $phone = '';
public string $payment_percentage = '50';
public bool $is_active = true;
```

### Computed

```php
#[Computed]
public function teachers()
{
    return Teacher::withCount([
        'groups',
        'groups as active_groups_count' => fn ($q) => $q->where('status', 'active')
    ])->get();
}
```

### CRUD

```php
// Yaratish
public function create(): void
{
    $this->reset(['editingId', 'name', 'phone', 'payment_percentage', 'is_active']);
    $this->payment_percentage = '50';
    $this->is_active = true;
    $this->showModal = true;
}

// Saqlash
public function save(): void
{
    $this->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'payment_percentage' => 'required|integer|min:0|max:100',
    ]);

    Teacher::updateOrCreate(
        ['id' => $this->editingId],
        [
            'name' => $this->name,
            'phone' => $this->phone,
            'payment_percentage' => $this->payment_percentage,
            'is_active' => $this->is_active,
        ]
    );

    $this->showModal = false;
}
```

## Payment Modelda Ishlatilishi

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

## Hisobotlarda

```php
// Reports.php - financialReport()
$byTeacher = $payments->groupBy('enrollment.group.teacher_id')->map(function ($items) {
    $teacher = $items->first()->enrollment->group->teacher;
    return [
        'name' => $teacher->name,
        'total' => $items->sum('amount'),           // Jami to'lovlar
        'share' => $items->sum('teacher_share'),    // O'qituvchi ulushi
        'percentage' => $teacher->payment_percentage,
    ];
});
```

## View Strukturasi

```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading>O'qituvchilar</flux:heading>
        <flux:button wire:click="create">Yangi o'qituvchi</flux:button>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>Ism</th>
                <th>Telefon</th>
                <th>Foiz</th>
                <th>Guruhlar</th>
                <th>Holat</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->teachers as $teacher)
                <tr>
                    <td>{{ $teacher->name }}</td>
                    <td>{{ $teacher->phone }}</td>
                    <td>{{ $teacher->payment_percentage }}%</td>
                    <td>
                        {{ $teacher->active_groups_count }} faol /
                        {{ $teacher->groups_count }} jami
                    </td>
                    <td>
                        <flux:badge :color="$teacher->is_active ? 'green' : 'red'">
                            {{ $teacher->is_active ? 'Faol' : 'Nofaol' }}
                        </flux:badge>
                    </td>
                    <td>
                        <flux:button wire:click="edit({{ $teacher->id }})" size="sm">
                            Tahrirlash
                        </flux:button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Modal --}}
    <flux:modal wire:model="showModal">
        <flux:input wire:model="name" label="Ism" />
        <flux:input wire:model="phone" label="Telefon" />
        <flux:input
            wire:model="payment_percentage"
            type="number"
            label="To'lov foizi"
            suffix="%"
            min="0"
            max="100"
        />
        <flux:checkbox wire:model="is_active" label="Faol" />
        <flux:button wire:click="save">Saqlash</flux:button>
    </flux:modal>
</div>
```

## Munosabatlar

```php
// Teacher.php
public function groups(): HasMany
{
    return $this->hasMany(Group::class);
}
```

## Faol O'qituvchilar

```php
// Groups.php - computed
#[Computed]
public function teachers()
{
    return Teacher::where('is_active', true)->get();
}
```

## Guruh Yaratishda

```blade
{{-- Groups.php modal --}}
<flux:select wire:model="teacher_id" label="O'qituvchi">
    <option value="">Tanlang...</option>
    @foreach ($this->teachers as $teacher)
        <option value="{{ $teacher->id }}">
            {{ $teacher->name }} ({{ $teacher->payment_percentage }}%)
        </option>
    @endforeach
</flux:select>
```

## Bog'liq Modullar

- [Guruhlar](./04-groups.md) - O'qituvchi tayinlash
- [To'lovlar](./06-payments.md) - Ulush hisoblash
- [Hisobotlar](./14-reports.md) - O'qituvchi daromadi
