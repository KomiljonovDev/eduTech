<?php

namespace App\Mcp\Tools;

use App\Models\Payment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class PaymentReportTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        To'lov va moliyaviy hisobotlarni ko'rish.
        Daromad, o'qituvchi to'lovlari va maktab ulushi statistikasi.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $period = $request->get('period', 'month');
        $teacherId = $request->get('teacher_id');
        $courseId = $request->get('course_id');
        $method = $request->get('method');

        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => null,
        };

        $paymentsQuery = Payment::query()
            ->with(['enrollment.student', 'enrollment.group.course', 'enrollment.group.teacher']);

        if ($startDate) {
            $paymentsQuery->where('paid_at', '>=', $startDate);
        }

        if ($method) {
            $paymentsQuery->where('method', $method);
        }

        if ($teacherId || $courseId) {
            $paymentsQuery->whereHas('enrollment.group', function ($query) use ($teacherId, $courseId) {
                if ($teacherId) {
                    $query->where('teacher_id', $teacherId);
                }
                if ($courseId) {
                    $query->where('course_id', $courseId);
                }
            });
        }

        $payments = $paymentsQuery->orderByDesc('paid_at')->get();

        $totalAmount = $payments->sum('amount');
        $totalSchoolShare = $payments->sum('school_share');
        $totalTeacherShare = $payments->sum('teacher_share');

        $periodLabel = match ($period) {
            'week' => 'Shu hafta',
            'month' => 'Shu oy',
            'year' => 'Shu yil',
            default => 'Barcha vaqt',
        };

        $output = "💰 MOLIYAVIY HISOBOT\n";
        $output .= "📅 Davr: {$periodLabel}\n\n";

        $output .= "📊 UMUMIY KO'RSATKICHLAR:\n";
        $output .= str_repeat('─', 40)."\n";
        $output .= "💵 Jami daromad: ".number_format($totalAmount, 0, '.', ' ')." so'm\n";
        $output .= "🏫 Maktab ulushi: ".number_format($totalSchoolShare, 0, '.', ' ')." so'm\n";
        $output .= "👨‍🏫 O'qituvchi to'lovlari: ".number_format($totalTeacherShare, 0, '.', ' ')." so'm\n";
        $output .= "📝 To'lovlar soni: {$payments->count()}\n\n";

        // By payment method
        $byMethod = $payments->groupBy('method');
        if ($byMethod->isNotEmpty()) {
            $output .= "💳 TO'LOV USULLARI:\n";
            foreach ($byMethod as $paymentMethod => $methodPayments) {
                $methodLabel = match ($paymentMethod) {
                    'cash' => '💵 Naqd',
                    'card' => '💳 Karta',
                    'transfer' => '🏦 O\'tkazma',
                    default => $paymentMethod,
                };
                $methodTotal = $methodPayments->sum('amount');
                $output .= "  {$methodLabel}: ".number_format($methodTotal, 0, '.', ' ')." so'm ({$methodPayments->count()} ta)\n";
            }
            $output .= "\n";
        }

        // By teacher
        $teacherStats = DB::table('payments')
            ->join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
            ->join('groups', 'enrollments.group_id', '=', 'groups.id')
            ->join('teachers', 'groups.teacher_id', '=', 'teachers.id')
            ->select('teachers.id', 'teachers.name')
            ->selectRaw('SUM(payments.amount) as total_amount')
            ->selectRaw('SUM(payments.teacher_share) as teacher_share')
            ->selectRaw('COUNT(*) as payment_count')
            ->when($startDate, fn ($q) => $q->where('payments.paid_at', '>=', $startDate))
            ->when($method, fn ($q) => $q->where('payments.method', $method))
            ->when($teacherId, fn ($q) => $q->where('teachers.id', $teacherId))
            ->groupBy('teachers.id', 'teachers.name')
            ->orderByDesc('total_amount')
            ->get();

        if ($teacherStats->isNotEmpty()) {
            $output .= "👨‍🏫 O'QITUVCHILAR BO'YICHA:\n";
            $output .= str_repeat('─', 40)."\n";

            foreach ($teacherStats as $stat) {
                $output .= "• {$stat->name}\n";
                $output .= "  Jami: ".number_format($stat->total_amount, 0, '.', ' ')." so'm\n";
                $output .= "  Ulushi: ".number_format($stat->teacher_share, 0, '.', ' ')." so'm\n";
                $output .= "  To'lovlar: {$stat->payment_count} ta\n";
            }
            $output .= "\n";
        }

        // By course
        $courseStats = DB::table('payments')
            ->join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
            ->join('groups', 'enrollments.group_id', '=', 'groups.id')
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->select('courses.id', 'courses.name')
            ->selectRaw('SUM(payments.amount) as total_amount')
            ->selectRaw('COUNT(*) as payment_count')
            ->when($startDate, fn ($q) => $q->where('payments.paid_at', '>=', $startDate))
            ->when($method, fn ($q) => $q->where('payments.method', $method))
            ->when($courseId, fn ($q) => $q->where('courses.id', $courseId))
            ->groupBy('courses.id', 'courses.name')
            ->orderByDesc('total_amount')
            ->get();

        if ($courseStats->isNotEmpty()) {
            $output .= "📚 KURSLAR BO'YICHA:\n";
            $output .= str_repeat('─', 40)."\n";

            foreach ($courseStats as $stat) {
                $output .= "• {$stat->name}\n";
                $output .= "  Jami: ".number_format($stat->total_amount, 0, '.', ' ')." so'm ({$stat->payment_count} ta)\n";
            }
            $output .= "\n";
        }

        // Recent payments
        $recentPayments = $payments->take(10);
        if ($recentPayments->isNotEmpty()) {
            $output .= "📋 SO'NGGI TO'LOVLAR:\n";
            $output .= str_repeat('─', 40)."\n";

            foreach ($recentPayments as $payment) {
                $paidAt = $payment->paid_at?->format('d.m.Y') ?? '---';
                $studentName = $payment->enrollment?->student?->name ?? 'Noma\'lum';
                $courseName = $payment->enrollment?->group?->course?->name ?? '---';
                $methodIcon = match ($payment->method) {
                    'cash' => '💵',
                    'card' => '💳',
                    'transfer' => '🏦',
                    default => '💰',
                };

                $output .= "{$methodIcon} {$paidAt} - {$studentName}\n";
                $output .= "   {$courseName}: ".number_format($payment->amount, 0, '.', ' ')." so'm\n";
            }
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
                ->description("Hisobot davri: 'all', 'week', 'month', 'year'")
                ->enum(['all', 'week', 'month', 'year'])
                ->default('month'),
            'teacher_id' => $schema->integer()
                ->title('O\'qituvchi ID')
                ->description('O\'qituvchi bo\'yicha filtrlash'),
            'course_id' => $schema->integer()
                ->title('Kurs ID')
                ->description('Kurs bo\'yicha filtrlash'),
            'method' => $schema->string()
                ->title('To\'lov usuli')
                ->description('To\'lov usuli bo\'yicha filtrlash')
                ->enum(['cash', 'card', 'transfer']),
        ];
    }
}
