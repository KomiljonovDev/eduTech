# MCP Tools (AI Agent Vositalari)

Laravel MCP (Model Context Protocol) orqali AI agentlar uchun vositalar.

## Fayllar

- **Server**: `app/Mcp/Servers/EduServer.php`
- **Tools**: `app/Mcp/Tools/`
- **Route**: `routes/ai.php`

## Server Konfiguratsiyasi

```php
// app/Mcp/Servers/EduServer.php
namespace App\Mcp\Servers;

use Anthropic\Mcp\Server;

class EduServer extends Server
{
    protected string $name = 'edu';
    protected string $version = '1.0.0';

    protected array $tools = [
        DashboardStatsTool::class,
        StudentSearchTool::class,
        CourseListTool::class,
        GroupScheduleTool::class,
        LeadManagementTool::class,
        AttendanceReportTool::class,
        PaymentReportTool::class,
    ];
}
```

## Route Registratsiyasi

```php
// routes/ai.php
use App\Mcp\Servers\EduServer;
use Anthropic\Mcp\Facades\Mcp;

Mcp::web('/mcp/edu', EduServer::class);
Mcp::local('edu', EduServer::class);
```

---

## 1. DashboardStatsTool

Umumiy statistikani olish.

### Input

```json
{
    "include_financial": true  // Moliyaviy ma'lumotlarni qo'shish (ixtiyoriy)
}
```

### Output

```json
{
    "students": {
        "total": 150,
        "active": 120,
        "waiting": 30
    },
    "groups": {
        "total": 15,
        "active": 10,
        "pending": 5
    },
    "leads": {
        "new": 25,
        "total_active": 45
    },
    "financial": {
        "current_month_revenue": 15000000,
        "teacher_share": 7500000,
        "school_share": 7500000,
        "outstanding": 3500000
    }
}
```

### Kod

```php
class DashboardStatsTool extends Tool
{
    public string $name = 'dashboard_stats';
    public string $description = "O'quv markaz umumiy statistikasini olish";

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'include_financial' => [
                    'type' => 'boolean',
                    'description' => 'Moliyaviy ma\'lumotlarni qo\'shish',
                ],
            ],
        ];
    }

    public function execute(array $params): array
    {
        $stats = [
            'students' => [
                'total' => Student::count(),
                'active' => Student::whereHas('enrollments', fn ($q) => $q->where('status', 'active'))->count(),
            ],
            'groups' => [
                'active' => Group::where('status', 'active')->count(),
                'pending' => Group::where('status', 'pending')->count(),
            ],
            // ...
        ];

        if ($params['include_financial'] ?? false) {
            $stats['financial'] = [...];
        }

        return $stats;
    }
}
```

---

## 2. StudentSearchTool

Talabalarni qidirish.

### Input

```json
{
    "query": "Ali",           // Ism yoki telefon
    "status": "active",       // active, waiting, all (ixtiyoriy)
    "limit": 10               // Natija soni (ixtiyoriy, default: 10)
}
```

### Output

```json
{
    "students": [
        {
            "id": 1,
            "name": "Alisher Karimov",
            "phone": "+998901234567",
            "source": "instagram",
            "active_groups": ["WD-1", "EN-3"],
            "total_paid": 1500000,
            "discounts": ["10% chegirma"]
        }
    ],
    "total": 5
}
```

### Kod

```php
class StudentSearchTool extends Tool
{
    public string $name = 'student_search';
    public string $description = "Talabalarni ism yoki telefon bo'yicha qidirish";

    public function execute(array $params): array
    {
        $query = Student::with(['enrollments.group.course', 'activeDiscounts']);

        if ($params['query']) {
            $query->where(function ($q) use ($params) {
                $q->where('name', 'like', "%{$params['query']}%")
                    ->orWhere('phone', 'like', "%{$params['query']}%");
            });
        }

        if ($params['status'] === 'active') {
            $query->whereHas('enrollments', fn ($q) => $q->where('status', 'active'));
        }

        $students = $query->limit($params['limit'] ?? 10)->get();

        return [
            'students' => $students->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'phone' => $s->phone,
                'active_groups' => $s->enrollments
                    ->where('status', 'active')
                    ->map(fn ($e) => $e->group->name)
                    ->values(),
            ]),
            'total' => $students->count(),
        ];
    }
}
```

---

## 3. CourseListTool

