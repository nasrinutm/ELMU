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
        Schema::table('chatbot_materials', function (Blueprint $table) {
            
            // 1. ADD: The new column to store BLOB/Binary data
            // longText is the appropriate column type for large binary data in Eloquent/PostgreSQL.
            $table->longText('file_content')
                  ->nullable()
                  ->after('id')
                  ->comment('The raw binary content (BLOB) of the uploaded file.');

            // 2. REMOVE: The file path column, as requested
            // Use dropColumn('internal_file_path') only if you know it was created.
            // If the column never existed, this line will cause an error, so handle it defensively:
            if (Schema::hasColumn('chatbot_materials', 'internal_file_path')) {
                $table->dropColumn('internal_file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On rollback, remove the file_content BLOB column
        Schema::table('chatbot_materials', function (Blueprint $table) {
            $table->dropColumn('file_content');

            // Optionally restore the file path column if needed for rollback integrity
            // $table->string('internal_file_path', 255)->nullable();
        });
    }
};