<?php

namespace App\Mcp\Tools;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class DashboardStatsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        O'quv markaz uchun umumiy statistika va dashboard ma'lumotlarini olish.
        Talabalar, kurslar, guruhlar, o'qituvchilar soni va moliyaviy ko'rsatkichlarni qaytaradi.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $period = $request->get('period', 'all');

        $studentsCount = Student::query()->where('is_active', true)->count();
        $coursesCount = Course::query()->where('is_active', true)->count();
        $teachersCount = Teacher::query()->where('is_active', true)->count();
        $activeGroupsCount = Group::query()->where('status', 'active')->count();

        $paymentsQuery = Payment::query();
        $leadsQuery = Lead::query();
        $attendanceQuery = Attendance::query();

        if ($period === 'month') {
            $startDate = now()->startOfMonth();
            $paymentsQuery->where('paid_at', '>=', $startDate);
            $leadsQuery->where('created_at', '>=', $startDate);
            $attendanceQuery->where('date', '>=', $startDate);
        } elseif ($period === 'week') {
            $startDate = now()->startOfWeek();
            $paymentsQuery->where('paid_at', '>=', $startDate);
            $leadsQuery->where('created_at', '>=', $startDate);
            $attendanceQuery->where('date', '>=', $startDate);
        }

        $totalRevenue = $paymentsQuery->sum('amount');
        $schoolRevenue = $paymentsQuery->sum('school_share');
        $teacherPayouts = $paymentsQuery->sum('teacher_share');

        $newLeadsCount = $leadsQuery->where('status', 'new')->count();
        $totalLeadsCount = Lead::query()->count();
        $enrolledLeadsCount = Lead::query()->where('status', 'enrolled')->count();

        $totalAttendance = $attendanceQuery->count();
        $presentCount = (clone $attendanceQuery)->where('present', true)->count();
        $attendanceRate = $totalAttendance > 0
            ? round(($presentCount / $totalAttendance) * 100, 1)
            : 0;

        $activeEnrollments = Enrollment::query()->where('status', 'active')->count();

        $groupsByStatus = Group::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $output = <<<TEXT
        📊 O'QUV MARKAZ STATISTIKASI

        👥 ASOSIY KO'RSATKICHLAR:
        • Faol talabalar: {$studentsCount}
        • Faol kurslar: {$coursesCount}
        • O'qituvchilar: {$teachersCount}
        • Faol guruhlar: {$activeGroupsCount}
        • Faol ro'yxatdan o'tishlar: {$activeEnrollments}

        💰 MOLIYAVIY KO'RSATKICHLAR:
        • Jami daromad: {$totalRevenue} so'm
        • Maktab ulushi: {$schoolRevenue} so'm
        • O'qituvchi to'lovlari: {$teacherPayouts} so'm

        📈 LIDLAR (Potensial talabalar):
        • Jami lidlar: {$totalLeadsCount}
        • Yangi lidlar: {$newLeadsCount}
        • Ro'yxatdan o'tganlar: {$enrolledLeadsCount}

        📅 DAVOMAT:
        • Davomat darajasi: {$attendanceRate}%
        • Qatnashganlar: {$presentCount}/{$totalAttendance}

        📚 GURUHLAR HOLATI:
        TEXT;

        foreach ($groupsByStatus as $status => $count) {
            $statusLabel = match ($status) {
                'pending' => 'Kutilmoqda',
                'active' => 'Faol',
                'completed' => 'Yakunlangan',
                'cancelled' => 'Bekor qilingan',
                default => $status,
            };
            $output .= "\n• {$statusLabel}: {$count}";
        }

        return Response::text($output);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()
                ->title('Davr')
                ->description("Statistika davri: 'all' (hammasi), 'month' (oy), 'week' (hafta)")
                ->enum(['all', 'month', 'week'])
                ->default('all'),
        ];
    }
}
