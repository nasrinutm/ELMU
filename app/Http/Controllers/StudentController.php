<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Display a list of students (for the 'Students' sidebar link).
     */
    public function index()
    {
        // Fetch users with the 'student' role
        // Ensure you have Spatie Permissions or a similar role setup
        $students = User::role('student')
            ->select('id', 'name', 'email')
            ->withCount('submissions')
            ->get(); // <--- Use get() to return a clean Array

        return Inertia::render('Students/Index', [
            'students' => $students
        ]);
    }

    /**
     * Show the Student Progress page (The Vue file you provided).
     */
    public function show(User $student)
{
    // 1. Fetch all assignments created by teachers
    $allTeacherActivities = \App\Models\Activity::all();

    // 2. Fetch the student's actual submissions
    $studentSubmissions = $student->submissions()->get()->keyBy('activity_id');

    // 3. Merge them so we see "Pending" activities too
    $activitiesReport = $allTeacherActivities->map(function ($activity) use ($studentSubmissions) {
        $submission = $studentSubmissions->get($activity->id);
        
        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'type' => $activity->type,
            'due_date' => $activity->due_date, // This enables your overdue logic
            'status' => $submission ? 'Completed' : 'Pending',
            'score' => $submission && $submission->score ? $submission->score : '-',
            'submitted_at' => $submission ? $submission->created_at : null,
            'is_manual' => false,
        ];
    });

    // 4. Fetch Manual Activities (Teacher-added custom scores)
    $manualActivities = $student->manualActivities()->get()->map(function ($act) {
        return [
            'id' => $act->id,
            'title' => $act->title,
            'type' => 'Manual',
            'status' => 'Completed',
            'score' => $act->score,
            'submitted_at' => $act->created_at,
            'due_date' => null,
            'is_manual' => true,
        ];
    });

    return Inertia::render('Students/Show', [
        'student' => $student,
        'activities' => $activitiesReport->concat($manualActivities)->sortByDesc('due_date')->values()
    ]);
}

    /**
     * Store a new manual activity (from the Modal).
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