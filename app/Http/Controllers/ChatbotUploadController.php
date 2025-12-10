<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiFileSearch;
use Illuminate\Support\Facades\Log;
use App\Models\ChatbotMaterial;

class ChatbotUploadController extends Controller
{
    protected $ragService;

    public function __construct(GeminiFileSearch $ragService)
    {
        $this->ragService = $ragService;
    }

    public function store(Request $request)
    {
        //Ensure it's a PDF/Text file and under 100MB
        $request->validate([
            'file' => 'required|file|mimes:pdf,txt,csv,md|max:102400', // 100MB max
            'title' => 'required|string|max:255', // This is your 'input_name'
        ]);

        try {
            $file = $request->file('file');
            $title = $request->input('title');
            $storeId = env('GEMINI_STORE_ID');

            if (!$storeId) {
                return back()->withErrors(['file' => 'AI Knowledge Base ID is missing in .env settings.']);
            }

            // --- STEP A: Read File Content for BLOB Storage ---
            $fileContentBlob = file_get_contents($file->getRealPath());
            
            // 1. Call the Gemini service (unchanged, still uploads the file physically)
            $geminiResponse = $this->ragService->uploadAndIndexFile(
                $storeId,
                $file->getRealPath(), 
                $file->getMimeType(),
                $title 
            );

            // 2. Save details to the database, including the BLOB content
            \App\Models\ChatbotMaterial::create([
                // ❌ REMOVED: 'internal_file_path' => $file->hashName(),
                
                // ✅ ADDED: Storing the BLOB content
                'file_content' => $fileContentBlob, 
                
                'display_name' => $title,
                //'gemini_display_name' => $title, 
                'gemini_document_name' => $geminiResponse['gemini_document_name'],
                'gemini_file_name' => $geminiResponse['gemini_file_name'],
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $geminiResponse['size_bytes'],
                'gemini_state' => 'PROCESSING',
            ]);

            return back()->with('success', 'File uploaded and indexed! The AI is learning from it now.');

        } catch (\Exception $e) {
            Log::error("RAG Upload Error: " . $e->getMessage());
            return back()->withErrors(['file' => 'Upload failed: ' . $e->getMessage()]);
        }
    }
    
    //
    public function create() {
        return \Inertia\Inertia::render('Admin/UploadChatbotMaterial');
    }

    public function index()
    {
        // Fetch files from Google
        $files = $this->ragService->listFiles();

        // Get the store ID from the environment (or directly from the service if you add a getter)
        $storeId = env('GEMINI_STORE_ID', 'Not Configured'); 

        // 2Get System Info
        $systemInfo = [
            'model' => 'gemini-2.5-flash',
            'store_id' => $storeId,
            'api_status' => 'Active', 
        ];

        return \Inertia\Inertia::render('Admin/ChatbotDetails', [
            'files' => $files,
            'systemInfo' => $systemInfo
    ]);
    }

    public function destroy($fileName)
    {
        
        $this->ragService->deleteFile($fileName);
        
        return back()->with('success', 'File deleted from AI memory.');
    }
}