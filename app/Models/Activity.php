<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'title',
        'type',        // e.g., 'Homework', 'Lab', 'Quiz', 'manual'
        'description', // <--- We use this for manual entry details
        'due_date',
        'file_path',   // Nullable
        'file_name',   // Nullable
        'file_type',   // Nullable
        'time_limit',  // Nullable, for quizzes
        'quiz_data',   // Nullable, JSON for quiz questions
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'due_date' => 'date',
        'quiz_data' => 'array', // Automatically cast JSON data to an array
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
