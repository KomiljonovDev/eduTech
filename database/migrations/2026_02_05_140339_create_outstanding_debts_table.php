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
        Schema::create('outstanding_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->decimal('original_amount', 12, 2)->comment('Boshlangich qarz summasi');
            $table->decimal('paid_amount', 12, 2)->default(0)->comment('Tolangan summa');
            $table->decimal('remaining_amount', 12, 2)->comment('Qolgan qarz');
            $table->enum('status', ['pending', 'partial', 'paid', 'written_off'])->default('pending');
            $table->enum('reason', ['completed', 'dropped', 'transferred'])->comment('Qarz sababi');
            $table->integer('lessons_attended')->default(0)->comment('Qatnashgan darslar');
            $table->integer('lessons_total')->default(0)->comment('Jami darslar');
            $table->date('due_date')->nullable()->comment('Tolov muddati');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'remaining_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outstanding_debts');
    }
};
