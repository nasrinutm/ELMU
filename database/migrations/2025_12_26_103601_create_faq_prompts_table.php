<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * * This creates the table to store high-accuracy FAQ prompts 
     * derived from the textbook knowledge base.
     */
    public function up(): void
    {
        Schema::create('faq_prompts', function (Blueprint $table) {
            $table->id();
            
            // The text shown to students in the Chatbot UI (e.g., "5 Prinsip Reka Bentuk")
            $table->string('label'); 
            
            // The highly detailed prompt sent to Gemini to ensure it retrieves 
            // the correct data from the PDF (e.g., "Rujuk Bab 3, terangkan...")
            $table->text('system_prompt'); 
            
            // Optional: Order in which they appear in the UI chips
            $table->integer('sort_order')->default(0); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_prompts');
    }
};