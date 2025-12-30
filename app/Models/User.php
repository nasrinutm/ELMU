<?php

namespace App\Models;

// ... other imports
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // ... existing fillable, hidden, casts ...

    /**
     * Relationship: A User has many Submissions (for Activities).
     */
    public function submissions()
    {
        // Ensure you have created the Submission model previously.
        // If not, run: php artisan make:model Submission
        return $this->hasMany(Submission::class);
    }

    /**
     * Relationship: A User has many Manual Activities (Teacher added).
     */
    public function manualActivities()
    {
        // This links to the manual table we created earlier
        return $this->hasMany(StudentManualActivity::class, 'user_id');
    }
}