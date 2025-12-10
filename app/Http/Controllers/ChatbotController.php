<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiFileSearch; 
//use Gemini\Laravel\Facades\Gemini; // Keep the old one for fallback
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected $ragService;


    public function __construct(GeminiFileSearch $ragService)
    {
        $this->ragService = $ragService;
    }

    public function send(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        try {
         
            $storeId = env('GEMINI_STORE_ID');

            if ($storeId) {
          
                $reply = $this->ragService->chatWithStore($storeId, $request->message);
            // } else {
            //  Standard Mode (Fallback)
            //  
            //   $result = Gemini::generativeModel('gemini-2.5-flash')->generateContent($request->message);
            //   $reply = $result->text();
            } else {
                $reply = "AI Knowledge Base is not set up. Please contact admin.";
            }

            return response()->json([
                'reply' => $reply
            ]);

        } catch (\Exception $e) {
            Log::error("Gemini Error: " . $e->getMessage());

            return response()->json([
                'reply' => "System Error: " . $e->getMessage()
            ], 200);
        }
    }
    
    
    public function setupStore() {
        $id = $this->ragService->createStore("LMS Main Store");
        return "Store Created! Add this to your .env file: GEMINI_STORE_ID=" . $id;
    }
}