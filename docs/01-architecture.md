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
│   └── Dashboard.php       # Asosiy dashboard
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
├── admin.php               # Admin routelar
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

Spatie Laravel Permission ishlatiladi:

```php
// Rol tekshirish
if ($user->hasRole('manager')) {
    // Admin panel
}

// Middleware
Route::middleware(['role:manager'])->group(function () {
    // Admin routes
});
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
