<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description'];

    /**
     * The users that belong to the quiz.
     */
    public function users()
{
    return $this->belongsToMany(User::class)
                ->withPivot('score', 'status', 'completed_at')
                ->withTimestamps();
}
}