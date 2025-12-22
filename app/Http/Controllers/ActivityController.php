<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Activity::query()
            ->with('user:id,name')
            ->select('id', 'user_id', 'title', 'type', 'description', 'due_date', 'file_path', 'created_at');

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at'); 
        $sortDirection = $request->input('sort', 'desc') === 'oldest' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Permissions
        $permissions = ['manage_activities' => false];
        if (Auth::check()) {
            $user = Auth::user(); 
            // Ensures Admins and Teachers can manage
            $permissions['manage_activities'] = $user->hasRole(['admin', 'teacher']) || $user->can('manage activities');
        }

        $activities = $query->paginate(50);

        return Inertia::render('Activities/Index', [
            'activities' => $activities, 
            'filters' => $request->all(['date', 'sort_by', 'sort']),
            'can' => $permissions,
            'name' => config('app.name'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Activities/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Assignment,Exercise', // Only these two allowed via form
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|max:10240',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'title' => $request->title,
            'type' => $request->type, // 'Assignment' or 'Exercise'
            'description' => $request->description,
            'due_date' => $request->due_date,
            'quiz_data' => null, 
        ];

        // Handle Main File Upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('activities', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        // 1. Create Main Activity
        Activity::create($data);

        // 2. AUTOMATICALLY CREATE SUBMISSION SLOT
        // Since we restricted type to Assignment/Exercise, we always create this slot.
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Submission: ' . $request->title,
            'type' => 'Submission', // This puts it in the Orange section
            'description' => 'Please upload your work for ' . $request->title . ' here.',
            'due_date' => $request->due_date,
            'file_path' => null,
        ]);

        return redirect()->route('activities.index')
            ->with('success', 'Activity and Submission slot created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        return Inertia::render('Activities/Edit', [
            'activity' => $activity
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Assignment,Exercise,Submission', // Allow Submission edits too
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|max:10240',
        ]);

        $data = [
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'due_date' => $request->due_date,
        ];

        // Handle File Replacement
        if ($request->hasFile('file')) {
            // Delete old file
            if ($activity->file_path && Storage::disk('public')->exists($activity->file_path)) {
                Storage::disk('public')->delete($activity->file_path);
            }
            
            $file = $request->file('file');
            $data['file_path'] = $file->store('activities', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        $activity->update($data);

        return redirect()->route('activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        // Delete attached file if exists
        if ($activity->file_path && Storage::disk('public')->exists($activity->file_path)) {
            Storage::disk('public')->delete($activity->file_path);
        }
        
        $activity->delete();
        
        return redirect()->route('activities.index')
            ->with('success', 'Activity deleted successfully.');
    }

    /**
     * Download the attached file.
     */
    public function download(Activity $activity)
    {
        if (!$activity->file_path || !Storage::disk('public')->exists($activity->file_path)) {
            return back()->with('error', 'File not found.');
        }
        $path = storage_path('app/public/' . $activity->file_path);
        return response()->download($path, $activity->file_name);
    }
}