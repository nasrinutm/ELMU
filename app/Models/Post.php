<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'user_id'];

    /**
     * Get the user who owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all replies (for the count).
     */
    public function allReplies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * Get only the top-level (non-nested) replies.
     */
    public function replies()
    {
        return $this->hasMany(Reply::class)->whereNull('parent_id');
    }
}