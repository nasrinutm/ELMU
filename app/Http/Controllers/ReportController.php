<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $students = User::role('student')->get(['id', 'name', 'email']);
        return Inertia::render('Reports/Index', ['students' => $students]);
    }

    public function showStudentPerformance(User $student)
    {
        // 1. Fetch activities
        $completedActivities = DB::table('activity_submissions')
            ->join('activities', 'activity_submissions.activity_id', '=', 'activities.id')
            ->where('activity_submissions.user_id', $student->id)
            ->select('activities.title as title', 'activity_submissions.created_at as completed_at')
            ->orderBy('activity_submissions.created_at', 'desc')
            ->get();

        // 2. Fetch quizzes with title fallback
        $completedQuizzes = DB::table('quiz_attempts')
            ->where('user_id', $student->id)
            ->select('quiz_title', 'score', 'total_questions', 'created_at as completed_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($quiz) {
                $percentage = ($quiz->total_questions > 0)
                    ? round(($quiz->score / $quiz->total_questions) * 100)
                    : 0;

                return [
                    'title' => $quiz->quiz_title ?: 'General Assessment', // Fallback for blank titles
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
