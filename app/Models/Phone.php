<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Phone extends Model
{
    protected $fillable = [
        'phoneable_type',
        'phoneable_id',
        'number',
        'owner',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function phoneable(): MorphTo
    {
        return $this->morphTo();
    }
}
