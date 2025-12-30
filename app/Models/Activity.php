<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne; // <--- Import this
use Illuminate\Support\Facades\Auth; // <--- Import this

class Activity extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'user_id', 'title', 'type', 'description', 'due_date', 
        'file_path', 'file_name', 'file_type', 'time_limit', 'quiz_data','file_path',   
        'file_name',   'file_type',   'time_limit',  'quiz_data',   
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- NEW METHODS BELOW ---

    public function submissions()
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    // This checks if the CURRENTLY logged-in user has a submission
    public function mySubmission(): HasOne
    {
        return $this->hasOne(ActivitySubmission::class)
            ->where('user_id', Auth::id());
    }
}