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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('teacher_share', 12, 2)->default(0);
            $table->decimal('school_share', 12, 2)->default(0);
            $table->date('paid_at');
            $table->string('period')->nullable();
            $table->enum('method', ['cash', 'card', 'transfer'])->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
