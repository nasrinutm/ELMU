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
    Schema::table('activities', function (Blueprint $table) {
        $table->integer('time_limit')->nullable(); // In minutes
        $table->json('quiz_data')->nullable(); // Stores questions, options, answers
    });
}

public function down(): void
{
    Schema::table('activities', function (Blueprint $table) {
        $table->dropColumn(['time_limit', 'quiz_data']);
    });
}
};
