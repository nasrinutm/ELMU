<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiFileSearch;
use Illuminate\Support\Facades\Log;
use App\Models\ChatbotMaterial;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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
        $materials = ChatbotMaterial::all()->map(function ($item) {
            return [
                'id'           => $item->id,
                'name'         => $item->gemini_document_name,
                'display_name' => $item->display_name,
                'size_bytes'   => $item->size_bytes,
                'mime_type'    => $item->mime_type,
                'state'        => $item->gemini_state ?? 'STATE_ACTIVE',
                'created_at'   => $item->created_at->toDateTimeString(),
            ];
        });

        $rawStoreId = config('gemini.store_id', 'Not Configured');
        $cleanStoreId = str_replace('fileSearchStores/', '', $rawStoreId);

        $currentPrompt = Setting::where('key', 'ai_strict_instruction')
            ->value('value') ?? "Answer in Malay. Provide a concise answer in a numbered list...";

        return Inertia::render('Admin/ChatbotDetails', [
            'files' => $materials->toArray(),
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
        return Inertia::render('Admin/UploadChatbotMaterial');
    }

    /**
     * Store and Index the new material.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,txt,csv,md|max:102400',
            'title' => 'required|string|max:255|unique:chatbot_materials,display_name',
        ], [
            'title.unique' => 'A document with this title already exists in the Knowledge Base.'
        ]);

        $supabasePath = null;

        try {
            $file = $request->file('file');
            $title = $request->input('title');
            $storeId = config('gemini.store_id');

            // 1. Upload to Supabase with randomized filename (default behavior of putFile)
            $supabasePath = Storage::disk('supabase')->putFile('chatbot-materials', $file);
            
            if (!$supabasePath) {
                throw new \Exception("File could not be written to Supabase.");
            }

            // 2. Upload to Gemini via RAG Service
            $geminiResponse = $this->ragService->uploadAndIndexFile(
                $storeId,
                $file->getRealPath(),
                $file->getMimeType(),
                $title
            );

            // 3. Save to Local Database
            ChatbotMaterial::create([
                'display_name'         => $title,
                'gemini_document_name' => $geminiResponse['gemini_document_name'],
                'gemini_file_name'     => $geminiResponse['gemini_file_name'],
                'mime_type'            => $file->getMimeType(),
                'size_bytes'           => $geminiResponse['size_bytes'],
                'gemini_state'         => 'STATE_ACTIVE',
                'internal_file_path'   => $supabasePath // Randomized path stored here
            ]);

            return redirect()->route('chatbot.details')->with('success', 'File indexed and backed up with unique hash!');
        } catch (\Exception $e) {
            if ($supabasePath) {
                Storage::disk('supabase')->delete($supabasePath);
            }
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
            $material = ChatbotMaterial::where('gemini_document_name', $geminiDocumentName)->first();

            // 1. Delete from Gemini API
            $this->ragService->deleteFile($geminiDocumentName);

            // 2. Delete from Supabase if path exists
            if ($material && $material->internal_file_path) {
                Storage::disk('supabase')->delete($material->internal_file_path);
            }

            // 3. Delete the record from local database
            ChatbotMaterial::where('gemini_document_name', $geminiDocumentName)->delete();

            return back()->with('success', 'File deleted from all systems.');
        } catch (\Exception $e) {
            Log::error("RAG Deletion Error: " . $e->getMessage());
            return back()->withErrors(['error' => 'Deletion failed: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $geminiDocumentName)
    {               
        $material = ChatbotMaterial::where('gemini_document_name', $geminiDocumentName)->firstOrFail();

        $request->validate([
            'display_name' => 'required|string|max:255|unique:chatbot_materials,display_name,' . $material->id,
        ]);

        try {
            $material->update([
                'display_name' => $request->display_name
            ]);
            return back()->with('success', 'Document name updated successfully!');
        } catch (\Exception $e) {
            Log::error("RAG Update Error: " . $e->getMessage());
            return back()->with('error', 'Failed to update document name.');
        }
    }

    public function download($id)
    {
        $material = ChatbotMaterial::findOrFail($id);

        if (!$material->internal_file_path) {
            return back()->with('error', 'Original file not found in storage.');
        }

        $url = Storage::disk('supabase')->temporaryUrl(
            $material->internal_file_path, 
            now()->addMinutes(5)
        );

        return redirect($url);
    }
}
