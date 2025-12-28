<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotMaterial extends Model
{
    use HasFactory;

    // The model is explicitly linked to the table name created in the migration
    protected $table = 'chatbot_materials';

    // The fields that can be mass assigned when using the create() method
    protected $fillable = [
        'internal_file_path',
        'display_name',
        'gemini_display_name',
        'gemini_document_name',
        'gemini_file_name',
        'mime_type',
        'size_bytes',
        'gemini_state',
    ];

    // Optional: Define fields that should be cast to a specific type
    protected $casts = [
        'size_bytes' => 'integer',
    ];
}