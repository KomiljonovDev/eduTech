<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Room;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * Demo ma'lumotlar uchun seeder.
 *
 * Bu seeder default seederlar bilan birga ishlamaydi.
 * Qo'lda ishga tushirish: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Demo ma\'lumotlar yaratilmoqda...');

        // O'zbek ismlari
        $maleNames = [
            'Azizbek', 'Sardor', 'Javohir', 'Shoxrux', 'Bekzod', 'Dilshod',
            'Firdavs', 'Jasur', 'Kamron', 'Mirjalol', 'Nodirbek', 'Otabek',
            'Sanjar', 'Temur', 'Ulugbek', 'Valijon', 'Xurshid', 'Yusuf',
            'Zafar', 'Abdulloh', 'Bobur', 'Diyorbek', 'Elyor', 'Farrux',
        ];

        $femaleNames = [
            'Madina', 'Nilufar', 'Ozoda', 'Sevinch', 'Dilnoza', 'Feruza',
            'Gulnora', 'Hilola', 'Iroda', 'Jamila', 'Kamola', 'Lola',
            'Malika', 'Nodira', 'Oysha', 'Parizod', 'Robiya', 'Saida',
        ];

        $allNames = array_merge($maleNames, $femaleNames);
        shuffle($allNames);

        // Mavjud ma'lumotlarni olish
        $courses = Course::all()->keyBy('code');
        $teachers = Teacher::all();
        $rooms = Room::all();

        if ($courses->isEmpty() || $teachers->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Avval default seederlarni ishga tushiring: php artisan db:seed');

            return;
        }

        // ==========================================
        // YANVAR OYIDA OCHILGAN GURUHLAR (o'tgan oy)
        // ==========================================
        $this->command->info('Yanvar guruhlari yaratilmoqda...');

        $januaryGroups = [
            [
                'name' => 'KS-15',
                'course_code' => 'KS',
                'teacher' => 'Isroiljon',
                'room' => '1-xona',
                'days' => 'odd',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'start_date' => '2026-01-06',
                'total_lessons' => 24,
                'student_count' => 8,
            ],
            [
                'name' => 'WEB-7',
                'course_code' => 'WEB',
                'teacher' => 'Ozodbek',
                'room' => '2-xona',
                'days' => 'even',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'start_date' => '2026-01-07',
                'total_lessons' => 48,
                'student_count' => 6,
            ],
            [
                'name' => 'PY-3',
                'course_code' => 'PY',
                'teacher' => 'Jasurbek T',
                'room' => '3-xona',
                'days' => 'odd',
                'start_time' => '16:00',
                'end_time' => '18:00',
                'start_date' => '2026-01-15',
                'total_lessons' => 36,
                'student_count' => 7,
            ],
            [
                'name' => 'GD-4',
                'course_code' => 'GD',
                'teacher' => 'Umarxon',
                'room' => '4-xona',
                'days' => 'even',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'start_date' => '2026-01-14',
                'total_lessons' => 36,
                'student_count' => 5,
            ],
            [
                'name' => '3D-2',
                'course_code' => '3D',
                'teacher' => 'Behruz',
                'room' => '1-xona',
                'days' => 'even',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'start_date' => '2026-01-21',
                'total_lessons' => 36,
                'student_count' => 4,
            ],
        ];

        $nameIndex = 0;

        foreach ($januaryGroups as $groupData) {
            $group = Group::create([
                'name' => $groupData['name'],
                'course_id' => $courses[$groupData['course_code']]->id,
                'teacher_id' => $teachers->firstWhere('name', $groupData['teacher'])->id,
                'room_id' => $rooms->firstWhere('name', $groupData['room'])->id,
                'days' => $groupData['days'],
                'start_time' => $groupData['start_time'],
                'end_time' => $groupData['end_time'],
                'start_date' => $groupData['start_date'],
                'total_lessons' => $groupData['total_lessons'],
                'status' => 'active',
            ]);

            // Har bir guruhga o'quvchilar qo'shish
            for ($i = 0; $i < $groupData['student_count']; $i++) {
                $student = Student::create([
                    'name' => $allNames[$nameIndex % count($allNames)],
                    'phone' => '+998'.rand(90, 99).rand(1000000, 9999999),
                    'source' => collect(['instagram', 'telegram', 'referral', 'walk_in'])->random(),
                    'is_active' => true,
                ]);

                Enrollment::create([
                    'student_id' => $student->id,
                    'group_id' => $group->id,
                    'enrolled_at' => $groupData['start_date'],
                    'status' => 'active',
                ]);

                $nameIndex++;
            }

            $this->command->line("  ✓ {$group->name} - {$groupData['student_count']} o'quvchi");
        }

        // ==========================================
        // FEVRAL OYIDA OCHILGAN GURUHLAR (joriy oy)
        // ==========================================
        $this->command->info('Fevral guruhlari yaratilmoqda...');

        $februaryGroups = [
            [
                'name' => 'KS-16',
                'course_code' => 'KS',
                'teacher' => 'Isroiljon',
                'room' => '2-xona',
                'days' => 'even',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'start_date' => '2026-02-03',
                'total_lessons' => 24,
                'student_count' => 6,
            ],
            [
                'name' => 'SMM-1',
                'course_code' => 'SMM',
                'teacher' => 'Raxmatillo',
                'room' => '3-xona',
                'days' => 'even',
                'start_time' => '18:00',
                'end_time' => '20:00',
                'start_date' => '2026-02-04',
                'total_lessons' => 24,
                'student_count' => 5,
            ],
            [
                'name' => 'KX-1',
                'course_code' => 'KX',
                'teacher' => 'Zohid',
                'room' => '4-xona',
                'days' => 'odd',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'start_date' => '2026-02-05',
                'total_lessons' => 36,
                'student_count' => 4,
            ],
        ];

        foreach ($februaryGroups as $groupData) {
            $group = Group::create([
                'name' => $groupData['name'],
                'course_id' => $courses[$groupData['course_code']]->id,
                'teacher_id' => $teachers->firstWhere('name', $groupData['teacher'])->id,
                'room_id' => $rooms->firstWhere('name', $groupData['room'])->id,
                'days' => $groupData['days'],
                'start_time' => $groupData['start_time'],
                'end_time' => $groupData['end_time'],
                'start_date' => $groupData['start_date'],
                'total_lessons' => $groupData['total_lessons'],
                'status' => 'active',
            ]);

            // Har bir guruhga o'quvchilar qo'shish
            for ($i = 0; $i < $groupData['student_count']; $i++) {
                $student = Student::create([
                    'name' => $allNames[$nameIndex % count($allNames)],
                    'phone' => '+998'.rand(90, 99).rand(1000000, 9999999),
                    'source' => collect(['instagram', 'telegram', 'referral', 'walk_in'])->random(),
                    'is_active' => true,
                ]);

                Enrollment::create([
                    'student_id' => $student->id,
                    'group_id' => $group->id,
                    'enrolled_at' => $groupData['start_date'],
                    'status' => 'active',
                ]);

                $nameIndex++;
            }

            $this->command->line("  ✓ {$group->name} - {$groupData['student_count']} o'quvchi");
        }

        // Xulosa
        $totalGroups = count($januaryGroups) + count($februaryGroups);
        $totalStudents = collect($januaryGroups)->sum('student_count') + collect($februaryGroups)->sum('student_count');

        $this->command->newLine();
        $this->command->info("Yaratildi: {$totalGroups} ta guruh, {$totalStudents} ta o'quvchi");
        $this->command->info("To'lovlar va davomat kiritilmagan - fevraldan boshlab qo'lda kiriting.");
    }
}
