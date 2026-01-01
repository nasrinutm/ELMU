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
     * Get statistics for the Merged Dashboard based on User Role.
     */
    public function dashboardStats()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        if ($user->hasRole('admin')) {
            return Inertia::render('Dashboard', [
                'auth' => [
                    'user' => $user,
                    'roles' => $user->getRoleNames(),
                ],
                'stats' => [
                    'admins' => User::role('admin')->count(),
                    'teachers' => User::role('teacher')->count(),
                    'students' => User::role('student')->count(),
                    'total_users' => User::count(),
                ],
                'recentUsers' => User::latest()
                    ->select('id', 'name', 'email', 'created_at')
                    ->take(5)
                    ->get()
            ]);
        }

        $totalQuizzes = Quiz::count();
        $attemptedQuizCount = DB::table('quiz_attempts')
            ->where('user_id', $userId)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $remainingQuizzes = max(0, $totalQuizzes - $attemptedQuizCount);
        $materialsCount = Material::count();

        $activitiesDone = DB::table('activity_submissions')
            ->where('user_id', $userId)
            ->count();

        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => $user,
                'roles' => $user->getRoleNames(),
            ],
            'stats' => [
                'materials' => $materialsCount,
                'my_materials' => $activitiesDone,
                'available_quizzes' => $remainingQuizzes,
            ],
            'recentMaterials' => Material::with('user:id,name')
                ->latest()
                ->take(5)
                ->get()
        ]);
    }

    /**
     * Display a listing of students.
     */
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

    /**
     * Display the specified student's activity and quiz report.
     */
    public function show(User $student)
    {
        // 1. Fetch Standard System Activities
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
                'type' => 'Activity',
                'due_date' => $activity->due_date,
                'status' => $submission ? 'Completed' : 'Pending',
                'score' => $submission->score ?? '-',
                'submitted_at' => $submission->created_at ?? null,
                'is_manual' => false,
            ];
        });

        // 2. Fetch Quiz Progress with Attempt Labels
        $quizSubmissions = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quiz_attempts.user_id', $student->id)
            ->select('quiz_attempts.id', 'quiz_attempts.quiz_id', 'quizzes.title', 'quiz_attempts.score', 'quiz_attempts.total_questions', 'quiz_attempts.created_at')
            ->orderBy('quiz_attempts.created_at', 'asc') // Sort by date to track order of attempts
            ->get();

        // Tracker for attempt numbering
        $attemptTracker = [];
        $formattedQuizzes = $quizSubmissions->map(function ($q) use (&$attemptTracker) {
            // Increment attempt count for this specific quiz_id
            $attemptTracker[$q->quiz_id] = ($attemptTracker[$q->quiz_id] ?? 0) + 1;
            $attemptNo = $attemptTracker[$q->quiz_id];

            // Add label if it's a second attempt or higher
            $displayTitle = $q->title . ($attemptNo > 1 ? " (Attempt $attemptNo)" : "");

            $percentage = ($q->total_questions > 0) ? round(($q->score / $q->total_questions) * 100) : 0;

            return [
                'id' => $q->id,
                'submission_id' => $q->id,
                'title' => $displayTitle,
                'type' => 'Quiz',
                'status' => 'Completed',
                'score' => $percentage . '%',
                'submitted_at' => $q->created_at,
                'due_date' => null,
                'is_manual' => false,
            ];
        });

        // 3. Fetch Manual Activities
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

        // 4. FIX: UNIQUE STATS CALCULATION

        // Count each Activity and each Quiz only once for the total card (4 Quiz + 2 Activity = 6)
        $totalSystemQuizzes = Quiz::count();
        $totalSystemActivities = Activity::count();
        $totalUniqueItemsPossible = $totalSystemQuizzes + $totalSystemActivities;

        // Completion logic (Uses unique counts)
        $uniqueActivitiesDone = DB::table('activity_submissions')
            ->where('user_id', $student->id)
            ->distinct('activity_id')
            ->count('activity_id');

        $uniqueQuizzesDone = DB::table('quiz_attempts')
            ->where('user_id', $student->id)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $assignmentCircle = $totalSystemActivities > 0 ? min(100, round(($uniqueActivitiesDone / $totalSystemActivities) * 100)) : 0;
        $quizCircle = $totalSystemQuizzes > 0 ? min(100, round(($uniqueQuizzesDone / $totalSystemQuizzes) * 100)) : 0;

        // 5. Combine for timeline
        $finalTimeline = $activitiesReport
            ->concat($formattedQuizzes)
            ->concat($manualActivities)
            ->sortByDesc(function ($item) {
                return $item['submitted_at'] ?? $item['due_date'];
            })
            ->values();

        return Inertia::render('Students/Show', [
            'student' => $student,
            'activities' => $finalTimeline,
            'total_unique_count' => $totalUniqueItemsPossible, // Sends "6" to the card
            'completion_stats' => [
                'activity' => $assignmentCircle,
                'quiz' => $quizCircle,
                'raw_activity_total' => $totalSystemActivities,
                'raw_quiz_total' => $totalSystemQuizzes,
            ]
        ]);
    }

    /**
     * Store a manual activity for a student.
     */
    public function storeActivity(Request $request, User $student)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'score' => 'required|string|max:10',
        ]);

        DB::table('student_manual_activities')->insert([
            'user_id' => $student->id,
            'title' => $validated['title'],
            'score' => $validated['score'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Manual activity recorded. [' . now()->timestamp . ']');
    }

    /**
     * Update a manual activity for a student.
     */
    public function updateActivity(Request $request, User $student, $activityId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'score' => 'required|string|max:10',
        ]);

        DB::table('student_manual_activities')
            ->where('id', $activityId)
            ->where('user_id', $student->id)
            ->update([
                'title' => $validated['title'],
                'score' => $validated['score'],
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Activity record updated. [' . now()->timestamp . ']');
    }

    /**
     * Remove a manual activity for a student.
     */
    public function destroyActivity(User $student, $activityId)
    {
        DB::table('student_manual_activities')
            ->where('id', $activityId)
            ->where('user_id', $student->id)
            ->delete();

        return back()->with('success', 'Record removed. [' . now()->timestamp . ']');
    }
}
