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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // For the material's name/title
            $table->string('subject'); // As specified in US003-01
            $table->text('description')->nullable(); // As specified in US003-01
            $table->string('file_path'); // The path to the file in storage
            $table->string('file_name'); // The original file name (e.g., "notes.pdf")
            $table->string('file_type'); // e.g., "PDF", "DOCX"

            // This links the material to the user who uploaded it
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->timestamps(); // For the "filter by date" feature
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
