<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// IMPORTANT: This line tells PHP where to find the BelongsTo class
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySubmission extends Model
{
    // Ensure the table name is correct if it's not the default plural
    protected $table = 'activity_submissions';

    protected $fillable = [
        'user_id', 
        'activity_id', 
        'file_path', 
        'file_name', 
        'submitted_at', 
        'score'
    ];

    /**
     * Relationship: A submission belongs to a student (User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A submission belongs to an activity.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}