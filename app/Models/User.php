<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Submission;
use App\Models\StudentManualActivity;
// Ensure these models exist if you are referencing them
use App\Models\ActivitySubmission;
use App\Models\QuizAttempt;

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

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function manualActivities()
    {
        return $this->hasMany(StudentManualActivity::class, 'user_id');
    }

    // --- REPORT RELATIONSHIPS ---

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class)
                    ->withPivot('score', 'status', 'completed_at')
                    ->withTimestamps();
    }

    public function activitySubmissions()
    {
        // Matches your activity_submissions table
        return $this->hasMany(ActivitySubmission::class);
    }

    public function quizAttempts()
    {
        // Matches your quiz_attempts logic
        return $this->hasMany(QuizAttempt::class);
    }
} // <--- Class now ends here, after all functions.