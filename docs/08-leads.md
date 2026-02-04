# Lidlar Moduli

Potensial mijozlarni (lidlarni) boshqarish va talabaga aylantirish.

## Fayllar

- **Komponent**: `app/Livewire/Admin/Leads.php`
- **View**: `resources/views/livewire/admin/leads.blade.php`
- **Model**: `app/Models/Lead.php`
- **Route**: `GET /admin/leads`

## Model

**Jadval**: `leads`

### Ustunlar

| Ustun | Tur | Tavsif |
|-------|-----|--------|
| id | bigint | Primary key |
| name | string | Ism |
| phone | string | Telefon raqam |
| course_id | foreignId, nullable | Qiziqtirgan kurs |
| source | enum | Qayerdan kelgan |
| status | enum | Holat |
| preferred_time | string, nullable | Qulay vaqt |
| notes | text, nullable | Izohlar |
| contacted_at | datetime, nullable | Bog'lanilgan vaqt |
| converted_student_id | foreignId, nullable | Aylangan talaba ID |

## Source (Manba)

| Qiymat | Tavsif |
|--------|--------|
| `instagram` | Instagram |
| `telegram` | Telegram |
| `google_form` | Google forma |
| `referral` | Tanish-bilish orqali |
| `walk_in` | O'zi kelgan |
| `phone_call` | Telefon qo'ng'iroq |
| `other` | Boshqa |

## Status (Holat)

```
┌─────────┐     ┌───────────┐     ┌────────────┐     ┌──────────┐
│   new   │────►│ contacted │────►│ interested │────►│ enrolled │
└─────────┘     └───────────┘     └────────────┘     └──────────┘
                      │                  │
                      ▼                  ▼
               ┌─────────────┐    ┌────────────────┐
               │  no_answer  │    │ not_interested │
               └─────────────┘    └────────────────┘
```

| Status | Tavsif |
|--------|--------|
| `new` | Yangi lid |
| `contacted` | Bog'lanildi |
| `interested` | Qiziqdi |
| `enrolled` | Ro'yxatga olindi |
| `not_interested` | Qiziqmadi |
| `no_answer` | Javob bermadi |

## Leads.php Komponent

### Filterlar

```php
#[Url] public string $filterStatus = '';
#[Url] public string $filterSource = '';
#[Url] public string $filterCourse = '';
#[Url] public string $search = '';
```

### Query

```php
public function render()
{
    $query = Lead::with('course');

    if ($this->search) {
        $query->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%");
        });
    }

    if ($this->filterStatus) {
        $query->where('status', $this->filterStatus);
    }

    if ($this->filterSource) {
        $query->where('source', $this->filterSource);
    }

    if ($this->filterCourse) {
        $query->where('course_id', $this->filterCourse);
    }

    return view('livewire.admin.leads', [
        'leads' => $query->latest()->get(),
    ]);
}
```

### CRUD

```php
// Yaratish
public function create(): void
{
    $this->reset([...]);
    $this->source = 'instagram';
    $this->status = 'new';
    $this->showModal = true;
}

// Saqlash
public function save(): void
{
    $this->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'course_id' => 'nullable|exists:courses,id',
        'source' => 'required|in:instagram,telegram,google_form,referral,walk_in,phone_call,other',
        'status' => 'required|in:new,contacted,interested,enrolled,not_interested,no_answer',
    ]);

    Lead::updateOrCreate(
        ['id' => $this->editingId],
        [
            'name' => $this->name,
            'phone' => $this->phone,
            'course_id' => $this->course_id ?: null,
            'source' => $this->source,
            'status' => $this->status,
            'preferred_time' => $this->preferred_time ?: null,
            'notes' => $this->notes ?: null,
        ]
    );

    $this->showModal = false;
}
```

### Status O'zgartirish

```php
public function updateStatus(Lead $lead, string $status): void
{
    $updateData = ['status' => $status];

    // Agar "contacted" bo'lsa, bog'lanilgan vaqtni belgilash
    if ($status === 'contacted' && !$lead->contacted_at) {
        $updateData['contacted_at'] = now();
    }

    $lead->update($updateData);
}
```

### Talabaga Aylantirish

```php
public function convertToStudent(Lead $lead): void
{
    // Yangi talaba yaratish
    $student = Student::create([
        'name' => $lead->name,
        'phone' => $lead->phone,
        'source' => $lead->source,
        'notes' => $lead->notes,
    ]);

    // Lidni yangilash
    $lead->update([
        'status' => 'enrolled',
        'converted_student_id' => $student->id,
    ]);

    // Talabalar sahifasiga yo'naltirish (ixtiyoriy)
    $this->redirect(route('admin.students'));
}
```

## Dashboard Statistikalari

```php
// Dashboard.php - stats()
$newLeads = Lead::where('status', 'new')->count();
$totalLeads = Lead::whereIn('status', ['new', 'contacted', 'interested'])->count();

return [
    'new_leads' => $newLeads,
    'total_leads' => $totalLeads,
];
```

## Oxirgi Lidlar (Dashboard)

```php
#[Computed]
public function recentLeads()
{
    return Lead::with('course')
        ->whereIn('status', ['new', 'contacted', 'interested'])
        ->latest()
        ->take(5)
        ->get();
}
```

## View Strukturasi

```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading>Lidlar</flux:heading>
        <flux:button wire:click="create">Yangi lid</flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex gap-4">
        <flux:input wire:model.live.debounce="search" placeholder="Qidirish..." />
        <flux:select wire:model.live="filterStatus">
            <option value="">Barcha holatlar</option>
            <option value="new">Yangi</option>
            <option value="contacted">Bog'lanildi</option>
            <option value="interested">Qiziqdi</option>
        </flux:select>
        <flux:select wire:model.live="filterSource">
            <option value="">Barcha manbalar</option>
            ...
        </flux:select>
    </div>

    {{-- Kanban yoki Table --}}
    @foreach ($leads as $lead)
        <div class="flex items-center justify-between">
            <div>
                <span class="font-medium">{{ $lead->name }}</span>
                <span class="text-zinc-500">{{ $lead->phone }}</span>
            </div>

            {{-- Status dropdown --}}
            <flux:dropdown>
                <flux:button variant="ghost">{{ $lead->status }}</flux:button>
                <flux:menu>
                    <flux:menu.item wire:click="updateStatus({{ $lead->id }}, 'contacted')">
                        Bog'lanildi
                    </flux:menu.item>
                    <flux:menu.item wire:click="updateStatus({{ $lead->id }}, 'interested')">
                        Qiziqdi
                    </flux:menu.item>
                    <flux:menu.item wire:click="convertToStudent({{ $lead->id }})">
                        Talabaga aylantirish
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    @endforeach
</div>
```

## Munosabatlar

```php
// Lead.php
public function course(): BelongsTo
{
    return $this->belongsTo(Course::class);
}

public function convertedStudent(): BelongsTo
{
    return $this->belongsTo(Student::class, 'converted_student_id');
}
```

## Casts

```php
protected function casts(): array
{
    return [
        'contacted_at' => 'datetime',
    ];
}
```

## Bog'liq Modullar

- [Talabalar](./03-students.md) - Lidni talabaga aylantirish
- [Kurslar](./12-courses.md) - Qiziqtirgan kurs
- [Hisobotlar](./14-reports.md) - Lid statistikasi
