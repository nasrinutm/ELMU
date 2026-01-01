<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use App\Models\Quiz;
use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Get statistics for the Student Dashboard.
     */
    public function dashboardStats()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        // 1. Total unique quiz modules available
        $totalQuizzes = Quiz::count();

        // 2. Identify unique quizzes the student has attempted
        $attemptedQuizCount = DB::table('quiz_attempts')
            ->where('user_id', $userId)
            ->distinct('quiz_id')
            ->count('quiz_id');

        // 3. Remaining quizzes logic
        $remainingQuizzes = $totalQuizzes - $attemptedQuizCount;

        // 4. Other Dashboard Stats
        $materialsCount = Material::count();

        // Count total activities submitted by this user
        $activitiesDone = DB::table('activity_submissions')
            ->where('user_id', $userId)
            ->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'materials' => $materialsCount,
                // FIX: Key changed from 'activities_done' to 'my_materials' to match Dashboard.vue
                'my_materials' => $activitiesDone,
                'available_quizzes' => max(0, $remainingQuizzes),
            ],
            'recentMaterials' => Material::with('user:id,name')
                ->latest()
                ->take(5)
                ->get()
        ]);
    }

    // ... rest of your methods (index, show, storeActivity, etc.) stay exactly the same ...

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
        $allTeacherActivities = Activity::all();

        $studentSubmissions = DB::table('activity_submissions')
            ->where('user_id', $student->id)
            ->get()
            ->keyBy('activity_id');

        $activitiesReport = $allTeacherActivities->map(function ($activity) use ($studentSubmissions) {
            $submission = $studentSubmissions->get($activity->id);

            return [
                'id' => $activity->id,
                'submission_id' => $submission ? $submission->id : null,
                'title' => $activity->title,
                'type' => $activity->type ?? 'General',
                'due_date' => $activity->due_date,
                'status' => $submission ? 'Completed' : 'Pending',
                'score' => $submission->score ?? '-',
                'submitted_at' => $submission->created_at ?? null,
                'is_manual' => false,
            ];
        });

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
