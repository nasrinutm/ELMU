<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiFileSearch;
use Illuminate\Support\Facades\Log;
use App\Models\ChatbotMaterial;
use App\Models\Setting;
use Inertia\Inertia;

class ChatbotUploadController extends Controller
{
    protected $ragService;

    public function __construct(GeminiFileSearch $ragService)
    {
        $this->ragService = $ragService;
    }

    /**
     * Display the Knowledge Base list and system info.
     */
    public function index()
    {
        $files = $this->ragService->listFiles();

        // Get the raw store ID from config
        $rawStoreId = config('gemini.store_id', 'Not Configured');

        // Remove the "fileSearchStores/" prefix if it exists for cleaner UI
        $cleanStoreId = str_replace('fileSearchStores/', '', $rawStoreId);

        $currentPrompt = Setting::where('key', 'ai_strict_instruction')
            ->value('value') ?? "Answer in Malay. Provide a concise answer in a numbered list...";

        return Inertia::render('Admin/ChatbotDetails', [
            'files' => $files->toArray(),
            'systemInfo' => [
                'model' => config('gemini.model', 'gemini-1.5-flash'),
                'store_id' => $cleanStoreId,
                'api_status' => 'Active',
                'current_prompt' => $currentPrompt,
            ]
        ]);
    }

    /**
     * Show the upload form.
     */
    public function create()
    {
        // Ensure this string exactly matches: resources/js/Pages/Admin/UploadChatbotMaterial.vue
        return Inertia::render('Admin/UploadChatbotMaterial');
    }

    /**
     * Store and Index the new material.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,txt,csv,md|max:102400',
            'title' => 'required|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            $title = $request->input('title');
            $storeId = config('gemini.store_id');

            // 1. Upload to Gemini via RAG Service
            $geminiResponse = $this->ragService->uploadAndIndexFile(
                $storeId,
                $file->getRealPath(),
                $file->getMimeType(),
                $title
            );

            // 2. Save to Local Database
            ChatbotMaterial::create([
                'file_content'         => file_get_contents($file->getRealPath()),
                'display_name'         => $title,
                'gemini_document_name' => $geminiResponse['gemini_document_name'],
                'gemini_file_name'     => $geminiResponse['gemini_file_name'],
                'mime_type'            => $file->getMimeType(),
                'size_bytes'           => $geminiResponse['size_bytes'],
                'gemini_state'         => 'STATE_ACTIVE',
            ]);

            // Redirect to index so the user can see the new file in the list immediately
            return redirect()->route('chatbot.details')->with('success', 'File uploaded and indexed successfully!');
        } catch (\Exception $e) {
            Log::error("RAG Upload Error: " . $e->getMessage());
            return back()->withErrors(['file' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove material from AI memory and local DB.
     */
    public function destroy($geminiDocumentName)
    {
        try {
            // 1. Delete from Gemini API
            $this->ragService->deleteFile($geminiDocumentName);

            // 2. Delete the record from local database
            ChatbotMaterial::where('gemini_document_name', $geminiDocumentName)->delete();

            return back()->with('success', 'File deleted from AI memory.');
        } catch (\Exception $e) {
            Log::error("RAG Deletion Error: " . $e->getMessage());
            return back()->withErrors(['error' => 'Deletion failed: ' . $e->getMessage()]);
        }
    }
}
