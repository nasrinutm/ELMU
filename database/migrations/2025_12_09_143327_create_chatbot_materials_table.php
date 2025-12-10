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
        Schema::create('chatbot_materials', function (Blueprint $table) {
            $table->id();

            // GEMINI RESOURCE NAMES
            // Stores the full document path (e.g., fileSearchStores/{id}/documents/{id})
            $table->longText('file_content')->nullable()->comment('The raw binary content (BLOB) of the uploaded file.');
            $table->string('gemini_document_name', 255)->unique()->comment('The full resource name for the Document in the File Search Store.');
            // Stores the full file path (e.g., files/{id}). Needed for deletion/reference.
            $table->string('gemini_file_name', 255)->nullable()->comment('The full resource name for the underlying File resource.');
            
            // FILE METADATA
            $table->string('display_name', 255)->comment('The human-readable file name (e.g., AnnualReport.pdf).');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);

            // STATUS AND TIMESTAMPS
            // State reported by Gemini (e.g., ACTIVE, PROCESSING, FAILED)
            $table->string('gemini_state', 50)->default('PROCESSING')->comment('The indexing state reported by the Gemini API.');
            // Internal flag for quick filtering
            $table->boolean('is_active')->default(true)->comment('Internal flag: Is this material currently in use by the chatbot?');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_materials');
    }
};
