<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['name' => 'Isroiljon', 'payment_percentage' => 50],
            ['name' => 'Ozodbek', 'payment_percentage' => 50],
            ['name' => 'Jasurbek T', 'payment_percentage' => 50],
            ['name' => 'Umarxon', 'payment_percentage' => 50],
            ['name' => 'Behruz', 'payment_percentage' => 50],
            ['name' => 'Raxmatillo', 'payment_percentage' => 50],
            ['name' => 'Zohid', 'payment_percentage' => 50],
            ['name' => 'Jasur', 'payment_percentage' => 50],
        ];

        foreach ($teachers as $teacher) {
            Teacher::create($teacher);
        }
    }
}
