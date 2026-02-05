<?php

use App\Models\Phone;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_primary column to phones table
        Schema::table('phones', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('owner');
        });

        // Migrate existing student phone and home_phone to phones table
        Student::query()
            ->whereNotNull('phone')
            ->orWhereNotNull('home_phone')
            ->each(function (Student $student) {
                // Migrate primary phone
                if ($student->phone) {
                    Phone::create([
                        'phoneable_type' => Student::class,
                        'phoneable_id' => $student->id,
                        'number' => $student->phone,
                        'owner' => null,
                        'is_primary' => true,
                    ]);
                }

                // Migrate home phone
                if ($student->home_phone) {
                    Phone::create([
                        'phoneable_type' => Student::class,
                        'phoneable_id' => $student->id,
                        'number' => $student->home_phone,
                        'owner' => 'Uy',
                        'is_primary' => false,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate phones back to students table
        Phone::query()
            ->where('phoneable_type', Student::class)
            ->where('is_primary', true)
            ->each(function (Phone $phone) {
                Student::where('id', $phone->phoneable_id)
                    ->update(['phone' => $phone->number]);
            });

        Phone::query()
            ->where('phoneable_type', Student::class)
            ->where('owner', 'Uy')
            ->each(function (Phone $phone) {
                Student::where('id', $phone->phoneable_id)
                    ->update(['home_phone' => $phone->number]);
            });

        // Delete migrated phones
        Phone::query()
            ->where('phoneable_type', Student::class)
            ->where(function ($query) {
                $query->where('is_primary', true)
                    ->orWhere('owner', 'Uy');
            })
            ->delete();

        Schema::table('phones', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
