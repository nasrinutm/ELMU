<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use create if it doesn't exist, or wrap in a check
        Schema::create('chatbot_materials', function (Blueprint $table) {
            $table->id();

            // 1. BLOB STORAGE
            // We include file_content here directly in the creation
            $table->longText('file_content')
                  ->nullable()
                  ->comment('The raw binary content (BLOB) of the uploaded file.');

            // 2. GEMINI RESOURCE NAMES
            $table->string('gemini_document_name', 255)->unique()->comment('The full resource name for the Document.');
            $table->string('gemini_file_name', 255)->nullable()->comment('The full resource name for the File resource.');
            
            // 3. FILE METADATA
            $table->string('display_name', 255)->unique()->comment('The human-readable file name.');            
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);

            // 4. STATUS AND TIMESTAMPS
            $table->string('gemini_state', 50)->default('PROCESSING');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_materials');
    }
};