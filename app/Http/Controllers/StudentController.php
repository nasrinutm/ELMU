<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity; // Ensure this model exists
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Display a list of students with Pagination & Search.
     */
    public function index(Request $request)
    {
        // 1. Start Query
        $query = User::role('student')
            // FIX: Select all fields needed by the Vue Table (username, created_at)
            ->select('id', 'name', 'email', 'username', 'created_at');

        // 2. Add Search Logic
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        // 3. FIX: Use paginate(10) instead of get() to match Vue logic
        $students = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the Student Progress page.
     */
    public function show(User $student)
    {
        // 1. Fetch all assignments created by teachers
        // Ensure you refer to the correct model namespace
        $allTeacherActivities = Activity::all();

        // 2. Fetch the student's actual submissions
        $studentSubmissions = $student->submissions()->get()->keyBy('activity_id');

        // 3. Merge them so we see "Pending" activities too
        $activitiesReport = $allTeacherActivities->map(function ($activity) use ($studentSubmissions) {
            $submission = $studentSubmissions->get($activity->id);

            return [
                'id' => $activity->id,
                'submission_id' => $submission ? $submission->id : null, // Needed for deletion
                'title' => $activity->title,
                'type' => $activity->type ?? 'General',
                'due_date' => $activity->due_date,
                'status' => $submission ? 'Completed' : 'Pending',
                'score' => $submission && $submission->score ? $submission->score : '-',
                'submitted_at' => $submission ? $submission->created_at : null,
                'is_manual' => false,
            ];
        });

        // 4. Fetch Manual Activities (Using DB Table to be safe)
        $manualActivities = DB::table('student_manual_activities')
            ->where('user_id', $student->id)
            ->get()
            ->map(function ($act) {
                return [
                    'id' => $act->id,
                    'submission_id' => null,
                    'title' => $act->title,
                    'type' => 'Manual',
                    'status' => 'Completed',
                    'score' => $act->score,
                    'submitted_at' => $act->created_at,
                    'due_date' => null,
                    'is_manual' => true,
                ];
            });

        // 5. Combine and Sort
        $finalActivities = $activitiesReport
            ->concat($manualActivities)
            ->sortByDesc(function ($item) {
                // Sort by submission date or created date
                return $item['submitted_at'] ?? $item['due_date'];
            })
            ->values();

        return Inertia::render('Students/Show', [
            'student' => $student,
            'activities' => $finalActivities
        ]);
    }

    /**
     * Store a new manual activity.
     */
    public function storeActivity(Request $request, User $student)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'score' => 'required|integer|min:0|max:100',
        ]);

        DB::table('student_manual_activities')->insert([
            'user_id' => $student->id,
            'title' => $validated['title'],
            'score' => $validated['score'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Activity recorded successfully.');
    }

    /**
     * Update an existing manual activity.
     */
    public function updateActivity(Request $request, User $student, $activityId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'score' => 'required|integer|min:0|max:100',
        ]);

        DB::table('student_manual_activities')
            ->where('id', $activityId)
            ->where('user_id', $student->id)
            ->update([
                'title' => $validated['title'],
                'score' => $validated['score'],
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Activity updated successfully.');
    }

    /**
     * Delete a manual activity.
     */
    public function destroyActivity(User $student, $activityId)
    {
        DB::table('student_manual_activities')
            ->where('id', $activityId)
            ->where('user_id', $student->id)
            ->delete();

        return back()->with('success', 'Activity record deleted.');
    }
}
