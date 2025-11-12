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
        Schema::create('replies', function (Blueprint $table) {
            $table->id();
            $table->text('body'); // The content of the reply

            // Foreign key for the user who replied
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Foreign key for the post this reply belongs to
            $table->foreignId('post_id')
                  ->constrained('posts')
                  ->onDelete('cascade'); // If post is deleted, delete replies

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};