<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivitySubmission extends Model
{
    use HasFactory;

    // Matches your database table name
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
     * The attributes that should be cast.
     * This ensures the date is treated as a Carbon object automatically.
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'score' => 'float',
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

    /**
     * Helper: Check if this submission was turned in after the due date.
     * Useful for your "Manual to Auto" presentation logic.
     */
    public function isLate(): bool
    {
        if (!$this->activity->due_date) {
            return false;
        }
        return $this->submitted_at->gt($this->activity->due_date);
    }
}
