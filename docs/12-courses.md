# Kurslar Moduli

O'quv kurslarini boshqarish va narxlash.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Courses.php`
- **View**: `resources/views/livewire/admin/courses.blade.php`
- **Model**: `app/Models/Course.php`
- **Route**: `GET /admin/courses`

## Model

**Jadval**: `courses`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Kurs nomi |
| code | string | Qisqa kod |
| description | text, nullable | Tavsif |
| monthly_price | decimal(12,2) | Oylik narx |
| is_active | boolean | Faol holati |

## Kurs Kodlari Misoli

| Kod | Nomi | Narx |
|-----|------|------|
| KS | Kompyuter savodxonligi | 300,000 |
| WD | Web Development | 500,000 |
| GD | Grafik Dizayn | 450,000 |
| SMM | SMM Marketing | 400,000 |
| EN | Ingliz tili | 350,000 |

## Courses.php Komponent

### Properties

```php
public bool $showModal = false;
public ?int $editingId = null;
public string $name = '';
public string $code = '';
public string $description = '';
public string $monthly_price = '';
public bool $is_active = true;
```

### Computed

```php
#[Computed]
public function courses()
{
    return Course::withCount([
        'groups',
        'groups as active_groups_count' => fn ($q) => $q->where('status', 'active'),
        'leads' => fn ($q) => $q->whereIn('status', ['new', 'contacted', 'interested']),
    ])->get();
}
```

### CRUD

```php
// Yaratish
public function create(): void
{
    $this->reset(['editingId', 'name', 'code', 'description', 'monthly_price', 'is_active']);
    $this->is_active = true;
    $this->showModal = true;
}

// Saqlash
public function save(): void
{
    $this->validate([
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:courses,code,' . $this->editingId,
        'monthly_price' => 'required|numeric|min:0',
    ]);

    Course::updateOrCreate(
        ['id' => $this->editingId],
        [
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'description' => $this->description ?: null,
            'monthly_price' => $this->monthly_price,
            'is_active' => $this->is_active,
        ]
    );

    $this->showModal = false;
}
```

## To'lovda Ishlatilishi

Kurs narxi to'lov hisoblashda asosiy qiymat:

```php
// GroupDetail.php
public function getPaymentStatusForPeriod(Enrollment $enrollment): array
{
    // Kurs oylik narxi
    $coursePrice = $this->group->course->monthly_price;

    // Chegirma
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
        <flux:heading>Kurslar</flux:heading>
        <flux:button wire:click="create">Yangi kurs</flux:button>
    </div>

    {{-- Cards yoki Table --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->courses as $course)
            <div class="rounded-lg border p-4">
                <div class="flex items-center justify-between">
                    <flux:badge>{{ $course->code }}</flux:badge>
                    <flux:badge :color="$course->is_active ? 'green' : 'red'">
                        {{ $course->is_active ? 'Faol' : 'Nofaol' }}
                    </flux:badge>
                </div>

                <flux:heading size="lg" class="mt-2">{{ $course->name }}</flux:heading>

                @if ($course->description)
                    <flux:text class="mt-1">{{ $course->description }}</flux:text>
                @endif

                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xl font-bold">
                        {{ number_format($course->monthly_price, 0, '', ' ') }} so'm
                    </span>
                    <span class="text-sm text-zinc-500">oyiga</span>
                </div>

                <div class="mt-4 flex gap-4 text-sm text-zinc-500">
                    <span>{{ $course->active_groups_count }} faol guruh</span>
                    <span>{{ $course->leads_count }} lid</span>
                </div>

                <div class="mt-4">
                    <flux:button wire:click="edit({{ $course->id }})" size="sm" variant="ghost">
                        Tahrirlash
                    </flux:button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal --}}
    <flux:modal wire:model="showModal">
        <flux:input wire:model="name" label="Kurs nomi" />
        <flux:input wire:model="code" label="Qisqa kod" placeholder="WD" />
        <flux:textarea wire:model="description" label="Tavsif" />
        <flux:input wire:model="monthly_price" type="number" label="Oylik narx" suffix="so'm" />
        <flux:checkbox wire:model="is_active" label="Faol" />
        <flux:button wire:click="save">Saqlash</flux:button>
    </flux:modal>
</div>
```

## Guruh Yaratishda

```blade
{{-- Groups.php modal --}}
<flux:select wire:model="course_id" label="Kurs">
    <option value="">Tanlang...</option>
    @foreach ($this->courses as $course)
        <option value="{{ $course->id }}">
            {{ $course->code }} - {{ $course->name }}
            ({{ number_format($course->monthly_price, 0, '', ' ') }} so'm)
        </option>
    @endforeach
</flux:select>
```

## Lid Yaratishda

```blade
{{-- Leads.php modal --}}
<flux:select wire:model="course_id" label="Qiziqtirgan kurs">
    <option value="">Tanlang...</option>
    @foreach ($this->courses as $course)
        <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
    @endforeach
</flux:select>
```

## Munosabatlar

```php
// Course.php
public function groups(): HasMany
{
    return $this->hasMany(Group::class);
}

public function leads(): HasMany
{
    return $this->hasMany(Lead::class);
}
```

## Faol Kurslar

```php
// Faqat faol kurslar (dropdown uchun)
#[Computed]
public function courses()
{
    return Course::where('is_active', true)->get();
}
```

## Bog'liq Modullar

- [Guruhlar](./04-groups.md) - Kurs asosida guruh yaratish
- [Lidlar](./08-leads.md) - Qiziqtirgan kurs
- [To'lovlar](./06-payments.md) - Kurs narxi asosida to'lov
