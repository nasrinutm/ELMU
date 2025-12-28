<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $guarded = [];

    // A Quiz has many Questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    
    // A Quiz has many Attempts (History)
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}