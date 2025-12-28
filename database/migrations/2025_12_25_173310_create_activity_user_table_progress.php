public function up(): void
{
    Schema::create('activity_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
        // Add extra fields if your relationship needs them (like 'score', 'status', etc.)
        $table->string('status')->default('pending'); 
        $table->integer('score')->nullable();
        $table->timestamp('submitted_at')->nullable();
        $table->timestamps();
    });
}