<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $fillable = [
        'user_id',
        'title',
        'type',
        'description',
        'due_date',
        'file_path',
        'file_name',
        'file_type',
        'time_limit',
        'quiz_data',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
