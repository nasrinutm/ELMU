<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'quiz_title',
        'score',
        'total_questions',
    ];
    
    // Helper to get formatted date
    protected $casts = [
        'created_at' => 'datetime',
    ];
}