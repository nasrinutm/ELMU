<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index(Request $request)
    {

        $query = Material::query()->with('user:id,name');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $query->orderBy('created_at', $request->input('sort') === 'oldest' ? 'asc' : 'desc');

        return Inertia::render('Materials/Index', [
            'materials' => $query->paginate(10)->withQueryString(),
            'filters' => [
                'date' => $request->date,
                'sort' => $request->input('sort', 'latest'),
            ],
            'can' => [
                'manage_materials' => Auth::user()->hasRole('teacher')
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Materials/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:10240|mimes:pdf,doc,docx,ppt,pptx',
        ]);

        $files = $request->file('files');
        $count = 0;

        foreach ($files as $file) {
            $path = $file->store('materials', 'public');


            $displayName = $request->name;
            if (count($files) > 1 || empty($displayName)) {
                $displayName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            }

            Material::create([
                'user_id' => Auth::id(),
                'name' => $displayName,
                'subject' => $request->subject,
                'description' => $request->description,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
            ]);

            $count++;
        }

        return redirect()->route('materials.index')->with('success', "$count material(s) uploaded successfully.");
    }

    public function edit(Material $material)
    {
        return Inertia::render('Materials/Edit', [
            'material' => $material
        ]);
    }

    public function update(Request $request, Material $material)
    {
         $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $data = $request->only('name', 'subject', 'description');

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($material->file_path);
            $file = $request->file('file');
            $data['file_path'] = $file->store('materials', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        $material->update($data);

        return redirect()->route('materials.index')->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        Storage::disk('public')->delete($material->file_path);
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Material deleted successfully.');
    }

    public function download(Material $material)
    {
        if (!Storage::disk('public')->exists($material->file_path)) {
            return back()->with('error', 'File not found.');
        }
        return Storage::disk('public')->download($material->file_path, $material->file_name);
    }
}
