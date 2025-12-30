<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $guarded = [];

    
    protected $casts = [
        'content' => 'array',
    ];

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}