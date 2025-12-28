<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query()
            ->with('user:id,name')
            ->with('mySubmission') // Load submission status
            ->select('id', 'user_id', 'title', 'type', 'description', 'due_date', 'file_path', 'created_at');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $sortField = $request->input('sort_by', 'created_at'); 
        $sortDirection = $request->input('sort', 'desc') === 'oldest' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDirection);

        $permissions = ['manage_activities' => false];
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user(); 
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

    public function create()
    {
        return Inertia::render('Activities/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Assignment,Exercise',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|max:10240',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'quiz_data' => null, 
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('activities', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        Activity::create($data);

        // Auto-create submission slot
        Activity::create([
            'user_id' => Auth::id(),
            'title' => 'Submission: ' . $request->title,
            'type' => 'Submission',
            'description' => 'Please upload your work for ' . $request->title . ' here.',
            'due_date' => $request->due_date,
            'file_path' => null,
        ]);

        return redirect()->route('activities.index')
            ->with('success', 'Activity and Submission slot created successfully.');
    }

    public function show(Activity $activity)
    {
        $activity->load('mySubmission');
        
        return Inertia::render('Activities/Show', [
            'activity' => $activity
        ]);
    }

    public function submit(Request $request, Activity $activity)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('submissions', 'public');

        ActivitySubmission::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'activity_id' => $activity->id
            ],
            [
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'submitted_at' => now(),
            ]
        );
        
        return back()->with('success', 'Work submitted successfully!');
    }

    /**
     * Download the STUDENT'S submission
     */
    public function downloadSubmission(Activity $activity)
    {
        $submission = $activity->mySubmission;

        if (!$submission || $submission->user_id !== Auth::id()) {
            abort(403);
        }

        $path = storage_path('app/public/' . $submission->file_path);
        
        if (!file_exists($path)) {
            return back()->with('error', 'File not found.');
        }

        return response()->download($path, $submission->file_name);
    }

    public function edit(Activity $activity)
    {
        return Inertia::render('Activities/Edit', [
            'activity' => $activity
        ]);
    }

    public function update(Request $request, Activity $activity)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Assignment,Exercise,Submission',
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

        if ($request->hasFile('file')) {
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

    public function destroy(Activity $activity)
    {
        if ($activity->file_path && Storage::disk('public')->exists($activity->file_path)) {
            Storage::disk('public')->delete($activity->file_path);
        }
        
        $activity->delete();
        
        return redirect()->route('activities.index')
            ->with('success', 'Activity deleted successfully.');
    }

    /**
     * Download the TEACHER'S attached material
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