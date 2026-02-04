<?php

namespace App\Mcp\Tools;

use App\Models\Lead;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class LeadManagementTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Lidlar (potensial talabalar) ro'yxatini ko'rish va boshqarish.
        Holat, manba va kurs bo'yicha filtrlash imkoniyati.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $status = $request->get('status');
        $source = $request->get('source');
        $courseId = $request->get('course_id');
        $limit = $request->get('limit', 20);

        $leadsQuery = Lead::query()->with('course');

        if ($status) {
            $leadsQuery->where('status', $status);
        }

        if ($source) {
            $leadsQuery->where('source', $source);
        }

        if ($courseId) {
            $leadsQuery->where('course_id', $courseId);
        }

        $leads = $leadsQuery->orderByDesc('created_at')->limit($limit)->get();

        if ($leads->isEmpty()) {
            return Response::text('❌ Lidlar topilmadi.');
        }

        // Statistics
        $totalLeads = Lead::query()->count();
        $statusStats = Lead::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $output = "📈 LIDLAR RO'YXATI\n";
        $output .= "Ko'rsatilmoqda: {$leads->count()} / {$totalLeads}\n\n";

        $output .= "📊 HOLAT BO'YICHA:\n";
        foreach ($statusStats as $stat => $count) {
            $statLabel = match ($stat) {
                'new' => '🆕 Yangi',
                'contacted' => '📞 Bog\'lanildi',
                'interested' => '⭐ Qiziqgan',
                'enrolled' => '✅ Ro\'yxatdan o\'tdi',
                'not_interested' => '❌ Qiziqmadi',
                'no_answer' => '📵 Javob bermadi',
                default => $stat,
            };
            $output .= "  {$statLabel}: {$count}\n";
        }
        $output .= "\n";

        foreach ($leads as $lead) {
            $statusIcon = match ($lead->status) {
                'new' => '🆕',
                'contacted' => '📞',
                'interested' => '⭐',
                'enrolled' => '✅',
                'not_interested' => '❌',
                'no_answer' => '📵',
                default => '⚪',
            };

            $sourceLabel = match ($lead->source) {
                'instagram' => 'Instagram',
                'telegram' => 'Telegram',
                'referral' => 'Tavsiya',
                'walk_in' => "O'zi kelgan",
                'grand' => 'Grand',
                default => $lead->source ?? 'Noma\'lum',
            };

            $courseName = $lead->course?->name ?? 'Belgilanmagan';
            $createdAt = $lead->created_at->format('d.m.Y H:i');
            $contactedAt = $lead->contacted_at?->format('d.m.Y H:i');

            $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $output .= "{$statusIcon} #{$lead->id} {$lead->name}\n";
            $output .= "📱 Tel: {$lead->phone}\n";

            if ($lead->home_phone) {
                $output .= "🏠 Uy tel: {$lead->home_phone}\n";
            }

            $output .= "📥 Manba: {$sourceLabel}\n";
            $output .= "📖 Kurs: {$courseName}\n";
            $output .= "📅 Yaratilgan: {$createdAt}\n";

            if ($contactedAt) {
                $output .= "📞 Bog'lanilgan: {$contactedAt}\n";
            }

            if ($lead->preferred_time) {
                $output .= "⏰ Qulay vaqt: {$lead->preferred_time}\n";
            }

            if ($lead->notes) {
                $output .= "📝 Izoh: {$lead->notes}\n";
            }

            if ($lead->converted_student_id) {
                $output .= "✅ Talabaga aylandi: #{$lead->converted_student_id}\n";
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
                ->description('Lid holati')
                ->enum(['new', 'contacted', 'interested', 'enrolled', 'not_interested', 'no_answer']),
            'source' => $schema->string()
                ->title('Manba')
                ->description('Lid manbasi')
                ->enum(['instagram', 'telegram', 'referral', 'walk_in', 'grand', 'other']),
            'course_id' => $schema->integer()
                ->title('Kurs ID')
                ->description('Kurs bo\'yicha filtrlash'),
            'limit' => $schema->integer()
                ->title('Limit')
                ->description('Maksimal natijalar soni')
                ->default(20)
                ->minimum(1)
                ->maximum(100),
        ];
    }
}
