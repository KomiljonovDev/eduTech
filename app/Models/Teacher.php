<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'payment_percentage',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payment_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
