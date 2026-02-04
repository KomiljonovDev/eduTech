<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['name' => 'Kompyuter Savodxonligi', 'code' => 'KS', 'monthly_price' => 500000],
            ['name' => 'Web Dasturlash', 'code' => 'WEB', 'monthly_price' => 800000],
            ['name' => '3D Modellashtirish', 'code' => '3D', 'monthly_price' => 800000],
            ['name' => 'Grafik Dizayn', 'code' => 'GD', 'monthly_price' => 800000],
            ['name' => 'Python Dasturlash', 'code' => 'PY', 'monthly_price' => 800000],
            ['name' => 'Kiberxavfsizlik', 'code' => 'KX', 'monthly_price' => 800000],
            ['name' => 'SMM Marketing', 'code' => 'SMM', 'monthly_price' => 800000],
            ['name' => 'Ingliz Tili', 'code' => 'ENG', 'monthly_price' => 0],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
