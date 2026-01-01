<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Activity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Includes fields for general activities, manual entries, and quiz data.
     */
    protected $fillable = [
        'user_id',
        'title',
        'type',        // e.g., 'Homework', 'Lab', 'Quiz', 'manual'
        'description', // Used for manual entry details or assignment instructions
        'due_date',
        'file_path',   // Path to teacher-uploaded files
        'file_name',   
        'file_type',   
        'time_limit',  // For quiz activities
        'quiz_data',   // JSON blob for quiz questions
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'due_date' => 'date',
        'quiz_data' => 'array', // Automatically handles JSON serialization
    ];

    /**
     * Get the user (teacher/admin) who created the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the students assigned to this activity.
     * * This defines the Many-to-Many relationship via the 'activity_user' table.
     * The pivot columns match your consolidated migration.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'activity_user')
            ->withPivot('status', 'score', 'submitted_at', 'is_manual')
            ->withTimestamps();
    }
}