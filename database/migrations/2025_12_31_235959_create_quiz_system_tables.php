<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. QUIZZES (Parent Table)
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->default(600); // Seconds
            $table->integer('max_attempts')->default(3);
            $table->string('difficulty')->default('Medium');
            $table->timestamps();
        });

        // 2. QUESTIONS (Belongs to Quiz)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->text('text');
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        // 3. OPTIONS (Belongs to Question)
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // 4. ATTEMPTS (Connects User + Quiz)
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->string('quiz_title')->nullable();
            $table->integer('score')->default(0);
            $table->integer('total_questions')->default(0);
            $table->timestamps();
        });

        // 5. QUIZ ACCESS (For Extra Attempts)
        Schema::create('quiz_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->integer('extra_attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_access');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }
};