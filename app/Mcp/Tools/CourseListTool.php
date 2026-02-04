<?php

namespace App\Mcp\Tools;

use App\Models\Course;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class CourseListTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Barcha kurslar ro'yxatini olish.
        Har bir kurs uchun guruhlar soni, narxi va faollik holati ko'rsatiladi.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $activeOnly = $request->get('active_only', false);
        $withDetails = $request->get('with_details', false);

        $coursesQuery = Course::query()
            ->withCount(['groups', 'groups as active_groups_count' => function ($query) {
                $query->where('status', 'active');
            }])
            ->withCount('leads');

        if ($activeOnly) {
            $coursesQuery->where('is_active', true);
        }

        $courses = $coursesQuery->orderBy('name')->get();

        if ($courses->isEmpty()) {
            return Response::text('❌ Kurslar topilmadi.');
        }

        $output = "📚 KURSLAR RO'YXATI\n";
        $output .= "Jami: {$courses->count()} ta kurs\n\n";

        foreach ($courses as $course) {
            $statusIcon = $course->is_active ? '✅' : '❌';
            $price = number_format($course->monthly_price, 0, '.', ' ');

            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $output .= "{$statusIcon} {$course->name} [{$course->code}]\n";
            $output .= "💰 Oylik narx: {$price} so'm\n";
            $output .= "📊 Guruhlar: {$course->active_groups_count} faol / {$course->groups_count} jami\n";
            $output .= "📈 Lidlar: {$course->leads_count} ta\n";

            if ($course->description && $withDetails) {
                $output .= "📝 Tavsif: {$course->description}\n";
            }

            if ($withDetails) {
                $activeGroups = $course->groups()->where('status', 'active')->with('teacher')->get();
                if ($activeGroups->isNotEmpty()) {
                    $output .= "\n🎓 Faol guruhlar:\n";
                    foreach ($activeGroups as $group) {
                        $output .= "  • {$group->name} - {$group->teacher->name}\n";
                        $output .= "    {$group->schedule_label}\n";
                    }
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
            'active_only' => $schema->boolean()
                ->title('Faqat faollar')
                ->description('Faqat faol kurslarni ko\'rsatish')
                ->default(false),
            'with_details' => $schema->boolean()
                ->title('Batafsil')
                ->description('Kurs guruhlari va tavsifini ko\'rsatish')
                ->default(false),
        ];
    }
}
