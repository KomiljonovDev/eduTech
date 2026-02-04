# EduTech - O'quv Markaz Boshqaruv Tizimi

O'quv markazlarini avtomatlashtirish va boshqarish uchun yaratilgan tizim.

## Texnologiyalar

- **Backend**: PHP 8.3+, Laravel 12, Livewire 4, Fortify v1
- **Frontend**: Vite 7, Tailwind CSS 4, Flux UI Free v2
- **Database**: SQLite (default), MySQL/PostgreSQL qo'llab-quvvatlanadi
- **Queue**: Laravel Horizon
- **AI Integration**: Laravel MCP (Model Context Protocol)

## Asosiy Modullar

| Modul | Fayl | Tavsif |
|-------|------|--------|
| [Arxitektura](./01-architecture.md) | - | Tizim arxitekturasi va tuzilishi |
| [Modellar](./02-models.md) | - | Barcha Eloquent modellar va munosabatlar |
| [Talabalar](./03-students.md) | `Students.php` | Talabalarni boshqarish |
| [Guruhlar](./04-groups.md) | `Groups.php`, `GroupDetail.php` | Guruhlar va dars jadvali |
| [Ro'yxatga olish](./05-enrollments.md) | - | Talaba-guruh bog'lanishi |
| [To'lovlar](./06-payments.md) | - | Moliyaviy operatsiyalar |
| [Davomat](./07-attendance.md) | `Attendance.php` | Davomat qayd etish |
| [Lidlar](./08-leads.md) | `Leads.php` | Potensial mijozlar |
| [Chegirmalar](./09-discounts.md) | `Discounts.php` | Chegirma tizimi |
| [Xarajatlar](./10-expenses.md) | `Expenses.php` | Xarajatlar hisobi |
| [O'qituvchilar](./11-teachers.md) | `Teachers.php` | O'qituvchilarni boshqarish |
| [Kurslar](./12-courses.md) | `Courses.php` | Kurs katalogi |
| [Xonalar](./13-rooms.md) | `Rooms.php` | O'quv xonalari |
| [Hisobotlar](./14-reports.md) | `Reports.php` | Statistika va hisobotlar |
| [MCP Tools](./15-mcp-tools.md) | `app/Mcp/Tools/` | AI agent vositalari |
| [SMS Xizmat](./16-sms-service.md) | `EskizSmsService.php` | SMS yuborish |

## Ma'lumotlar Bazasi Diagrammasi

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Student   │────<│  Enrollment │>────│    Group    │
└─────────────┘     └─────────────┘     └─────────────┘
      │                   │                    │
      │                   │                    │
      ▼                   ▼                    ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Discount   │     │   Payment   │     │   Course    │
└─────────────┘     │  Attendance │     │   Teacher   │
                    └─────────────┘     │    Room     │
                                        └─────────────┘
```

## Routelar

### Asosiy
- `GET /` - Bosh sahifa
- `GET /dashboard` - Dashboard

### Admin (`/admin/*`)
Barcha admin routelari `auth`, `verified`, `role:manager` middleware bilan himoyalangan.

| Route | Komponent | Tavsif |
|-------|-----------|--------|
| `/admin/students` | `Students` | Talabalar ro'yxati |
| `/admin/groups` | `Groups` | Guruhlar ro'yxati |
| `/admin/groups/{group}` | `GroupDetail` | Guruh tafsilotlari |
| `/admin/teachers` | `Teachers` | O'qituvchilar |
| `/admin/courses` | `Courses` | Kurslar |
| `/admin/rooms` | `Rooms` | Xonalar |
| `/admin/leads` | `Leads` | Lidlar |
| `/admin/discounts` | `Discounts` | Chegirmalar |
| `/admin/attendance` | `Attendance` | Davomat |
| `/admin/expenses` | `Expenses` | Xarajatlar |
| `/admin/reports` | `Reports` | Hisobotlar |

## Ishga Tushirish

```bash
# O'rnatish
composer setup

# Development server
composer dev

# Testlar
composer test

# Kod formatlash
composer lint
```

## Fayl Tuzilishi

```
app/
├── Livewire/
│   ├── Dashboard.php
│   └── Admin/
│       ├── Students.php
│       ├── Groups.php
│       ├── GroupDetail.php
│       └── ...
├── Models/
│   ├── Student.php
│   ├── Group.php
│   ├── Enrollment.php
│   └── ...
├── Mcp/
│   ├── Servers/EduServer.php
│   └── Tools/
│       ├── StudentSearchTool.php
│       └── ...
└── Services/
    └── EskizSmsService.php
```
