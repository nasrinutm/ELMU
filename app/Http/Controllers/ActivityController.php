<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
            $permissions['manage_activities'] = $user->hasRole(['admin', 'teacher']);
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
            // Move from 'public' disk to 'supabase' disk
            $data['file_path'] = $file->store('activities', 'supabase');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        Activity::create($data);

        return redirect()->route('activities.index')
            ->with('success', 'Activity successfully deployed to the cloud.');
    }

    public function show(Activity $activity)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isTeacher = $user->hasRole(['admin', 'teacher']);

        $activity->load('mySubmission');

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

    public function submit(Request $request, Activity $activity)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,docx,pptx,zip', 'max:10240'],
        ]);

        $file = $request->file('file');
        // Store student submission in Supabase
        $path = $file->store('submissions', 'supabase');

        ActivitySubmission::updateOrCreate(
            ['user_id' => Auth::id(), 'activity_id' => $activity->id],
            [
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Work submitted to cloud vault successfully!');
    }

    public function downloadSubmission(ActivitySubmission $submission)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($submission->user_id !== $user->id && !$user->hasRole(['admin', 'teacher'])) {
            abort(403);
        }

        // Use the "Friend Method" (Redirect to Temporary Supabase URL)
        if (!Storage::disk('supabase')->exists($submission->file_path)) {
            return back()->with('error', 'File not found in cloud storage.');
        }

        $url = Storage::disk('supabase')->temporaryUrl(
            $submission->file_path,
            now()->addMinutes(15)
        );

        return redirect($url);
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
            // Remove old file from Supabase before uploading new one
            if ($activity->file_path && Storage::disk('supabase')->exists($activity->file_path)) {
                Storage::disk('supabase')->delete($activity->file_path);
            }

            $file = $request->file('file');
            $data['file_path'] = $file->store('activities', 'supabase');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        $activity->update($data);

        return redirect()->route('activities.index')
            ->with('success', 'Activity updated and re-synced with cloud.');
    }

    public function destroy(Activity $activity)
    {
        if ($activity->file_path && Storage::disk('supabase')->exists($activity->file_path)) {
            Storage::disk('supabase')->delete($activity->file_path);
        }
        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Activity removed from cloud permanently.');
    }

    public function download(Activity $activity)
    {
        if (!$activity->file_path || !Storage::disk('supabase')->exists($activity->file_path)) {
            return back()->with('error', 'File not found in cloud storage.');
        }

        // Generate temporary URL for the activity file
        $url = Storage::disk('supabase')->temporaryUrl(
            $activity->file_path,
            now()->addMinutes(15)
        );

        return redirect($url);
    }

    public function unsubmit(Activity $activity)
    {
        $submission = ActivitySubmission::where('activity_id', $activity->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$submission) {
            return back()->with('error', 'No submission found to remove.');
        }

        if ($submission->created_at->diffInMinutes(now()) >= 2) {
            return back()->with('error', 'Time limit exceeded (2 mins). Contact your teacher.');
        }

        if ($submission->file_path && Storage::disk('supabase')->exists($submission->file_path)) {
            Storage::disk('supabase')->delete($submission->file_path);
        }

        $submission->delete();

        return back()->with('success', 'Submission removed. Cloud storage updated.');
    }

    public function destroySubmission(ActivitySubmission $submission)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole(['teacher', 'admin'])) {
            abort(403);
        }

        if ($submission->file_path && Storage::disk('supabase')->exists($submission->file_path)) {
            Storage::disk('supabase')->delete($submission->file_path);
        }

        $submission->delete();

        return back()->with('success', 'Submission has been deleted from cloud.');
    }
}
