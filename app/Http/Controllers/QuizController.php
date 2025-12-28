<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuizController extends Controller
{
    // 1. STUDENT DASHBOARD: Fetch Quizzes with SEARCH & FILTER
    public function index(Request $request)
    {
        // Start the query builder
        $query = Quiz::query();

        // Filter by Search Term (if user typed something)
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by Difficulty (if user selected specific level)
        if ($request->filled('difficulty') && $request->difficulty !== 'All') {
            $query->where('difficulty', $request->difficulty);
        }

        // Execute Query & Map Data
        $quizzes = $query->get()->map(function ($quiz) {
            
            $attempts = QuizAttempt::where('user_id', Auth::id())
                ->where('quiz_id', $quiz->id)
                ->count();
            
            $access = QuizAccess::where('user_id', Auth::id())
                ->where('quiz_id', $quiz->id)
                ->first();
            
            $extra = $access ? $access->extra_attempts : 0;
            $limit = 3 + $extra;

            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'questions_count' => $quiz->questions()->count(),
                'time_limit' => floor($quiz->duration / 60) . ' mins',
                'difficulty' => $quiz->difficulty,
                'attempts_taken' => $attempts,
                'attempts_max' => $limit,
                'attempts_left' => max(0, $limit - $attempts),
                'is_locked' => $attempts >= $limit,
            ];
        })->values();

        return Inertia::render('Quiz/Index', [
            'quizzes' => $quizzes,
            'filters' => $request->only(['search', 'difficulty']), // Pass filters back to frontend
        ]);
    }

    // 2. TAKE QUIZ: Fetch REAL questions/options
    public function show($id)
    {
        // Find quiz or fail (404)
        $quizModel = Quiz::with(['questions.options'])->findOrFail($id);

        // Security Check: Lock if max attempts reached
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->count();

        // Check for Extra Attempts granted by teacher
        $access = QuizAccess::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->first();

        $extra = $access ? $access->extra_attempts : 0;
        $limit = 3 + $extra;

        if ($attempts >= $limit) {
            return redirect()->route('quiz.index')->with('error', "Maximum attempts ($limit) reached for this quiz.");
        }

        // Transform Data for Vue Frontend
        $quizData = [
            'id' => $quizModel->id,
            'title' => $quizModel->title,
            'duration' => $quizModel->duration,
            'questions' => $quizModel->questions->map(function($q) {
                // Find the correct option ID for grading logic
                $correctOption = $q->options->where('is_correct', true)->first();
                
                return [
                    'id' => $q->id,
                    'text' => $q->text,
                    'explanation' => $q->explanation,
                    'correct_option_id' => $correctOption ? $correctOption->id : null,
                    'options' => $q->options->map(function($opt) {
                        return [
                            'id' => $opt->id, 
                            'text' => $opt->text
                            // Note: We do NOT send 'is_correct' here
                        ];
                    })
                ];
            })
        ];

        return Inertia::render('Quiz/Show', [
            'quiz' => $quizData
        ]);
    }

    // 3. SUBMIT: Save the attempt
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|integer',
            'quiz_title' => 'required|string',
            'score' => 'required|integer',
            'total_questions' => 'required|integer',
        ]);

        // Double check lock before saving
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $validated['quiz_id'])
            ->count();

        // Check for Extra Attempts granted by teacher
        $access = QuizAccess::where('user_id', Auth::id())
            ->where('quiz_id', $validated['quiz_id'])
            ->first();

        $extra = $access ? $access->extra_attempts : 0;
        $limit = 3 + $extra;

        if ($attempts >= $limit) {
            return redirect()->back()->with('error', 'Cannot submit. Max attempts reached.');
        }

        QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $validated['quiz_id'],
            'quiz_title' => $validated['quiz_title'],
            'score' => $validated['score'],
            'total_questions' => $validated['total_questions'],
        ]);

        return redirect()->back()->with('success', 'Quiz result saved!');
    }

    // 4. HISTORY: View past results
    public function history($id)
    {
        $quiz = Quiz::find($id);
        $quizTitle = $quiz ? $quiz->title : 'Quiz History';

        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->latest()
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'total' => $attempt->total_questions,
                    'date' => $attempt->created_at->format('M d, Y, h:i A'),
                    'percentage' => $attempt->total_questions > 0 
                        ? round(($attempt->score / $attempt->total_questions) * 100) 
                        : 0,
                ];
            });

        $stats = [
            'attempts' => $attempts->count(),
            'average' => $attempts->count() > 0 ? round($attempts->avg('percentage')) : 0,
            'best' => $attempts->count() > 0 ? $attempts->max('percentage') : 0,
        ];

        return Inertia::render('Quiz/History', [
            'quizTitle' => $quizTitle,
            'attempts' => $attempts,
            'stats' => $stats
        ]);
    }
}