<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Reply extends Model
{
    use HasFactory;
    
    protected $fillable = ['body', 'user_id', 'post_id', 'parent_id'];

    protected $appends = ['can_update', 'can_delete'];

    /**
     * Get the user who wrote the reply.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post this reply belongs to.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the parent reply (if this is a nested reply).
     */
    public function parent()
    {
        return $this->belongsTo(Reply::class, 'parent_id');
    }

    /**
     * Get all children replies (replies to this reply).
     */
    public function children()
    {
        return $this->hasMany(Reply::class, 'parent_id');
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