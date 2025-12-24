<?php

namespace App\Policies; // This MUST be App\Policies

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function update(?User $user, Post $post): bool
    {
        if (!$user) return false;

        // Spatie Role Check
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return true;
        }

        return $user->id === $post->user_id &&
               $post->created_at->gt(now()->subMinutes(30));
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}