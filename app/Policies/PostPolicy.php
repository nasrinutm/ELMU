<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Rule: Admin can update any post, anytime.
        if ($user->hasRole('admin')) {
            return true;
        }

        // Rule: Author can update their own post within 30 minutes.
        return $user->id === $post->user_id &&
               $post->created_at->gt(now()->subMinutes(30));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // For this, we'll use the same logic as updating.
        return $this->update($user, $post);
    }
}