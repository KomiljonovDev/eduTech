<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    /** @use HasFactory<\Database\Factories\DiscountFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot(['valid_from', 'valid_until', 'notes'])
            ->withTimestamps();
    }

    public function getFormattedValueAttribute(): string
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        }

        return number_format($this->value, 0, '', ' ') . " so'm";
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percentage') {
            return $amount * ($this->value / 100);
        }

        return min($this->value, $amount);
    }
}
