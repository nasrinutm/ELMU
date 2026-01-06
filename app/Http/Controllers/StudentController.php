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

        // --- GLOBAL SYSTEM COUNTS ---
        $totalSystemMaterials = Material::count();
        $totalSystemQuizzes = Quiz::count();
        $totalSystemStudents = User::role('student')->count();

        // 1. ADMIN DASHBOARD LOGIC
        if ($user->hasRole('admin')) {
            return Inertia::render('AdminDashboard', [
                'auth' => [
                    'user' => $user,
                    'roles' => $user->getRoleNames(),
                ],
                'stats' => [
                    'admins' => User::role('admin')->count(),
                    'teachers' => User::role('teacher')->count(),
                    'students' => $totalSystemStudents,
                    'total_users' => User::count(),
                    'materials' => $totalSystemMaterials,
                    'available_quizzes' => $totalSystemQuizzes, // Admins see all quizzes
                ],
                'recentUsers' => User::latest()
                    ->select('id', 'name', 'email', 'created_at')
                    ->take(5)
                    ->get()
            ]);
        }

        // 2. TEACHER DASHBOARD LOGIC
        if ($user->hasRole('teacher')) {
            return Inertia::render('TeacherDashboard', [
                'auth' => [
                    'user' => $user,
                    'roles' => $user->getRoleNames(),
                ],
                'stats' => [
                    'students' => $totalSystemStudents,
                    'materials' => $totalSystemMaterials,
                    'available_quizzes' => $totalSystemQuizzes, // Teachers see all quizzes
                ],
                'recentMaterials' => Material::with('user:id,name')
                    ->latest()
                    ->take(5)
                    ->get()
            ]);
        }

        // 3. STUDENT DASHBOARD LOGIC - FIXED UNIQUE COUNTS

        // Count unique system assignments submitted
        $uniqueSystemActivities = DB::table('activity_submissions')
            ->where('user_id', $userId)
            ->distinct('activity_id')
            ->count('activity_id');

        // Count manual activities added by teacher
        $manualActivitiesCount = DB::table('student_manual_activities')
            ->where('user_id', $userId)
            ->count();

        // Calculate unique quizzes attempted (ignores retakes for the count)
        $uniqueQuizzesAttempted = DB::table('quiz_attempts')
            ->where('user_id', $userId)
            ->distinct('quiz_id')
            ->count('quiz_id');

        // For students, "Available" means "Remaining Unattempted Quizzes"
        $remainingQuizzes = max(0, $totalSystemQuizzes - $uniqueQuizzesAttempted);

        return Inertia::render('StudentDashboard', [
            'auth' => [
                'user' => $user,
                'roles' => $user->getRoleNames(),
            ],
            'stats' => [
                'materials' => $totalSystemMaterials,
                'my_materials' => $uniqueSystemActivities + $manualActivitiesCount, // Correctly shows total unique work done
                'available_quizzes' => $remainingQuizzes, // Correctly shows 0 if all quizzes are tried at least once
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
                'percentage' => null,
                'submitted_at' => $submission->created_at ?? null,
                'is_manual' => false,
            ];
        });

        $quizSubmissions = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quiz_attempts.user_id', $student->id)
            ->select('quiz_attempts.id', 'quiz_attempts.quiz_id', 'quizzes.title', 'quiz_attempts.score', 'quiz_attempts.total_questions', 'quiz_attempts.created_at')
            ->orderBy('quiz_attempts.created_at', 'asc')
            ->get();

        $attemptTracker = [];
        $formattedQuizzes = $quizSubmissions->map(function ($q) use (&$attemptTracker) {
            $attemptTracker[$q->quiz_id] = ($attemptTracker[$q->quiz_id] ?? 0) + 1;
            $attemptNo = $attemptTracker[$q->quiz_id];
            $displayTitle = $q->title . ($attemptNo > 1 ? " (Attempt $attemptNo)" : "");
            $rawPercentage = ($q->total_questions > 0) ? round(($q->score / $q->total_questions) * 100) : 0;

            return [
                'id' => $q->id,
                'submission_id' => $q->id,
                'title' => $displayTitle,
                'type' => 'Quiz',
                'status' => 'Completed',
                'score' => $rawPercentage . '%',
                'percentage' => $rawPercentage,
                'submitted_at' => $q->created_at,
                'due_date' => null,
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
                    'percentage' => null,
                    'submitted_at' => $act->created_at,
                    'due_date' => null,
                    'is_manual' => true,
                ];
            });

        $uniqueActivitiesDoneCount = DB::table('activity_submissions')
            ->where('user_id', $student->id)
            ->distinct('activity_id')
            ->count('activity_id');

        $uniqueQuizzesDoneCount = DB::table('quiz_attempts')
            ->where('user_id', $student->id)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $totalSystemQuizzes = Quiz::count();
        $totalSystemActivities = Activity::count();
        $totalUniqueItemsPossible = $totalSystemQuizzes + $totalSystemActivities;

        $assignmentCircle = $totalSystemActivities > 0 ? min(100, round(($uniqueActivitiesDoneCount / $totalSystemActivities) * 100)) : 0;
        $quizCircle = $totalSystemQuizzes > 0 ? min(100, round(($uniqueQuizzesDoneCount / $totalSystemQuizzes) * 100)) : 0;

        $finalTimeline = $activitiesReport
            ->concat($formattedQuizzes)
            ->concat($manualActivities)
            ->sortByDesc(fn($item) => $item['submitted_at'] ?? $item['due_date'])
            ->values();

        return Inertia::render('Students/Show', [
            'student' => $student,
            'activities' => $finalTimeline,
            'total_unique_count' => $totalUniqueItemsPossible,
            'true_completed_count' => $uniqueActivitiesDoneCount + $uniqueQuizzesDoneCount,
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

        return back()->with('success', 'Manual activity recorded.');
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

        return back()->with('success', 'Activity record updated.');
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

        return back()->with('success', 'Record removed.');
    }
}
