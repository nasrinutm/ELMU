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
        // Wrap in a check to ensure we don't crash if the table was partially created
        if (!Schema::hasTable('activity_user')) {
            Schema::create('activity_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
                
                // The status (e.g., Pending, Completed)
                $table->string('status')->default('Pending');
                
                // The score (Decimal ensures precision, handled by Vue for display)
                $table->decimal('score', 5, 2)->nullable();
                
                // Date Completed
                $table->timestamp('submitted_at')->nullable();
                
                // Distinguish between system and manual
                $table->boolean('is_manual')->default(false);
                
                $table->timestamps();

                $table->unique(['user_id', 'activity_id']); 

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop if it exists to avoid errors during rollbacks
        Schema::dropIfExists('activity_user');
    }
};