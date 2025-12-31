<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TABLE 1: THE QUIZ (Holds Settings + ALL Questions inside 'content')
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->default(600);
            $table->string('difficulty')->default('Medium');
            
            // 🔥 ALL QUESTIONS & OPTIONS LIVE HERE AS JSON
            $table->json('content'); 
            
            $table->timestamps();
        });

        // TABLE 2: THE SCORE
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->integer('score');
            $table->integer('total_questions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quizzes');
    }
};