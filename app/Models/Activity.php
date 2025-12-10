<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $fillable = [
        'user_id',
        'title',
        'type',         // e.g., 'Homework', 'Lab', 'Quiz'
        'description',
        'due_date',
        'file_path',    // Nullable
        'file_name',    // Nullable
        'file_type',    // Nullable
        'time_limit',   // Nullable, for quizzes
        'quiz_data',    // Nullable, JSON for quiz questions
    ];

    // This ensures Laravel treats due_date as a Carbon object, not just a string
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get the user (teacher) who created the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}