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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->date('dropped_at')->nullable()->after('status');
            $table->decimal('final_balance', 12, 2)->default(0)->after('dropped_at');
            $table->string('drop_reason')->nullable()->after('final_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['dropped_at', 'final_balance', 'drop_reason']);
        });
    }
};
