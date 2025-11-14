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
        Schema::table('replies', function (Blueprint $table) {
            // This is the code to ADD the new column
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('replies') // Points to its own table
                  ->onDelete('cascade')
                  ->after('post_id'); // Puts it in a nice spot
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            // This is how to drop it if we ever roll back
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};