Kurslar ro'yxatini olish.

### Input

```json
{
    "active_only": true  // Faqat faol kurslar (ixtiyoriy)
}
```

### Output

```json
{
    "courses": [
        {
            "id": 1,
            "code": "WD",
            "name": "Web Development",
            "monthly_price": 500000,
            "active_groups": 3,
            "total_students": 45
        }
    ]
}
```

---

## 4. GroupScheduleTool

Guruh jadvalini olish.

### Input

```json
{
    "date": "2024-01-15",     // Sana (ixtiyoriy, default: bugun)
    "days": "odd"             // odd, even, all (ixtiyoriy)
}
```

### Output

```json
{
    "date": "2024-01-15",
    "day_type": "odd",
    "groups": [
        {
            "id": 1,
            "name": "WD-1",
            "course": "Web Development",
            "teacher": "Sardor Usmonov",
            "room": "A1",
            "time": "09:00 - 11:00",
            "students_count": 12
        }
    ]
}
```

---

## 5. LeadManagementTool

Lidlarni boshqarish.

### Input

```json
{
    "action": "list",         // list, get, update_status
    "status": "new",          // Filter (ixtiyoriy)
    "lead_id": 1,             // Get/Update uchun
    "new_status": "contacted" // Update uchun
}
```

### Output (list)

```json
{
    "leads": [
        {
            "id": 1,
            "name": "Jamshid",
            "phone": "+998901234567",
            "course": "Web Development",
            "status": "new",
            "source": "instagram",
            "created_at": "2024-01-15"
        }
    ],
    "counts": {
        "new": 10,
        "contacted": 15,
        "interested": 8
    }
}
```

---

## 6. AttendanceReportTool

Davomat hisoboti.

### Input

```json
{
    "group_id": 1,            // Guruh ID (ixtiyoriy)
    "date_from": "2024-01-01",
    "date_to": "2024-01-31"
}
```

### Output

```json
{
    "summary": {
        "total_lessons": 12,
        "average_attendance": 85
    },
    "students": [
        {
            "id": 1,
            "name": "Alisher",
            "present": 10,
            "absent": 2,
            "percentage": 83
        }
    ]
}
```

---

## 7. PaymentReportTool

To'lov hisoboti.

### Input

```json
{
    "period": "2024-01",      // Davr
    "group_by": "teacher"     // teacher, course, method (ixtiyoriy)
}
```

### Output

```json
{
    "total": 15000000,
    "teacher_share": 7500000,
    "school_share": 7500000,
    "count": 120,
    "by_teacher": [
        {
            "name": "Sardor Usmonov",
            "total": 5000000,
            "share": 2500000
        }
    ],
    "recent_payments": [
        {
            "student": "Alisher",
            "amount": 500000,
            "method": "cash",
            "date": "2024-01-15"
        }
    ]
}
```

---

## Tool Yaratish Qoidalari

### Base Tool

```php
namespace App\Mcp\Tools;

use Anthropic\Mcp\Tool;

class ExampleTool extends Tool
{
    public string $name = 'example_tool';
    public string $description = 'Tool tavsifi';

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'param1' => [
                    'type' => 'string',
                    'description' => 'Parametr tavsifi',
                ],
            ],
            'required' => ['param1'],
        ];
    }

    public function execute(array $params): array
    {
        // Logika
        return ['result' => 'value'];
    }
}
```

### Qoidalar

1. **Read-only**: Faqat o'qish operatsiyalari
2. **Idempotent**: Bir xil natija har safar
3. **O'zbek tili**: Tavsiflar o'zbek tilida
4. **Error handling**: Xatolarni to'g'ri qaytarish

```php
public function execute(array $params): array
{
    try {
        // ...
    } catch (\Exception $e) {
        return [
            'error' => true,
            'message' => $e->getMessage(),
        ];
    }
}
```

## Ishlatilishi

### Claude Desktop

```json
// claude_desktop_config.json
{
    "mcpServers": {
        "edu": {
            "command": "php",
            "args": ["artisan", "mcp:serve", "edu"],
            "cwd": "/path/to/eduTech"
        }
    }
}
```

### Web API

```
POST /mcp/edu
Content-Type: application/json

{
    "method": "tools/call",
    "params": {
        "name": "student_search",
        "arguments": {
            "query": "Ali"
        }
    }
}
```
