<?php

namespace App\Mcp\Tools;

use App\Models\Student;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class StudentSearchTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Talabalarni ismi, telefon raqami yoki manbasi bo'yicha qidirish.
        Talaba ma'lumotlari, guruhlari va to'lovlari haqida ma'lumot olish.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $query = $request->get('query');
        $activeOnly = $request->get('active_only', true);
        $limit = $request->get('limit', 10);

        $studentsQuery = Student::query()
            ->with(['enrollments.group.course', 'enrollments.group.teacher', 'enrollments.payments']);

        if ($activeOnly) {
            $studentsQuery->where('is_active', true);
        }

        if ($query) {
            $studentsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('home_phone', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");
            });
        }

        $students = $studentsQuery->limit($limit)->get();

        if ($students->isEmpty()) {
            return Response::text("❌ Talaba topilmadi: \"{$query}\"");
        }

        $output = "🎓 TALABALAR RO'YXATI\n";
        $output .= "Topildi: {$students->count()} ta\n\n";

        foreach ($students as $student) {
            $sourceLabel = match ($student->source) {
                'instagram' => 'Instagram',
                'telegram' => 'Telegram',
                'referral' => 'Tavsiya',
                'walk_in' => "O'zi kelgan",
                'grand' => 'Grand',
                default => $student->source ?? 'Noma\'lum',
            };

            $statusIcon = $student->is_active ? '✅' : '❌';

            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $output .= "{$statusIcon} #{$student->id} {$student->name}\n";
            $output .= "📱 Tel: {$student->phone}\n";

            if ($student->home_phone) {
                $output .= "🏠 Uy tel: {$student->home_phone}\n";
            }
            if ($student->address) {
                $output .= "📍 Manzil: {$student->address}\n";
            }

            $output .= "📥 Manba: {$sourceLabel}\n";

            if ($student->enrollments->isNotEmpty()) {
                $output .= "\n📚 Guruhlar:\n";
                foreach ($student->enrollments as $enrollment) {
                    $group = $enrollment->group;
                    $statusLabel = match ($enrollment->status) {
                        'active' => '🟢 Faol',
                        'completed' => '✅ Yakunlangan',
                        'dropped' => '🔴 Chiqib ketgan',
                        'transferred' => '🔄 Ko\'chirilgan',
                        default => $enrollment->status,
                    };

                    $totalPaid = $enrollment->payments->sum('amount');

                    $output .= "  • {$group->name} ({$group->course->name})\n";
                    $output .= "    O'qituvchi: {$group->teacher->name}\n";
                    $output .= "    Holat: {$statusLabel}\n";
                    $output .= "    To'langan: {$totalPaid} so'm\n";
                }
            }

            if ($student->notes) {
                $output .= "\n📝 Izoh: {$student->notes}\n";
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
            'query' => $schema->string()
                ->title('Qidiruv')
                ->description('Talaba ismi, telefon raqami yoki manzili'),
            'active_only' => $schema->boolean()
                ->title('Faqat faollar')
                ->description('Faqat faol talabalarni ko\'rsatish')
                ->default(true),
            'limit' => $schema->integer()
                ->title('Limit')
                ->description('Maksimal natijalar soni')
                ->default(10)
                ->minimum(1)
                ->maximum(100),
        ];
    }
}
