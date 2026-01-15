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
            // Enforce uniqueness on the title (as discussed)
            $table->string('display_name')->unique()->change();
            
            // Add the column to store the Supabase path
            $table->string('internal_file_path')->after('display_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_materials', function (Blueprint $table) {
            //
        });
    }
};
