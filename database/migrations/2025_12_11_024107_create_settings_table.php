<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Add this

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed the initial value for the AI Prompt
        DB::table('settings')->insert([
            'key' => 'ai_strict_instruction',
            'value' => 'Answer in Malay. Provide a concise answer in a numbered list. DO NOT PROVIDE EXPLANATIONS FOR EACH ITEM. START THE ANSWER IMMEDIATELY WITH NUMBER 1. DO NOT PROVIDE ANY INTRODUCTION OR PREAMBLE.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};