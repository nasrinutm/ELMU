<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MaterialController extends Controller
{
    /**
     * 1. INDEX: Handles List, Search (Case-Insensitive), and Filters
     */
    public function index(Request $request)
    {
        $query = Material::query()->with('user:id,name');

        // FIX: Case-Insensitive Search using LOWER()
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(subject) LIKE ?', ["%{$search}%"]);
            });
        }

        // File Type Filter
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
            $query->latest();
        }

        return Inertia::render('Materials/Index', [
            'materials' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'type', 'sort']),
            'can' => [
                'manage_materials' => Auth::user()->hasRole('teacher') || Auth::user()->hasRole('admin'),
            ]
        ]);
    }

    /**
     * 2. CREATE: Shows the Upload Form
     */
    public function create()
    {
        return Inertia::render('Materials/Create');
    }

    /**
     * 3. STORE: Handles the Single File Upload
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('materials', 'public');
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

            // Timestamp ensures Vue watcher detects a fresh message every time
            return redirect()->route('materials.index')
                ->with('success', 'New material has been successfully deployed. [' . now()->timestamp . ']');
        }

        return back()->with('error', 'File upload failed. Please try again. [' . now()->timestamp . ']');
    }

    /**
     * 4. DOWNLOAD: Forces file download
     */
    public function download(Material $material)
    {
        if (Storage::disk('public')->exists($material->file_path)) {
            return Storage::disk('public')->download(
                $material->file_path,
                $material->file_name ?? ($material->name . '.' . $material->file_type)
            );
        }
        return back()->with('error', 'The requested file could not be found. [' . now()->timestamp . ']');
    }

    /**
     * 5. EDIT: Show Edit Form (With Ownership Check)
     */
    public function edit(Material $material)
    {
        // Ownership Check: Block access if not owner and not admin
        if (Auth::id() !== $material->user_id && !Auth::user()->hasRole('admin')) {
             return redirect()->route('materials.index')
                ->with('error', 'Access Denied: This material belongs to another instructor. [' . now()->timestamp . ']');
        }

        return Inertia::render('Materials/Edit', [
            'material' => $material
        ]);
    }

    /**
     * 6. UPDATE: Handle Changes (With Ownership Check)
     */
    public function update(Request $request, Material $material)
    {
        // Security Check: Ensure the user is authorized to perform this update
        if (Auth::id() !== $material->user_id && !Auth::user()->hasRole('admin')) {
            return redirect()->route('materials.index')
                ->with('error', 'Unauthorized: You cannot modify another teacher\'s material. [' . now()->timestamp . ']');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip',
        ]);

        $data = $request->only(['name', 'subject', 'description']);

        if ($request->hasFile('file')) {
            // Delete the old file from storage
            if (Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $file = $request->file('file');
            $data['file_path'] = $file->store('materials', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = strtolower($file->getClientOriginalExtension());
        }

        $material->update($data);

        return redirect()->route('materials.index')
            ->with('success', 'Changes saved successfully. [' . now()->timestamp . ']');
    }

    /**
     * 7. DESTROY: Delete File & Record (With Ownership Check)
     */
    public function destroy(Material $material)
    {
        // Security Check: Block unauthorized deletion
        if (Auth::id() !== $material->user_id && !Auth::user()->hasRole('admin')) {
             return redirect()->route('materials.index')
                ->with('error', 'Delete Failed: You do not have permission to remove this resource. [' . now()->timestamp . ']');
        }

        if (Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Material permanently removed from system. [' . now()->timestamp . ']');
    }
}
