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
    // 1. The Quiz Itself
    Schema::create('quizzes', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->integer('duration')->default(600); // In seconds
        $table->integer('max_attempts')->default(3);
        $table->string('difficulty')->default('Medium'); // Easy, Medium, Hard
        $table->timestamps();
    });

    // 2. The Questions inside a Quiz
    Schema::create('questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
        $table->text('text'); // The question text
        $table->text('explanation')->nullable(); // Feedback for students
        $table->timestamps();
    });

    // 3. The Options (A, B, C, D) for a Question
    Schema::create('question_options', function (Blueprint $table) {
        $table->id();
        $table->foreignId('question_id')->constrained()->cascadeOnDelete();
        $table->string('text'); // The answer text
        $table->boolean('is_correct')->default(false); // Is this the right answer?
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('question_options');
    Schema::dropIfExists('questions');
    Schema::dropIfExists('quizzes');
}
};
