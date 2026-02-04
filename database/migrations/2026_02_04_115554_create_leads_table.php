<?php

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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('home_phone')->nullable();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('source', ['instagram', 'telegram', 'google_form', 'referral', 'walk_in', 'other'])->default('other');
            $table->enum('status', ['new', 'contacted', 'interested', 'enrolled', 'not_interested', 'no_answer'])->default('new');
            $table->string('preferred_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->foreignId('converted_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
