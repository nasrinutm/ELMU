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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Adding the missing column after quiz_id
            $table->string('quiz_title')->nullable()->after('quiz_id');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('quiz_title');
        });
    }
};
