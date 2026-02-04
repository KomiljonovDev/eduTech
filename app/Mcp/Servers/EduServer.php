<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AttendanceReportTool;
use App\Mcp\Tools\CourseListTool;
use App\Mcp\Tools\DashboardStatsTool;
use App\Mcp\Tools\GroupScheduleTool;
use App\Mcp\Tools\LeadManagementTool;
use App\Mcp\Tools\PaymentReportTool;
use App\Mcp\Tools\StudentSearchTool;
use Laravel\Mcp\Server;

class EduServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'EduTech Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        O'quv markaz boshqaruv tizimi uchun MCP server.

        Bu server quyidagi imkoniyatlarni taqdim etadi:
        - Dashboard statistikasi (talabalar, kurslar, guruhlar, daromad)
        - Talabalarni qidirish va ma'lumotlarini ko'rish
        - Kurslar ro'yxati va tafsilotlari
        - Guruhlar jadvali va holati
        - Lidlar boshqaruvi (potensial talabalar)
        - Davomat hisobotlari
        - To'lov va moliyaviy hisobotlar

        Barcha toollar o'zbek tilida javob beradi.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        DashboardStatsTool::class,
        StudentSearchTool::class,
        CourseListTool::class,
        GroupScheduleTool::class,
        LeadManagementTool::class,
        AttendanceReportTool::class,
        PaymentReportTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
