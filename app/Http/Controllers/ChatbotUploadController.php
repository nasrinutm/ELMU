<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiFileSearch;
use Illuminate\Support\Facades\Log;

class ChatbotUploadController extends Controller
{
    protected $ragService;

    public function __construct(GeminiFileSearch $ragService)
    {
        $this->ragService = $ragService;
    }

    public function store(Request $request)
    {
        // 1. Validate: Ensure it's a PDF/Text file and under 100MB
        $request->validate([
            'file' => 'required|file|mimes:pdf,txt,csv,md|max:102400', // 100MB max
            'title' => 'required|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            
            // 2. Get your Store ID from .env
            $storeId = env('GEMINI_STORE_ID');

            if (!$storeId) {
                return back()->withErrors(['file' => 'AI Knowledge Base ID is missing in .env settings.']);
            }

            // 3. Send to Google via our Service
            $this->ragService->uploadAndIndexFile(
                $storeId,
                $file->getRealPath(), // Path to temp file
                $file->getMimeType()  // e.g. application/pdf
            );

            return back()->with('success', 'File uploaded and indexed! The AI is learning from it now.');

        } catch (\Exception $e) {
            Log::error("RAG Upload Error: " . $e->getMessage());
            return back()->withErrors(['file' => 'Upload failed: ' . $e->getMessage()]);
        }
    }
    
    // Just to render the view
    public function create() {
        return \Inertia\Inertia::render('Admin/UploadChatbotMaterial');
    }

    public function index()
    {
        // 1. Fetch files from Google
        $files = $this->ragService->listFiles();

        // 2. Get System Info
        $systemInfo = [
            'model' => 'gemini-2.5-flash',
            'store_id' => env('GEMINI_STORE_ID', 'Not Configured'),
            'api_status' => 'Active', // You could add real health check logic here
        ];

        return \Inertia\Inertia::render('Admin/ChatbotDetails', [
            'files' => $files,
            'systemInfo' => $systemInfo
        ]);
    }

    public function destroy($fileName)
    {
        // Remove "files/" prefix logic if needed, but usually API handles the full string
        $this->ragService->deleteFile($fileName);
        
        return back()->with('success', 'File deleted from AI memory.');
    }
}