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
        // Check if table exists before creating to prevent errors
        if (!Schema::hasTable('quiz_accesses')) {
            Schema::create('quiz_accesses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
                $table->integer('extra_attempts')->default(0);
                $table->timestamps();
            });
        }
    }
};
