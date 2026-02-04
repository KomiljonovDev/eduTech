<?php

namespace App\Mcp\Tools;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class AttendanceReportTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Davomat hisobotlarini ko'rish.
        Guruh yoki talaba bo'yicha davomat statistikasi.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $groupId = $request->get('group_id');
        $studentId = $request->get('student_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($groupId) {
            return $this->groupReport($groupId, $dateFrom, $dateTo);
        }

        if ($studentId) {
            return $this->studentReport($studentId, $dateFrom, $dateTo);
        }

        return $this->overallReport($dateFrom, $dateTo);
    }

    private function groupReport(int $groupId, ?string $dateFrom, ?string $dateTo): Response
    {
        $group = Group::query()
            ->with(['course', 'teacher', 'enrollments.student'])
            ->find($groupId);

        if (! $group) {
            return Response::error("Guruh topilmadi: #{$groupId}");
        }

        $enrollmentIds = $group->enrollments->pluck('id');

        $attendanceQuery = Attendance::query()
            ->whereIn('enrollment_id', $enrollmentIds);

        if ($dateFrom) {
            $attendanceQuery->where('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $attendanceQuery->where('date', '<=', $dateTo);
        }

        $totalRecords = $attendanceQuery->count();
        $presentCount = (clone $attendanceQuery)->where('present', true)->count();
        $absentCount = $totalRecords - $presentCount;
        $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;

        $output = "📊 GURUH DAVOMAT HISOBOTI\n\n";
        $output .= "📚 Guruh: {$group->name}\n";
        $output .= "📖 Kurs: {$group->course->name}\n";
        $output .= "👨‍🏫 O'qituvchi: {$group->teacher->name}\n";
        $output .= "👥 Talabalar: {$group->enrollments->count()}\n\n";

        $output .= "📈 UMUMIY STATISTIKA:\n";
        $output .= "  ✅ Qatnashgan: {$presentCount}\n";
        $output .= "  ❌ Qatnashmagan: {$absentCount}\n";
        $output .= "  📊 Davomat darajasi: {$attendanceRate}%\n\n";

        $output .= "👥 TALABALAR BO'YICHA:\n";
        $output .= str_repeat('─', 40)."\n";

        foreach ($group->enrollments as $enrollment) {
            $studentAttendance = Attendance::query()
                ->where('enrollment_id', $enrollment->id);

            if ($dateFrom) {
                $studentAttendance->where('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $studentAttendance->where('date', '<=', $dateTo);
            }

            $studentTotal = $studentAttendance->count();
            $studentPresent = (clone $studentAttendance)->where('present', true)->count();
            $studentRate = $studentTotal > 0 ? round(($studentPresent / $studentTotal) * 100, 1) : 0;

            $rateIcon = $studentRate >= 80 ? '🟢' : ($studentRate >= 60 ? '🟡' : '🔴');

            $output .= "{$rateIcon} {$enrollment->student->name}\n";
            $output .= "   Qatnashdi: {$studentPresent}/{$studentTotal} ({$studentRate}%)\n";
        }

        return Response::text($output);
    }

    private function studentReport(int $studentId, ?string $dateFrom, ?string $dateTo): Response
    {
        $enrollments = Enrollment::query()
            ->where('student_id', $studentId)
            ->with(['student', 'group.course', 'group.teacher', 'attendances'])
            ->get();

        if ($enrollments->isEmpty()) {
            return Response::error("Talaba topilmadi yoki guruhlarga ro'yxatdan o'tmagan: #{$studentId}");
        }

        $student = $enrollments->first()->student;

        $output = "📊 TALABA DAVOMAT HISOBOTI\n\n";
        $output .= "🎓 Talaba: {$student->name}\n";
        $output .= "📱 Tel: {$student->phone}\n\n";

        $totalPresent = 0;
        $totalAbsent = 0;

        foreach ($enrollments as $enrollment) {
            $attendanceQuery = $enrollment->attendances();

            if ($dateFrom) {
                $attendanceQuery->where('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $attendanceQuery->where('date', '<=', $dateTo);
            }

            $records = $attendanceQuery->get();
            $present = $records->where('present', true)->count();
            $absent = $records->where('present', false)->count();
            $total = $records->count();
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            $totalPresent += $present;
            $totalAbsent += $absent;

            $rateIcon = $rate >= 80 ? '🟢' : ($rate >= 60 ? '🟡' : '🔴');

            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $output .= "📚 {$enrollment->group->name}\n";
            $output .= "📖 {$enrollment->group->course->name}\n";
            $output .= "{$rateIcon} Davomat: {$present}/{$total} ({$rate}%)\n";
        }

        $overallTotal = $totalPresent + $totalAbsent;
        $overallRate = $overallTotal > 0 ? round(($totalPresent / $overallTotal) * 100, 1) : 0;

        $output .= "\n📈 UMUMIY: {$totalPresent}/{$overallTotal} ({$overallRate}%)\n";

        return Response::text($output);
    }

    private function overallReport(?string $dateFrom, ?string $dateTo): Response
    {
        $attendanceQuery = Attendance::query();

        if ($dateFrom) {
            $attendanceQuery->where('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $attendanceQuery->where('date', '<=', $dateTo);
        }

        $totalRecords = $attendanceQuery->count();
        $presentCount = (clone $attendanceQuery)->where('present', true)->count();
        $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;

        // Group statistics
        $groupStats = DB::table('attendances')
            ->join('enrollments', 'attendances.enrollment_id', '=', 'enrollments.id')
            ->join('groups', 'enrollments.group_id', '=', 'groups.id')
            ->select('groups.id', 'groups.name')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN attendances.present = 1 THEN 1 ELSE 0 END) as present')
            ->when($dateFrom, fn ($q) => $q->where('attendances.date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('attendances.date', '<=', $dateTo))
            ->groupBy('groups.id', 'groups.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $output = "📊 UMUMIY DAVOMAT HISOBOTI\n\n";

        if ($dateFrom || $dateTo) {
            $output .= "📅 Davr: ".($dateFrom ?? '...').'-'.($dateTo ?? '...')."\n\n";
        }

        $output .= "📈 UMUMIY STATISTIKA:\n";
        $output .= "  ✅ Qatnashgan: {$presentCount}\n";
        $output .= "  ❌ Qatnashmagan: ".($totalRecords - $presentCount)."\n";
        $output .= "  📊 Davomat darajasi: {$attendanceRate}%\n\n";

        if ($groupStats->isNotEmpty()) {
            $output .= "📚 GURUHLAR BO'YICHA (Top 10):\n";
            $output .= str_repeat('─', 40)."\n";

            foreach ($groupStats as $stat) {
                $rate = $stat->total > 0 ? round(($stat->present / $stat->total) * 100, 1) : 0;
                $rateIcon = $rate >= 80 ? '🟢' : ($rate >= 60 ? '🟡' : '🔴');

                $output .= "{$rateIcon} {$stat->name}\n";
                $output .= "   {$stat->present}/{$stat->total} ({$rate}%)\n";
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
            'group_id' => $schema->integer()
                ->title('Guruh ID')
                ->description('Guruh bo\'yicha hisobot'),
            'student_id' => $schema->integer()
                ->title('Talaba ID')
                ->description('Talaba bo\'yicha hisobot'),
            'date_from' => $schema->string()
                ->title('Boshlanish sanasi')
                ->description('Hisobot boshlanish sanasi (YYYY-MM-DD)'),
            'date_to' => $schema->string()
                ->title('Tugash sanasi')
                ->description('Hisobot tugash sanasi (YYYY-MM-DD)'),
        ];
    }
}
