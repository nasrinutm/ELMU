<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    // --- FIX: Use guarded = [] to allow ALL columns (including 'answers') to be saved ---
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'answers' => 'array', // Automatically handles JSON
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}