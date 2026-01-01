<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAccess extends Model
{
    protected $table = 'quiz_access';
    

    // This line is crucial. Without it, the database refuses to save.
    protected $fillable = ['user_id', 'quiz_id', 'extra_attempts'];
}