<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MaterialController extends Controller
{
    // 1. INDEX: Handles List, Search, and Filters
    public function index(Request $request)
    {
        $query = Material::query()->with('user:id,name');

        // FIX: Search Logic (Matches Vue 'search' prop)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('subject', 'like', '%' . $search . '%');
            });
        }

        // FIX: File Type Filter (Matches Vue 'type' prop)
        if ($request->filled('type')) {
            $query->where('file_type', 'like', '%' . $request->type . '%');
        }

        // Sort Logic
        $sort = $request->input('sort', 'latest');
        if ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'a-z') {
            $query->orderBy('name', 'asc');
        } else {
            $query->latest(); // Default
        }

        return Inertia::render('Materials/Index', [
            'materials' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'type', 'sort']),
            'can' => [
                // Safe check for roles (handles different role packages/setups)
                'manage_materials' => Auth::user()->roles->contains('name', 'teacher')
                                   || Auth::user()->roles->contains('name', 'admin'),
            ]
        ]);
    }

    // 2. CREATE: Shows the Upload Form
    public function create()
    {
        return Inertia::render('Materials/Create');
    }

    // 3. STORE: Handles the Single File Upload
    public function store(Request $request)
    {
        // FIX: Updated validation to match the Single File form in Vue
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Store file
            $path = $file->store('materials', 'public');

            // Get clean extension
            $extension = strtolower($file->getClientOriginalExtension());

            Material::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'subject' => $request->subject,
                'description' => $request->description,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $extension,
            ]);

            return redirect()->route('materials.index')->with('success', 'Material uploaded successfully.');
        }

        return back()->with('error', 'File upload failed.');
    }

    // 4. DOWNLOAD: Forces file download
    public function download(Material $material)
    {
        if (Storage::disk('public')->exists($material->file_path)) {
            // Returns download with original filename
            return Storage::disk('public')->download(
                $material->file_path,
                $material->file_name ?? ($material->name . '.' . $material->file_type)
            );
        }
        return back()->with('error', 'File not found.');
    }

    // 5. EDIT: Show Edit Form
    public function edit(Material $material)
    {
        // Authorization Check
        if (Auth::id() !== $material->user_id) {
             abort(403, 'Unauthorized');
        }

        return Inertia::render('Materials/Edit', [
            'material' => $material
        ]);
    }

    // 6. UPDATE: Handle Changes
    public function update(Request $request, Material $material)
    {
        if (Auth::id() !== $material->user_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip',
        ]);

        $data = $request->only(['name', 'subject', 'description']);

        // Handle File Replacement
        if ($request->hasFile('file')) {
            // Delete old file
            if (Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            // Upload new file
            $file = $request->file('file');
            $data['file_path'] = $file->store('materials', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = strtolower($file->getClientOriginalExtension());
        }

        $material->update($data);

        return redirect()->route('materials.index')->with('success', 'Material updated successfully.');
    }

    // 7. DESTROY: Delete File & Record
    public function destroy(Material $material)
    {
        // Authorization Check (Owner or Admin)
        $isAdmin = Auth::user()->roles->contains('name', 'admin');
        if (Auth::id() !== $material->user_id && !$isAdmin) {
             abort(403);
        }

        // Delete from Storage
        if (Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        // Delete from DB
        $material->delete();

        return redirect()->back()->with('success', 'Material deleted successfully.');
    }
}
