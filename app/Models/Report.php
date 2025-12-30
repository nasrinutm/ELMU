<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    // These fields must match your migration
    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject',
        'score',
        'comments',
    ];

    /**
     * Get the student associated with the report.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the teacher who wrote the report.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}