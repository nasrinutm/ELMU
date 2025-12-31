<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    |
    | To get an API key, visit: https://aistudio.google.com/
    |
    */

    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | If you need to proxy requests or use a custom endpoint.
    |
    */
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/'),
    'store_id' => env('GEMINI_STORE_ID'),
    'model'    => env('GEMINI_MODEL', 'gemini-2.5-flash'),
];