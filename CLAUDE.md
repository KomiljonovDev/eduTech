# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 starter kit with Livewire 4 for reactive UI components. Uses Fortify for authentication (registration, login, two-factor auth, password reset, email verification).

## Tech Stack

- **Backend**: PHP 8.3+, Laravel 12, Livewire 4, Fortify v1
- **Frontend**: Vite 7, Tailwind CSS 4, Flux UI Free v2
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Testing**: PHPUnit 11
- **Code Style**: Laravel Pint v1 (PSR-12)

## Development Commands

```bash
# Full development environment (PHP server + queue + logs + Vite)
composer dev

# Run test suite (includes lint check)
composer test

# Fix code style
composer lint
# Or directly: vendor/bin/pint --dirty --format agent

# Initial setup
composer setup

# Frontend only
npm run dev      # Development server
npm run build    # Production build
```

## Architecture

### Directory Structure

- `app/Actions/Fortify/` - Authentication actions (user creation, password reset)
- `app/Concerns/` - Shared validation traits (password rules, profile rules)
- `app/Livewire/` - Reactive Livewire components
- `app/Providers/FortifyServiceProvider.php` - Auth feature configuration
- `resources/views/pages/` - Livewire page components (settings pages)
- `resources/views/components/` - Reusable Blade components
- `resources/views/layouts/` - App and auth layouts
- `routes/settings.php` - Settings routes (separate from web.php)

### Laravel 12 Structure

- Middleware configured in `bootstrap/app.php` using `Application::configure()->withMiddleware()`
- Service providers registered in `bootstrap/providers.php`
- Console commands in `app/Console/Commands/` are auto-discovered
- No `app/Http/Kernel.php` or `app/Console/Kernel.php`

### Authentication Flow

Fortify handles all auth features. Configuration in `config/fortify.php`. Actions in `app/Actions/Fortify/` define user creation and password reset logic. Two-factor authentication uses TOTP.

### Livewire Pages

Settings pages use Livewire full-page components with named routes like `pages::settings.profile`. Views in `resources/views/pages/settings/`.

### Frontend

- Tailwind CSS 4 with Flux UI Free component library (`<flux:*>` components)
- Custom theme colors defined in `resources/css/app.css`
- Dark mode supported via appearance settings
- Use Alpine.js for client-side interactions when needed

## Testing

```bash
# Run all tests
php artisan test --compact

# Run single test file
php artisan test --compact tests/Feature/Auth/RegistrationTest.php

# Run specific test method
php artisan test --compact --filter=test_new_users_can_register
```

Tests in `tests/Feature/Auth/` cover authentication flows. Settings tests in `tests/Feature/Settings/`. Uses SQLite in-memory database. All tests must be PHPUnit classes (not Pest).

## Skills Activation

Activate domain-specific skills when working in these areas:

- `fluxui-development` - When working with Flux UI components, forms, modals, inputs
- `livewire-development` - When working with Livewire components, wire: directives, reactivity
- `tailwindcss-development` - When styling with Tailwind CSS utilities

## Laravel Boost MCP Tools

Use these tools when available:

- `search-docs` - Search version-specific Laravel documentation before making changes
- `list-artisan-commands` - Check available artisan command parameters
- `get-absolute-url` - Get correct URL scheme/domain/port for project URLs
- `tinker` - Execute PHP to debug code or query Eloquent models
- `database-query` - Read from the database
- `browser-logs` - Read browser logs and errors

## Code Conventions

### PHP

- Use PHP 8 constructor property promotion
- Always use explicit return type declarations and type hints
- Use curly braces for all control structures
- Prefer PHPDoc blocks over inline comments
- Enum keys should be TitleCase

### Laravel

- Use `php artisan make:` commands to create files (pass `--no-interaction`)
- Create Form Request classes for validation (not inline in controllers)
- Use `Model::query()` instead of `DB::`; use eager loading to prevent N+1
- Use `config()` helper, never `env()` directly outside config files
- Use named routes with `route()` function for URL generation
- When modifying columns in migrations, include all existing attributes

### Models

- Use `casts()` method rather than `$casts` property
- Create factories and seeders when creating new models
- Use proper Eloquent relationship methods with return type hints

### Testing

- Every change must be tested; run affected tests before finalizing
- Use model factories; check for existing factory states
- Create tests with `php artisan make:test --phpunit {name}`
- Do not remove tests without approval
