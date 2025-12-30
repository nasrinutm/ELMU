<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('submissions', function (Blueprint $table) {
        $table->id();
        // Link to the User who submitted
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // Link to the Activity being submitted for
        $table->foreignId('activity_id')->constrained()->onDelete('cascade');
        
        // The file path (stored in storage/app/public)
        $table->string('file_path');
        
        // Optional score/points
        $table->integer('score')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
