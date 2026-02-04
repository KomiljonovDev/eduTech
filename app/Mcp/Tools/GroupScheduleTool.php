<?php

namespace App\Mcp\Tools;

use App\Models\Group;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class GroupScheduleTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Guruhlar jadvalini ko'rish.
        Dars kunlari, vaqti, xona va o'qituvchi ma'lumotlari bilan.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $status = $request->get('status', 'active');
        $days = $request->get('days');
        $teacherId = $request->get('teacher_id');
        $courseId = $request->get('course_id');

        $groupsQuery = Group::query()
            ->with(['course', 'teacher', 'room'])
            ->withCount(['enrollments as students_count' => function ($query) {
                $query->where('status', 'active');
            }]);

        if ($status !== 'all') {
            $groupsQuery->where('status', $status);
        }

        if ($days) {
            $groupsQuery->where('days', $days);
        }

        if ($teacherId) {
            $groupsQuery->where('teacher_id', $teacherId);
        }

        if ($courseId) {
            $groupsQuery->where('course_id', $courseId);
        }

        $groups = $groupsQuery->orderBy('start_time')->get();

        if ($groups->isEmpty()) {
            return Response::text('❌ Guruhlar topilmadi.');
        }

        $output = "📅 GURUHLAR JADVALI\n";
        $output .= "Jami: {$groups->count()} ta guruh\n\n";

        $groupedByDays = $groups->groupBy('days');

        foreach ($groupedByDays as $day => $dayGroups) {
            $dayLabel = $day === 'odd' ? '📆 DU-CHOR-JUM (Toq kunlar)' : '📆 SE-PAY-SHAN (Juft kunlar)';
            $output .= "{$dayLabel}\n";
            $output .= str_repeat('─', 40)."\n";

            foreach ($dayGroups as $group) {
                $statusIcon = match ($group->status) {
                    'pending' => '🟡',
                    'active' => '🟢',
                    'completed' => '✅',
                    'cancelled' => '🔴',
                    default => '⚪',
                };

                $startTime = $group->start_time?->format('H:i') ?? '--:--';
                $endTime = $group->end_time?->format('H:i') ?? '--:--';
                $roomName = $group->room?->name ?? 'Belgilanmagan';

                $output .= "\n{$statusIcon} {$group->name}\n";
                $output .= "   ⏰ {$startTime} - {$endTime}\n";
                $output .= "   📖 {$group->course->name}\n";
                $output .= "   👨‍🏫 {$group->teacher->name}\n";
                $output .= "   🚪 Xona: {$roomName}\n";
                $output .= "   👥 Talabalar: {$group->students_count}\n";

                if ($group->start_date || $group->end_date) {
                    $startDate = $group->start_date?->format('d.m.Y') ?? '...';
                    $endDate = $group->end_date?->format('d.m.Y') ?? '...';
                    $output .= "   📆 Davr: {$startDate} - {$endDate}\n";
                }

                if ($group->total_lessons) {
                    $output .= "   📚 Jami darslar: {$group->total_lessons}\n";
                }
            }

            $output .= "\n";
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
            'status' => $schema->string()
                ->title('Holat')
                ->description("Guruh holati: 'all', 'pending', 'active', 'completed', 'cancelled'")
                ->enum(['all', 'pending', 'active', 'completed', 'cancelled'])
                ->default('active'),
            'days' => $schema->string()
                ->title('Kunlar')
                ->description("Dars kunlari: 'odd' (Du-Chor-Jum) yoki 'even' (Se-Pay-Shan)")
                ->enum(['odd', 'even']),
            'teacher_id' => $schema->integer()
                ->title('O\'qituvchi ID')
                ->description('O\'qituvchi bo\'yicha filtrlash'),
            'course_id' => $schema->integer()
                ->title('Kurs ID')
                ->description('Kurs bo\'yicha filtrlash'),
        ];
    }
}
