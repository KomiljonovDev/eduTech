# Arxitektura

## Umumiy Ko'rinish

EduTech tizimi Laravel 12 va Livewire 4 asosida qurilgan. Full-stack reaktiv UI komponentlar Livewire orqali boshqariladi.

## Texnologiya Stack

### Backend
- **PHP 8.3+** - Asosiy til
- **Laravel 12** - Framework
- **Livewire 4** - Reaktiv UI komponentlar
- **Fortify v1** - Autentifikatsiya (login, register, 2FA, password reset)
- **Spatie Permission** - Rol va ruxsatlar tizimi

### Frontend
- **Vite 7** - Asset bundler
- **Tailwind CSS 4** - Stillar
- **Flux UI Free v2** - UI komponentlar kutubxonasi (`<flux:*>`)
- **Alpine.js** - Client-side interaktivlik

### Database
- **SQLite** - Default (development)
- **MySQL/PostgreSQL** - Production uchun

### Queue & Jobs
- **Laravel Horizon** - Queue monitoring
- **Redis/Database** - Queue driver

## Papka Tuzilishi

```
app/
├── Actions/Fortify/        # Autentifikatsiya actions
├── Concerns/               # Shared validation traits
├── Console/Commands/       # Artisan commands
├── Http/Controllers/       # HTTP controllers (kam ishlatiladi)
├── Jobs/                   # Queue jobs
├── Livewire/               # Livewire komponentlar
│   ├── Actions/            # Action komponentlar (Logout)
│   ├── Admin/              # Admin panel komponentlar
│   ├── Teacher/            # O'qituvchi panel komponentlar
│   ├── Student/            # O'quvchi panel komponentlar
│   └── Dashboard.php       # Asosiy dashboard (faqat manager)
├── Mcp/                    # AI integration
│   ├── Servers/            # MCP serverlar
│   └── Tools/              # MCP tools
├── Models/                 # Eloquent modellar
├── Providers/              # Service providers
└── Services/               # Business logic services

bootstrap/
├── app.php                 # Middleware konfiguratsiyasi
└── providers.php           # Service provider registration

config/
├── fortify.php             # Auth konfiguratsiyasi
├── livewire.php            # Livewire settings
└── ...

database/
├── factories/              # Model factories (testing)
├── migrations/             # Database migrations
└── seeders/                # Database seeders

resources/views/
├── components/             # Blade komponentlar
├── layouts/                # App va auth layoutlar
├── livewire/               # Livewire view'lar
│   ├── admin/              # Admin panel view'lar
│   └── dashboard.blade.php
└── pages/                  # Livewire page komponentlar

routes/
├── web.php                 # Asosiy routelar
├── admin.php               # Admin routelar (role:manager)
├── teacher.php             # O'qituvchi routelar (role:teacher)
├── student.php             # O'quvchi routelar (role:student)
├── settings.php            # Settings routelar
├── ai.php                  # MCP routelar
└── console.php             # Console routelar
```

## Middleware Konfiguratsiyasi

Laravel 12 da middleware `bootstrap/app.php` da konfiguratsiya qilinadi:

```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        // Route middleware
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling
    })
    ->create();
```

## Livewire Komponent Anatomiyasi

### PHP Komponenti
```php
<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]           // Layout
#[Title('Sahifa Nomi')]             // Title
class ExampleComponent extends Component
{
    // Public properties (state)
    public string $search = '';

    #[Url]                          // URL query parameter
    public string $filter = '';

    // Computed properties (cached)
    #[Computed]
    public function items()
    {
        return Model::query()->get();
    }

    // Actions
    public function save(): void
    {
        $this->validate();
        // ...
    }

    public function render()
    {
        return view('livewire.admin.example');
    }
}
```

### Blade View
```blade
<div class="space-y-6">
    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Qidirish..." />

    {{-- List --}}
    @foreach ($this->items as $item)
        <div wire:key="{{ $item->id }}">
            {{ $item->name }}
            <flux:button wire:click="delete({{ $item->id }})">
                O'chirish
            </flux:button>
        </div>
    @endforeach

    {{-- Modal --}}
    <flux:modal wire:model="showModal">
        <flux:input wire:model="name" label="Nomi" />
        <flux:button wire:click="save">Saqlash</flux:button>
    </flux:modal>
</div>
```

