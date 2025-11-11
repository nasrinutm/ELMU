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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body'); // The main content of the post
            
            // This is the foreign key for the user
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // If user is deleted, delete their posts

            $table->timestamps(); // Handles created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};