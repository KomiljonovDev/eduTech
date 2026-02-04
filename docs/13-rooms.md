# Xonalar Moduli

O'quv xonalarini boshqarish va sig'imni nazorat qilish.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Rooms.php`
- **View**: `resources/views/livewire/admin/rooms.blade.php`
- **Model**: `app/Models/Room.php`
- **Route**: `GET /admin/rooms`

## Model

**Jadval**: `rooms`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Xona nomi |
| capacity | integer | Sig'im (o'rinlar soni) |
| is_active | boolean | Faol holati |

## Xona Misollari

| Nomi | Sig'im | Tavsif |
|------|--------|--------|
| A1 | 15 | Katta xona |
| A2 | 10 | O'rta xona |
| B1 | 8 | Kichik xona |
| Lab | 20 | Kompyuter lab |

## Rooms.php Komponent

### Properties

```php
public bool $showModal = false;
public ?int $editingId = null;
public string $name = '';
public string $capacity = '10';
public bool $is_active = true;
```

### Computed

```php
#[Computed]
public function rooms()
{
    return Room::withCount([
        'groups',
        'groups as active_groups_count' => fn ($q) => $q->where('status', 'active'),
    ])->get();
}
```

### CRUD

```php
// Yaratish
public function create(): void
{
    $this->reset(['editingId', 'name', 'capacity', 'is_active']);
    $this->capacity = '10';
    $this->is_active = true;
    $this->showModal = true;
}

// Saqlash
public function save(): void
{
    $this->validate([
        'name' => 'required|string|max:255',
        'capacity' => 'required|integer|min:1|max:100',
    ]);

    Room::updateOrCreate(
        ['id' => $this->editingId],
        [
            'name' => $this->name,
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
        ]
    );

    $this->showModal = false;
}
```

## Xona To'lganlik Darajasi

Guruh statistikasida xona sig'imi ishlatiladi:

```php
// Reports.php - groupStats()
->map(function ($group) {
    return [
        'group' => $group,
        'active_students' => $group->active_count,
        'capacity' => $group->room->capacity,
        'fill_rate' => $group->room->capacity > 0
            ? round(($group->active_count / $group->room->capacity) * 100)
            : 0,
    ];
});
```

### Fill Rate (To'lganlik)

| Foiz | Holat | Rang |
|------|-------|------|
| 80%+ | Yaxshi | Yashil |
| 50-79% | O'rta | Sariq |
| <50% | Past | Qizil |

## View Strukturasi

```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading>Xonalar</flux:heading>
        <flux:button wire:click="create">Yangi xona</flux:button>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>Nomi</th>
                <th>Sig'im</th>
                <th>Faol guruhlar</th>
                <th>Holat</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->rooms as $room)
                <tr>
                    <td>{{ $room->name }}</td>
                    <td>{{ $room->capacity }} o'rin</td>
                    <td>{{ $room->active_groups_count }}</td>
                    <td>
                        <flux:badge :color="$room->is_active ? 'green' : 'red'">
                            {{ $room->is_active ? 'Faol' : 'Nofaol' }}
                        </flux:badge>
                    </td>
                    <td>
                        <flux:button wire:click="edit({{ $room->id }})" size="sm">
                            Tahrirlash
                        </flux:button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Modal --}}
    <flux:modal wire:model="showModal">
        <flux:input wire:model="name" label="Xona nomi" />
        <flux:input wire:model="capacity" type="number" label="Sig'im" suffix="o'rin" />
        <flux:checkbox wire:model="is_active" label="Faol" />
        <flux:button wire:click="save">Saqlash</flux:button>
    </flux:modal>
</div>
```

## Guruh Yaratishda

```blade
{{-- Groups.php modal --}}
<flux:select wire:model="room_id" label="Xona">
    <option value="">Tanlang...</option>
    @foreach ($this->rooms as $room)
        <option value="{{ $room->id }}">
            {{ $room->name }} ({{ $room->capacity }} o'rin)
        </option>
    @endforeach
</flux:select>
```

## Xona Bo'shligini Tekshirish

Bir xonada bir vaqtda bitta guruh bo'lishi kerak:

```php
// Xona band yoki yo'qligini tekshirish
$isRoomBusy = Group::where('room_id', $roomId)
    ->where('status', 'active')
    ->where('days', $days)  // odd yoki even
    ->where(function ($q) use ($startTime, $endTime) {
        // Vaqt kesishishi
        $q->whereBetween('start_time', [$startTime, $endTime])
            ->orWhereBetween('end_time', [$startTime, $endTime]);
    })
    ->exists();
```

## Munosabatlar

```php
// Room.php
public function groups(): HasMany
{
    return $this->hasMany(Group::class);
}
```

## Faol Xonalar

```php
// Faqat faol xonalar (dropdown uchun)
#[Computed]
public function rooms()
{
    return Room::where('is_active', true)->get();
}
```

## Bog'liq Modullar

- [Guruhlar](./04-groups.md) - Xona tayinlash
- [Hisobotlar](./14-reports.md) - To'lganlik statistikasi
