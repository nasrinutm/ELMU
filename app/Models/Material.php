<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * We list all the columns from our migration (Step 1)
     * so that Laravel allows us to fill them in our code.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'subject',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'user_id',
    ];

    /**
     * This defines the relationship.
     * It tells Laravel that each Material "Belongs To" one User.
     * This lets us easily find the teacher who uploaded a material.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
