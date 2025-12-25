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
        // Use Schema::table to modify the existing table
        Schema::table('posts', function (Blueprint $table) {
            // Add the missing 'title' column
            $table->string('title', 255)->after('id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the column if rolling back
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};