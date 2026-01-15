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
     * 1. INDEX: Handles List, Search, and Filters
     */
    public function index(Request $request)
    {
        $query = Material::query()->with('user:id,name');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(subject) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('type')) {
            $query->where('file_type', 'like', '%' . $request->type . '%');
        }

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
     * 3. STORE: Handles Upload to Supabase (Add-on)
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

            // Save specifically to your materials folder on Supabase
            $path = $file->store('materials', 'supabase');
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

            return redirect()->route('materials.index')
                ->with('success', 'Material successfully deployed to Cloud Storage.');
        }

        return back()->with('error', 'File upload failed.');
    }

    /**
     * 4. DOWNLOAD: "Friend Method" for Private Buckets
     * This generates a signed URL just like his chatbot download does.
     */
    public function download(Material $material)
    {
        if (!$material->file_path) {
            return back()->with('error', 'File not found.');
        }

        // Friend Method: Simple Temporary URL
        $url = Storage::disk('supabase')->temporaryUrl(
            $material->file_path,
            now()->addMinutes(5)
        );

        return redirect($url);
    }

    /**
     * 5. EDIT: Show Edit Form
     */
    public function edit(Material $material)
    {
        if (Auth::id() !== $material->user_id && !Auth::user()->hasRole('admin')) {
             return redirect()->route('materials.index')
                ->with('error', 'Restricted access: Unauthorized edit attempt.');
        }

        return Inertia::render('Materials/Edit', [
            'material' => $material
        ]);
    }

    /**
     * 6. UPDATE: Handle Changes in Supabase
     */
    public function update(Request $request, Material $material)
    {
        if (Auth::id() !== $material->user_id && !Auth::user()->hasRole('admin')) {
            return redirect()->route('materials.index')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip',
        ]);

        $data = $request->only(['name', 'subject', 'description']);

        if ($request->hasFile('file')) {
            if (Storage::disk('supabase')->exists($material->file_path)) {
                Storage::disk('supabase')->delete($material->file_path);
            }

            $file = $request->file('file');
            $data['file_path'] = $file->store('materials', 'supabase');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = strtolower($file->getClientOriginalExtension());
        }

        $material->update($data);

        return redirect()->route('materials.index')->with('success', 'Changes saved successfully.');
    }

    /**
     * 7. DESTROY: Delete from Supabase & DB
     */
    public function destroy(Material $material)
    {
        if (Auth::id() !== $material->user_id && !Auth::user()->hasRole('admin')) {
             return redirect()->route('materials.index')->with('error', 'Delete Failed: Unauthorized.');
        }

        if (Storage::disk('supabase')->exists($material->file_path)) {
            Storage::disk('supabase')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Material removed from cloud.');
    }
}
