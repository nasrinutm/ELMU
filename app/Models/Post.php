<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = ['title', 'content', 'user_id'];

    protected $appends = ['can_update', 'can_delete'];

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

    protected function canUpdate(): Attribute
    {
        return Attribute::make(
            get: fn () => Gate::allows('update', $this),
        );
    }

    protected function canDelete(): Attribute
    {
        return Attribute::make(
            get: fn () => Gate::allows('delete', $this),
        );
    }
}