## Database Munosabatlari

```
User (1) ──────────────────────────── (*) Expense

Student (1) ───────────────────────── (*) Enrollment
Student (*) ───────────────────────── (*) Discount (pivot: discount_student)

Teacher (1) ───────────────────────── (*) Group

Course (1) ────────────────────────── (*) Group
Course (1) ────────────────────────── (*) Lead

Room (1) ──────────────────────────── (*) Group

Group (1) ─────────────────────────── (*) Enrollment

Enrollment (1) ────────────────────── (*) Payment
Enrollment (1) ────────────────────── (*) Attendance
Enrollment (1) ────────────────────── (1) Student
Enrollment (1) ────────────────────── (1) Group

Lead (*) ──────────────────────────── (1) Student (converted_student_id, nullable)
```

## Autentifikatsiya

Fortify orqali boshqariladi:

- **Login/Logout** - Session based
- **Registration** - Custom validation rules
- **Password Reset** - Email orqali
- **Email Verification** - `verified` middleware
- **Two-Factor Auth** - TOTP (Google Authenticator)

```php
// config/fortify.php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirmPassword' => true,
    ]),
],
```

## Rollar va Ruxsatlar

Spatie Laravel Permission ishlatiladi.

### Mavjud Rollar

| Rol | Tavsif | Dashboard Route |
|-----|--------|-----------------|
| `manager` | Admin/Menejer | `/dashboard` |
| `teacher` | O'qituvchi | `/teacher/dashboard` |
| `student` | O'quvchi | `/student/dashboard` |

### Middleware Konfiguratsiyasi

```php
// bootstrap/app.php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

### Route Himoyasi

```php
// Admin routelar - faqat manager
Route::middleware(['auth', 'verified', 'role:manager'])->group(...);

// O'qituvchi routelar - faqat teacher
Route::middleware(['auth', 'verified', 'role:teacher'])->group(...);

// O'quvchi routelar - faqat student
Route::middleware(['auth', 'verified', 'role:student'])->group(...);
```

### Dashboard Redirect

Asosiy `/dashboard` route faqat `manager` roli uchun. Boshqa rollar o'z dashboardlariga avtomatik redirect qilinadi:

```php
// Dashboard.php mount() metodida
if ($user->hasRole('teacher')) {
    $this->redirect(route('teacher.dashboard'));
}

if ($user->hasRole('student')) {
    $this->redirect(route('student.dashboard'));
}
```

## Qo'llanma Tizimi

Platforma ichida foydalanuvchi qo'llanmasi mavjud (`/help`). Har bir rol faqat o'ziga tegishli qo'llanmani ko'radi.

### Mavjud Qo'llanmalar

| Rol | Bo'limlar |
|-----|-----------|
| `manager` | Dashboard, Leadlar, O'quvchilar, Guruhlar, Davomat, Dars jadvali, Qarzdorliklar, Xarajatlar, Hisobotlar, Ustozlar, Yo'nalishlar, Xonalar, Chegirmalar |
| `teacher` | Dashboard, Dars jadvali, Davomat, Hisobim |
| `student` | Dashboard, Dars jadvali, To'lovlar |

### Qo'llanma Komponentlari

```blade
{{-- Qo'llanma sahifasiga link --}}
<x-help-button section="students" title="Yordam" />

{{-- Modal ko'rinishida yordam --}}
<x-help-modal title="O'quvchilar">
    <p>Bu yerda o'quvchilar haqida ma'lumot...</p>
</x-help-modal>
```

## Konvensiyalar

### Nomlash
- **Modellar**: Singular, PascalCase (`Student`, `Enrollment`)
- **Jadvallar**: Plural, snake_case (`students`, `enrollments`)
- **Livewire**: PascalCase (`GroupDetail.php`)
- **Views**: kebab-case (`group-detail.blade.php`)

### Kod Stili
- Laravel Pint (PSR-12)
- `composer lint` - formatlash
- PHP 8 constructor property promotion
- Explicit return types

### Testing
- PHPUnit 11
- `composer test`
- Feature tests for auth flows
- Model factories for data
