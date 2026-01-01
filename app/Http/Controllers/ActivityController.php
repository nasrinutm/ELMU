<?php

namespace App\Http\Controllers;

use App\Models\Activity;
// REMOVED: use App\Models\Quiz; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $permissions = ['manage_activities' => false];
        if ($user) {
            $permissions['manage_activities'] = $user->role === 'teacher' || $user->role === 'admin'; 
        }

        // --- FETCH ACTIVITIES ONLY ---
        $query = Activity::query()->with('user:id,name');

        // IF STUDENT: Only show their own activities
        // IF TEACHER: Show ALL
        if ($user->role !== 'teacher' && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        
        // Sorting
        $sortField = $request->input('sort_by', 'created_at'); 
        $sortDirection = $request->input('sort', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // --- PAGINATION ---
        $activities = $query->paginate(50)->through(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type ?? 'Activity', 
                'status' => $item->status ?? 'pending',
                'score' => $item->score ?? '-',
                'due_date' => $item->due_date,
                'file_path' => $item->file_path,
                'created_at' => $item->created_at,
                'model_type' => 'activity'
            ];
        });

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

        $activity = Activity::create($data);

        // Auto-assign to creator
        DB::table('activity_user')->insert([
            'activity_id' => $activity->id,
            'user_id' => Auth::id(), 
            'status' => 'pending',
            'score' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('activities.index')
            ->with('success', 'Activity created successfully.');
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
        
        DB::table('activity_user')->where('activity_id', $activity->id)->delete();
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
}