<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'outcome',
        'phone_called',
        'phone_owner',
        'notes',
        'next_contact_date',
        'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'next_contact_date' => 'date',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    public static function getOutcomeLabels(): array
    {
        return [
            'answered' => 'Javob berdi',
            'no_answer' => 'Javob bermadi',
            'busy' => 'Band edi',
            'callback_requested' => "Qayta qo'ng'iroq so'radi",
            'interested' => 'Qiziqdi',
            'not_interested' => 'Qiziqmadi',
            'enrolled' => 'Yozildi',
            'other' => 'Boshqa',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getOutcomeColors(): array
    {
        return [
            'answered' => 'blue',
            'no_answer' => 'zinc',
            'busy' => 'yellow',
            'callback_requested' => 'orange',
            'interested' => 'green',
            'not_interested' => 'red',
            'enrolled' => 'emerald',
            'other' => 'zinc',
        ];
    }
}
