<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function send(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        try {
            $result = Gemini::generativeModel('gemini-2.5-flash')->generateContent($request->message);

            return response()->json([
                'reply' => $result->text()
            ]);

        } catch (\Exception $e) {
            // 1. Log the error to storage/logs/laravel.log so you can see it later
            Log::error("Gemini Error: " . $e->getMessage());

            // 2. Return the REAL error message to the frontend
            // This helps us debug immediately without digging into logs
            return response()->json([
                'reply' => "System Error: " . $e->getMessage()
            ], 200); // Return 200 so the frontend displays this message
        }
    }
}