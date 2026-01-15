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
use Carbon\Carbon;

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

        $totalSystemMaterials = Material::count();
        $totalSystemQuizzes = Quiz::count();
        $totalSystemStudents = User::role('student')->count();

        // 1. ADMIN DASHBOARD
        if ($user->hasRole('admin')) {
            return Inertia::render('AdminDashboard', [
                'auth' => ['user' => $user, 'roles' => $user->getRoleNames()],
                'stats' => [
                    'admins' => User::role('admin')->count(),
                    'teachers' => User::role('teacher')->count(),
                    'students' => $totalSystemStudents,
                    'total_users' => User::count(),
                    'materials' => $totalSystemMaterials,
                    'available_quizzes' => $totalSystemQuizzes,
                ],
                'recentUsers' => User::latest()->select('id', 'name', 'email', 'created_at')->take(5)->get()
            ]);
        }

        // 2. TEACHER DASHBOARD
        if ($user->hasRole('teacher')) {
            return Inertia::render('TeacherDashboard', [
                'auth' => ['user' => $user, 'roles' => $user->getRoleNames()],
                'stats' => [
                    'students' => $totalSystemStudents,
                    'materials' => $totalSystemMaterials,
                    'available_quizzes' => $totalSystemQuizzes,
                ],
                'recentMaterials' => Material::with('user:id,name')->latest()->take(5)->get()
            ]);
        }

        // 3. STUDENT DASHBOARD
        $uniqueSystemActivities = DB::table('activity_submissions')
            ->where('user_id', $userId)
            ->distinct('activity_id')
            ->count('activity_id');

        $manualActivitiesCount = DB::table('student_manual_activities')
            ->where('user_id', $userId)
            ->count();

        $uniqueQuizzesAttempted = DB::table('quiz_attempts')
            ->where('user_id', $userId)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $remainingQuizzes = max(0, $totalSystemQuizzes - $uniqueQuizzesAttempted);

        return Inertia::render('StudentDashboard', [
            'auth' => ['user' => $user, 'roles' => $user->getRoleNames()],
            'stats' => [
                'materials' => $totalSystemMaterials,
                'my_materials' => $uniqueSystemActivities + $manualActivitiesCount,
                'available_quizzes' => $remainingQuizzes,
            ],
            'recentMaterials' => Material::with('user:id,name')->latest()->take(5)->get()
        ]);
    }

    public function index(Request $request)
    {
        $query = User::role('student')->select('id', 'name', 'email', 'username', 'created_at');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        return Inertia::render('Students/Index', [
            'students' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Display student report with correct combined Evaluation logic.
     */
    public function show(User $student)
    {
        $now = Carbon::now();

        // 1. Get Totals from the System
        $allSystemActivities = Activity::all();
        $allSystemQuizzes = Quiz::all();

        // 2. Fetch Student Data
        $submissions = DB::table('activity_submissions')
            ->where('user_id', $student->id)
            ->get()
            ->keyBy('activity_id');

        $firstQuizAttempts = DB::table('quiz_attempts')
            ->where('user_id', $student->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('quiz_id')
            ->map(fn($attempts) => $attempts->first());

        $manualScores = DB::table('student_manual_activities')
            ->where('user_id', $student->id)
            ->get();

        // 3. BUILD THE COMBINED HISTORY LIST
        // FIX: Ensure 'id' is present for Ziggy route calls
        $activitiesHistory = $allSystemActivities->map(function ($act) use ($submissions, $now) {
            $sub = $submissions->get($act->id);
            $isOverdue = !$sub && $act->due_date && Carbon::parse($act->due_date)->isPast();
            return [
                'id' => $act->id, // Critical for Ziggy
                'title' => $act->title,
                'type' => 'Activity',
                'status' => $sub ? 'COMPLETED' : ($isOverdue ? 'OVERDUE' : 'PENDING'),
                'score' => $sub->score ?? '-',
                'submitted_at' => $sub->created_at ?? null,
                'due_date' => $act->due_date,
            ];
        });

        $quizzesHistory = $allSystemQuizzes->map(function ($quiz) use ($firstQuizAttempts) {
            $attempt = $firstQuizAttempts->get($quiz->id);
            $scoreDisplay = $attempt ? (($attempt->total_questions > 0) ? round(($attempt->score / $attempt->total_questions) * 100) . '%' : '0%') : '-';
            return [
                'id' => $quiz->id, // Critical for Ziggy
                'title' => $quiz->title . ($attempt ? '' : ' (Not Attempted)'),
                'type' => 'Quiz',
                'status' => $attempt ? 'COMPLETED' : 'PENDING',
                'score' => $scoreDisplay,
                'submitted_at' => $attempt->created_at ?? null,
                'due_date' => null,
            ];
        });

        $manualHistory = $manualScores->map(function ($m) {
            return [
                'id' => $m->id,
                'title' => $m->title,
                'type' => 'Manual',
                'status' => 'COMPLETED',
                'score' => $m->score . '%',
                'submitted_at' => $m->created_at,
                'due_date' => null,
            ];
        });

        // 4. STATS CALCULATION
        $totalActivitiesCount = $allSystemActivities->count();
        $totalQuizzesCount = $allSystemQuizzes->count();
        $totalManualCount = $manualScores->count();

        $totalSystemPossible = $totalActivitiesCount + $totalQuizzesCount;
        $completedOverall = $submissions->count() + $firstQuizAttempts->count();
        $pendingOverall = max(0, $totalSystemPossible - $completedOverall);

        $overdueCount = $allSystemActivities->filter(function($act) use ($submissions, $now) {
            return !$submissions->has($act->id) && $act->due_date && Carbon::parse($act->due_date)->isPast();
        })->count();

        $assignmentPercent = ($totalActivitiesCount > 0) ? round(($submissions->count() / $totalActivitiesCount) * 100) : 0;
        $evalPoolTotal = $totalQuizzesCount + $totalManualCount;
        $evalPoolDone = $firstQuizAttempts->count() + $totalManualCount;
        $evaluationPercent = ($evalPoolTotal > 0) ? round(($evalPoolDone / $evalPoolTotal) * 100) : 0;

        $finalTimeline = $activitiesHistory
            ->concat($quizzesHistory)
            ->concat($manualHistory)
            ->sortByDesc(fn($item) => $item['submitted_at'] ?? $item['due_date'])
            ->values();

        return Inertia::render('Students/Show', [
            'student' => $student,
            'activities' => $finalTimeline,
            'stats' => [
                'total' => (int)$totalSystemPossible,
                'completed' => (int)$completedOverall,
                'pending' => (int)$pendingOverall,
                'overdue' => (int)$overdueCount,
            ],
            'completion_stats' => [
                'activity' => (int)min(100, $assignmentPercent),
                'evaluation' => (int)min(100, $evaluationPercent),
                'raw_activity_total' => $totalActivitiesCount,
                'raw_quiz_total' => $totalQuizzesCount,
            ]
        ]);
    }

    public function storeActivity(Request $request, User $student)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100', // Changed to numeric for better safety
        ]);

        DB::table('student_manual_activities')->insert([
            'user_id' => $student->id,
            'title' => $validated['title'],
            'score' => $validated['score'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Manual activity recorded.');
    }

    public function updateActivity(Request $request, User $student, $activityId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        DB::table('student_manual_activities')
            ->where('id', $activityId)
            ->where('user_id', $student->id)
            ->update([
                'title' => $validated['title'],
                'score' => $validated['score'],
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Activity record updated.');
    }

    public function destroyActivity(User $student, $activityId)
    {
        DB::table('student_manual_activities')
            ->where('id', $activityId)
            ->where('user_id', $student->id)
            ->delete();

        return back()->with('success', 'Record removed.');
    }
}
