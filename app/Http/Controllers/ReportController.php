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
    public function index()
{
    // 1. Get the total count of activities that are specifically 'submission' type
    $totalSubmissionActivities = \App\Models\Activity::where('type', 'Submission')->count();
    
    // 2. Get the total count of all quizzes
    $totalQuizzes = \App\Models\Quiz::count();
    
    // Combined total of (Submission-type Activities + All Quizzes)
    $systemTotal = $totalSubmissionActivities + $totalQuizzes;

    $students = User::role('student')->get()->map(function ($student) use ($systemTotal) {
        
        // 3. Count unique activity completions ONLY for 'submission' type activities
        $uniqueSubmissions = \DB::table('activity_submissions')
            ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
            ->where('activity_submissions.user_id', $student->id)
            ->where('activities.type', 'Submission')
            ->distinct('activity_submissions.activity_id')
            ->count();

        // 4. Count unique quizzes attempted
        $uniqueQuizzes = \DB::table('quiz_attempts')
            ->where('user_id', $student->id)
            ->distinct('quiz_id')
            ->count();

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            // Sum of unique submission activities and unique quiz attempts
            'completed_count' => $uniqueSubmissions + $uniqueQuizzes,
            'total_activities' => $systemTotal ?: 1, // Combined denominator
        ];
    });

    return Inertia::render('Reports/Index', ['students' => $students]);
}

    /**
     * Display detailed performance with joined quiz titles.
     */
    public function showStudentPerformance(User $student)
    {
        // 1. Fetch activities with a join to get real titles
        $completedActivities = DB::table('activity_submissions')
            ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
            ->where('activity_submissions.user_id', $student->id)
            ->select('activities.title as title', 'activity_submissions.created_at as completed_at')
            ->orderBy('activity_submissions.created_at', 'desc')
            ->get();

        // 2. Fetch quizzes by JOINING the quizzes table to get the real TITLE
        $completedQuizzes = DB::table('quiz_attempts')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id') // This link gets the title
            ->where('quiz_attempts.user_id', $student->id)
            ->select(
                'quizzes.title as title', // Get the name from quizzes table
                'quiz_attempts.score',
                'quiz_attempts.total_questions',
                'quiz_attempts.created_at as completed_at'
            )
            ->orderBy('quiz_attempts.created_at', 'desc')
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

        // 3. Stats for cards
        $stats = [
            'quiz_avg' => $completedQuizzes->count() > 0 ? round($completedQuizzes->avg('score')) : 0,
            'activities_completed' => $completedActivities->count()
        ];

        $existingReport = Report::where('student_id', $student->id)
            ->where('subject', 'Overall Performance')
            ->first();

        // Render to the specific StudentDetail view
        return Inertia::render('Reports/StudentDetail', [
            'student' => $student->only(['id', 'name', 'email', 'username']),
            'activities' => $completedActivities,
            'quizzes' => $completedQuizzes,
            'existingReport' => $existingReport,
            'stats' => $stats
        ]);
    }

    public function saveRemark(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'comments' => 'required|string',
        ]);

        Report::updateOrCreate(
            ['student_id' => $validated['student_id'], 'subject' => 'Overall Performance'],
            ['teacher_id' => Auth::id(), 'comments' => $validated['comments'], 'score' => 0]
        );

        return back()->with('success', 'Evaluation saved successfully!');
    }

    public function deleteRemark(Report $report)
    {
        $report->delete();
        return back()->with('success', 'Remark deleted successfully!');
    }
}
