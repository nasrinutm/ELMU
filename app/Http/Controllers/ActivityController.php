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
            ->with('mySubmission')
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
            'type' => 'required|string|in:Assignment,Exercise,Submission',
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

        return redirect()->route('activities.index')
            ->with('success', 'Activity and Submission slot created successfully.');
    }

    public function show(Activity $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isTeacher = $user->hasRole(['admin', 'teacher']);

        // Load specific submission for the logged-in user
        $activity->load('mySubmission');

        // If teacher, load ALL submissions with student names
        $allSubmissions = [];
        if ($isTeacher) {
            $allSubmissions = ActivitySubmission::where('activity_id', $activity->id)
                ->with('user:id,name,email')
                ->orderBy('submitted_at', 'desc')
                ->get();
        }

        return Inertia::render('Activities/Show', [
            'activity' => $activity,
            'allSubmissions' => $allSubmissions,
            'isTeacher' => $isTeacher
        ]);
    }

    /**
     * FIX: Handles user submissions correctly to update status from Pending to Completed.
     */
    public function submit(Request $request, Activity $activity)
    {
        // Ensure 'file' is required and is a valid file type
        $request->validate([
            'file' => [
                'required', 
                'file', 
                'mimes:pdf,docx,pptx,zip', // Specify allowed types for better security
                'max:10240'
            ],
        ], [
            'file.required' => 'You must upload a file to complete this submission.'
        ]);

        $file = $request->file('file');
        $path = $file->store('submissions', 'public');

        ActivitySubmission::updateOrCreate(
            ['user_id' => Auth::id(), 'activity_id' => $activity->id],
            [
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Work submitted successfully!');
    }

    public function downloadSubmission(ActivitySubmission $submission)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($submission->user_id !== $user->id && !$user->hasRole(['admin', 'teacher'])) {
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

    public function download(Activity $activity)
    {
        if (!$activity->file_path || !Storage::disk('public')->exists($activity->file_path)) {
            return back()->with('error', 'File not found.');
        }
        $path = storage_path('app/public/' . $activity->file_path);
        return response()->download($path, $activity->file_name);
    }

    public function unsubmit(Activity $activity)
    {
        $submission = ActivitySubmission::where('activity_id', $activity->id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $submission) {
            return back()->with('error', 'No submission found to remove.');
        }

        // Standard 2-minute window for unsubmitting
        if ($submission->created_at->diffInMinutes(now()) >= 2) {
            return back()->with('error', 'Time limit exceeded. You can only remove a submission within 2 minutes.');
        }

        if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
            Storage::disk('public')->delete($submission->file_path);
        }

        $submission->delete();

        return back()->with('success', 'Submission removed. You can now upload a new file.');
    }

    public function destroySubmission(ActivitySubmission $submission)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole(['teacher', 'admin'])) {
            abort(403);
        }

        if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
            Storage::disk('public')->delete($submission->file_path);
        }

        $submission->delete();

        return back()->with('success', 'Submission has been permanently deleted.');
    }
}
