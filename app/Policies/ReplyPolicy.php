<?php

namespace App\Policies;

use App\Models\Reply;
use App\Models\User;

class ReplyPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reply $reply): bool
    {
        // Rule: Admin can update any reply, anytime.
        if ($user->hasRole('admin')) {
            return true;
        }

        // Rule: Author can update their own reply within 30 minutes.
        return $user->id === $reply->user_id &&
               $reply->created_at->gt(now()->subMinutes(30));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reply $reply): bool
    {
        // Use the same logic as updating.
        return $this->update($user, $reply);
    }
}