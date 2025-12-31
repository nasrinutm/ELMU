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
    Schema::create('student_manual_activities', function (Blueprint $table) {
        $table->id();
        // Links the activity to a specific user (student)
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // The name of the manual activity (e.g., "Midterm Exam")
        $table->string('title');
        
        // The score received (0-100)
        $table->integer('score');
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_manual_activities');
    }
};
