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
    Schema::table('activities', function (Blueprint $table) {
        // Adding indexes to columns we frequently sort or filter by
        $table->index('type');       // Speeds up filtering by Quiz/Game/Assignment
        $table->index('user_id');    // Speeds up finding who created it
        $table->index('created_at'); // Speeds up "Latest" sorting
    });
}

public function down(): void
{
    Schema::table('activities', function (Blueprint $table) {
        $table->dropIndex(['type', 'user_id', 'created_at']);
    });
}
};
