<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('student')
            ->select('id', 'name', 'email', 'username', 'created_at');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        $students = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(User $student)
    {
        // 1. Fetch all teacher-created activities
        $allTeacherActivities = Activity::all();

        // 2. Fetch the student's actual submissions directly from the DB to avoid model relationship issues
        $studentSubmissions = DB::table('activity_submissions')
            ->where('user_id', $student->id)
            ->get()
            ->keyBy('activity_id');

        // 3. Merge them so we see "Pending" vs "Completed" correctly
        $activitiesReport = $allTeacherActivities->map(function ($activity) use ($studentSubmissions) {
            $submission = $studentSubmissions->get($activity->id);

            return [
                'id' => $activity->id,
                'submission_id' => $submission ? $submission->id : null,
                'title' => $activity->title,
                'type' => $activity->type ?? 'General',
                'due_date' => $activity->due_date,
                // FIX: If submission exists in activity_submissions, it is COMPLETED
                'status' => $submission ? 'Completed' : 'Pending',
                'score' => ($submission && property_exists($submission, 'score')) ? $submission->score : '-',
                'submitted_at' => $submission ? $submission->created_at : null,
                'is_manual' => false,
            ];
        });

        // 4. Fetch Manual Activities
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
                return $item['submitted_at'] ?? $item['due_date'];
            })
            ->values();

        return Inertia::render('Students/Show', [
            'student' => $student,
            'activities' => $finalActivities
        ]);
    }

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

    public function destroyActivity(User $student, $activityId)
    {
        DB::table('student_manual_activities')
            ->where('id', $activityId)
            ->where('user_id', $student->id)
            ->delete();

        return back()->with('success', 'Activity record deleted.');
    }
}