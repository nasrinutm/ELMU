<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiFileSearch; 
//use Gemini\Laravel\Facades\Gemini; // Keep the old one for fallback
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class ChatbotController extends Controller
{
    protected $ragService;

    const DEFAULT_PROMPT = "Answer in Malay. Provide a concise answer in a numbered list. DO NOT PROVIDE EXPLANATIONS FOR EACH ITEM. START THE ANSWER IMMEDIATELY WITH NUMBER 1. DO NOT PROVIDE ANY INTRODUCTION OR PREAMBLE.(default)";
    const PROMPT_KEY = 'ai_strict_instruction';

    public function __construct(GeminiFileSearch $ragService)
    {
        $this->ragService = $ragService;
    }

    protected function getInstruction()
    {
        return Setting::where('key', self::PROMPT_KEY)
            ->value('value') ?? self::DEFAULT_PROMPT;
    }

    public function send(Request $request)
    {
        // 1. Validate FIRST
        $request->validate(['message' => 'required|string']);

        try {
            // 2. Use config() instead of env() for production stability
            $storeId = config('gemini.store_id');

            if (!$storeId) {
                return response()->json(['reply' => "AI Knowledge Base is not set up. Please contact admin."]);
            }

            // 3. FAQ Matching Logic
            $userMessage = $request->input('message');
            $faqs = \App\Models\FaqPrompt::all();

            foreach ($faqs as $faq) {
                similar_text(strtolower($userMessage), strtolower($faq->label), $percent);
                
                // 75% similarity is the recommended threshold
                if ($percent > 75) {
                    $userMessage = $faq->system_prompt; 
                    break;
                }
            }
            
            // 4. Fetch the system instruction (prompt)
            $strictInstruction = $this->getInstruction();

            // 5. Send the OPTIMIZED message (not the raw request message)
            $reply = $this->ragService->chatWithStore($storeId, $userMessage, $strictInstruction);

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
    
    public function editPrompt()
    {
        // Get the current instruction
        $currentInstruction = $this->getInstruction(); // <-- USES NEW HELPER

        return \Inertia\Inertia::render('Admin/ChatbotPromptEdit', [
            'currentInstruction' => $currentInstruction,
        ]);
    }
    
    // NEW: Method to save the prompt
    public function updatePrompt(Request $request)
    {
        $request->validate([
            'instruction' => 'required|string|max:2048',
        ]);
        
        $newInstruction = $request->input('instruction');

        // Save the new instruction to the database
        Setting::updateOrCreate(
            ['key' => self::PROMPT_KEY],
            ['value' => $newInstruction]
        );
        
        return redirect()
            ->route('chatbot.details')
            ->with('success', 'AI strict instruction updated successfully!');
    }
    
    public function setupStore() {
        $id = $this->ragService->createStore("LMS Main Store");
        return "Store Created! Add this to your .env file: GEMINI_STORE_ID=" . $id;
    }
}