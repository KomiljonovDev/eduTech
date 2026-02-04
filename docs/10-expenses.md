# Xarajatlar Moduli

O'quv markaz xarajatlarini qayd qilish va hisobot olish.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Expenses.php`
- **View**: `resources/views/livewire/admin/expenses.blade.php`
- **Model**: `app/Models/Expense.php`
- **Route**: `GET /admin/expenses`

## Model

**Jadval**: `expenses`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| category | enum | Kategoriya |
| amount | decimal(12,2) | Summa |
| period | string | Davr (YYYY-MM) |
| description | text, nullable | Tavsif |
| user_id | foreignId | Kim qo'shgan |

## Kategoriyalar

| Qiymat | Tavsif | Misol |
|--------|--------|-------|
| `rent` | Ijara | Bino ijarasi |
| `utilities` | Kommunal | Elektr, suv, gaz |
| `supplies` | Ta'minot | Doskalar, markerlar |
| `salary` | Oylik | Admin oyligi |
| `marketing` | Reklama | Instagram reklama |
| `maintenance` | Ta'mirlash | Konditsioner ta'miri |
| `other` | Boshqa | Turli xarajatlar |

## Expenses.php Komponent

### Filterlar

```php
#[Url] public string $filterCategory = '';
#[Url] public string $filterPeriod = '';

public function mount(): void
{
    if (!$this->filterPeriod) {
        $this->filterPeriod = now()->format('Y-m');
    }
}
```

### Properties

```php
public bool $showModal = false;
public ?int $editingId = null;
public string $category = 'other';
public string $amount = '';
public string $period = '';
public string $description = '';
```

### Query

```php
public function render()
{
    $query = Expense::with('user');

    if ($this->filterCategory) {
        $query->where('category', $this->filterCategory);
    }

    if ($this->filterPeriod) {
        $query->where('period', $this->filterPeriod);
    }

    return view('livewire.admin.expenses', [
        'expenses' => $query->latest()->get(),
        'totalByCategory' => $this->getTotalByCategory(),
    ]);
}

private function getTotalByCategory(): array
{
    return Expense::where('period', $this->filterPeriod)
        ->selectRaw('category, SUM(amount) as total')
        ->groupBy('category')
        ->pluck('total', 'category')
        ->toArray();
}
```

### CRUD

```php
// Yaratish
public function create(): void
{
    $this->reset(['editingId', 'category', 'amount', 'description']);
    $this->category = 'other';
    $this->period = $this->filterPeriod ?: now()->format('Y-m');
    $this->showModal = true;
}

// Saqlash
public function save(): void
{
    $this->validate([
        'category' => 'required|in:rent,utilities,supplies,salary,marketing,maintenance,other',
        'amount' => 'required|numeric|min:0',
        'period' => 'required|date_format:Y-m',
        'description' => 'nullable|string|max:500',
    ]);

    Expense::updateOrCreate(
        ['id' => $this->editingId],
        [
            'category' => $this->category,
            'amount' => $this->amount,
            'period' => $this->period,
            'description' => $this->description ?: null,
            'user_id' => auth()->id(),
        ]
    );

    $this->showModal = false;
}
```

## Dashboard'da Ishlatilishi

```php
// Dashboard.php - stats()

$currentMonth = now()->format('Y-m');
$lastMonthPeriod = now()->subMonth()->format('Y-m');

// Xarajatlar
$currentExpenses = Expense::where('period', $currentMonth)->sum('amount');
$lastExpenses = Expense::where('period', $lastMonthPeriod)->sum('amount');

// Sof daromad hisoblash
$currentNetIncome = $currentMonthRevenue - $currentTeacherShare - $currentExpenses;
```

## Sof Daromad Formulasi

```
Sof daromad = Jami daromad - O'qituvchi ulushi - Xarajatlar

Misol:
- Jami daromad: 10,000,000 so'm
- O'qituvchi ulushi (50%): 5,000,000 so'm
- Xarajatlar: 2,000,000 so'm
- Sof daromad: 10,000,000 - 5,000,000 - 2,000,000 = 3,000,000 so'm
```

## View Strukturasi

```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading>Xarajatlar</flux:heading>
        <flux:button wire:click="create">Yangi xarajat</flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex gap-4">
        <flux:input type="month" wire:model.live="filterPeriod" label="Davr" />
        <flux:select wire:model.live="filterCategory">
            <option value="">Barcha kategoriyalar</option>
            <option value="rent">Ijara</option>
            <option value="utilities">Kommunal</option>
            <option value="supplies">Ta'minot</option>
            <option value="salary">Oylik</option>
            <option value="marketing">Reklama</option>
            <option value="maintenance">Ta'mirlash</option>
            <option value="other">Boshqa</option>
        </flux:select>
    </div>

    {{-- Summary Cards --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border p-4">
            <flux:text>Jami xarajat</flux:text>
            <flux:heading size="xl">
                {{ number_format(array_sum($totalByCategory), 0, '', ' ') }} so'm
            </flux:heading>
        </div>
        @foreach ($totalByCategory as $category => $total)
            <div class="rounded-lg border p-4">
                <flux:text>{{ $categoryLabels[$category] }}</flux:text>
                <flux:heading>{{ number_format($total, 0, '', ' ') }} so'm</flux:heading>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <table>
        @foreach ($expenses as $expense)
            <tr>
                <td>{{ $categoryLabels[$expense->category] }}</td>
                <td>{{ number_format($expense->amount, 0, '', ' ') }} so'm</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->created_at->format('d.m.Y') }}</td>
            </tr>
        @endforeach
    </table>

    {{-- Modal --}}
    <flux:modal wire:model="showModal">
        <flux:select wire:model="category" label="Kategoriya">
            <option value="rent">Ijara</option>
            <option value="utilities">Kommunal</option>
            ...
        </flux:select>

        <flux:input wire:model="amount" type="number" label="Summa" suffix="so'm" />
        <flux:input wire:model="period" type="month" label="Davr" />
        <flux:textarea wire:model="description" label="Tavsif" />

        <flux:button wire:click="save">Saqlash</flux:button>
    </flux:modal>
</div>
```

## Munosabatlar

```php
// Expense.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

## Casts

```php
protected function casts(): array
{
    return [
        'amount' => 'decimal:2',
    ];
}
```

## Oylik Xarajat Statistikasi

```php
// Oylik xarajatlar kategoriya bo'yicha
$monthlyExpenses = Expense::where('period', $period)
    ->selectRaw('category, SUM(amount) as total')
    ->groupBy('category')
    ->get();
```

## Bog'liq Modullar

- [Dashboard](./README.md) - Sof daromad hisoblash
- [Hisobotlar](./14-reports.md) - Moliyaviy hisobot
