<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Relationship with Forum Posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * NEW: Relationship with Activities.
     * This links the student to the classroom activities they have interacted with.
     */
    public function activities()
{
    return $this->belongsToMany(Activity::class)
                ->withPivot('status', 'score', 'completed_at')
                ->withTimestamps();
}

public function quizzes()
{
    return $this->belongsToMany(Quiz::class)
                ->withPivot('score', 'status', 'completed_at')
                ->withTimestamps();
}

// In User.php

public function activitySubmissions()
{
    // Points to your activity_submissions table
    return $this->hasMany(ActivitySubmission::class);
}

public function quizAttempts()
{
    // Points to your quiz_attempts table
    return $this->hasMany(QuizAttempt::class);
}
}