<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentManualActivity extends Model
{
    // 1. Link to the specific table we created
    protected $table = 'student_manual_activities';

    // 2. Allow these fields to be filled
    protected $fillable = [
        'user_id',
        'title',
        'score',
    ];

    /**
     * Relationship: This activity belongs to one User (Student).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}