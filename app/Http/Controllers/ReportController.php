<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Activity;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display the main student directory for reports.
     */
    public function index()
    {
        // 1. Get the total count of activities that are specifically 'submission' type
        $totalSubmissionActivities = Activity::where('type', 'Submission')->count();

        // 2. Get the total count of all quizzes
        $totalQuizzes = Quiz::count();

        // Combined total of (Submission-type Activities + All Quizzes)
        $systemTotal = $totalSubmissionActivities + $totalQuizzes;

        $students = User::role('student')->get()->map(function ($student) use ($systemTotal) {

            // 3. Count unique activity completions ONLY for 'submission' type activities
            $uniqueSubmissions = DB::table('activity_submissions')
                ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
                ->where('activity_submissions.user_id', $student->id)
                ->where('activities.type', 'Submission')
                ->distinct('activity_submissions.activity_id')
                ->count();

            // 4. Count unique quizzes attempted (ignores multiple retries)
            $uniqueQuizzes = DB::table('quiz_attempts')
                ->where('user_id', $student->id)
                ->distinct('quiz_id')
                ->count('quiz_id');

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'completed_count' => $uniqueSubmissions + $uniqueQuizzes,
                'total_activities' => $systemTotal ?: 1,
            ];
        });

        return Inertia::render('Reports/Index', ['students' => $students]);
    }

    /**
     * Display detailed performance with unique counts and best attempts.
     */
    public function showStudentPerformance(User $student)
    {
        // 1. Fetch activities (Shows only the latest submission per activity title)
        $completedActivities = DB::table('activity_submissions')
            ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
            ->where('activity_submissions.user_id', $student->id)
            ->select('activities.title as title', 'activity_submissions.created_at as completed_at')
            ->orderBy('activity_submissions.created_at', 'desc')
            ->get()
            ->unique('title')
            ->values();

        // 2. Fetch quizzes showing only the BEST ATTEMPT for each unique quiz
        $completedQuizzes = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('quiz_attempts.user_id', $student->id)
            ->select(
                'quizzes.title as title',
                'quiz_attempts.quiz_id',
                DB::raw('MAX(quiz_attempts.score) as score'), 
                DB::raw('MAX(quiz_attempts.total_questions) as total_questions'),
                DB::raw('MAX(quiz_attempts.created_at) as completed_at')
            )
            ->groupBy('quiz_attempts.quiz_id', 'quizzes.title')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($quiz) {
                $percentage = ($quiz->total_questions > 0)
                    ? round(($quiz->score / $quiz->total_questions) * 100)
                    : 0;

                return [
                    'title' => $quiz->title,
                    'score' => (int) $percentage,
                    'completed_at' => $quiz->completed_at,
                ];
            });

        // 3. Stats for cards using DISTINCT logic
        $quizzesTakenUnique = DB::table('quiz_attempts')
            ->where('user_id', $student->id)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $stats = [
            'quiz_avg' => $completedQuizzes->count() > 0 ? round($completedQuizzes->avg('score')) : 0,
            'activities_completed' => $completedActivities->count(),
            'quizzes_taken' => $quizzesTakenUnique 
        ];

        $existingReport = Report::where('student_id', $student->id)
            ->where('subject', 'Overall Performance')
            ->first();

        return Inertia::render('Reports/StudentDetail', [
            'student' => $student->only(['id', 'name', 'email', 'username']),
            'activities' => $completedActivities,
            'quizzes' => $completedQuizzes,
            'existingReport' => $existingReport,
            'stats' => $stats
        ]);
    }

    /**
     * Handle saving or updating the remark with custom validation message.
     */
    public function saveRemark(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'comments' => 'required|string',
        ], [
            // Custom validation message as requested
            'comments.required' => 'Evaluation is required.',
        ]);

        Report::updateOrCreate(
            ['student_id' => $validated['student_id'], 'subject' => 'Overall Performance'],
            ['teacher_id' => Auth::id(), 'comments' => $validated['comments'], 'score' => 0]
        );

        return back()->with('success', 'Evaluation saved successfully!');
    }

    /**
     * Delete the official remark.
     */
    public function deleteRemark(Report $report)
    {
        $report->delete();
        return back()->with('success', 'Remark deleted successfully!');
    }
}