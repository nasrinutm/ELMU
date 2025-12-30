<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuizController extends Controller
{
    // 1. STUDENT DASHBOARD: Fetch Quizzes
    public function index(Request $request)
    {
        $query = Quiz::query();

        // Search Filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Difficulty Filter
        if ($request->filled('difficulty') && $request->difficulty !== 'All') {
            $query->where('difficulty', $request->difficulty);
        }

        $quizzes = $query->get()->map(function ($quiz) {
            
            // Count how many times user attempted this quiz
            $attempts = QuizAttempt::where('user_id', Auth::id())
                ->where('quiz_id', $quiz->id)
                ->count();

            // Default limit is 3 (Since we removed the max_attempts column for simplicity)
            $limit = 3; 

            // Handle questions count safely from JSON
            $questionCount = is_array($quiz->content) ? count($quiz->content) : 0;

            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'questions_count' => $questionCount,
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
            'filters' => $request->only(['search', 'difficulty']),
        ]);
    }

    // 2. TAKE QUIZ: Prepare JSON questions for Frontend
    public function show($id)
    {
        // Fetch quiz (No need for 'with' relations anymore)
        $quizModel = Quiz::findOrFail($id);

        // Security: Check Attempts Limit
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->count();
        
        $limit = 3; // Hardcoded limit for 2-table system

        if ($attempts >= $limit) {
            return redirect()->route('quiz.index')->with('error', "Maximum attempts reached.");
        }

        // Transform JSON Data for Vue
        // We must loop through the JSON array to format it correctly
        $rawQuestions = $quizModel->content ?? []; // Get JSON content or empty array
        
        $formattedQuestions = collect($rawQuestions)->map(function($q) {
            // Find correct option ID inside the JSON structure
            $correctOptionId = null;
            if (isset($q['options']) && is_array($q['options'])) {
                foreach ($q['options'] as $index => $opt) {
                    if (isset($opt['is_correct']) && $opt['is_correct'] === true) {
                        // We use the array index or a specific ID if you added one in seeder
                        $correctOptionId = $opt['id'] ?? $index; 
                        break;
                    }
                }
            }

            return [
                'id' => $q['id'] ?? rand(1000,9999), // Fallback ID if missing
                'text' => $q['question'] ?? $q['text'] ?? 'No text', // Handle 'question' vs 'text' key
                'explanation' => $q['explanation'] ?? '',
                'correct_option_id' => $correctOptionId,
                'options' => collect($q['options'] ?? [])->map(function($opt, $index) {
                    return [
                        'id' => $opt['id'] ?? $index,
                        'text' => $opt['text'] ?? '',
                        // We DO NOT send 'is_correct' to frontend to prevent cheating
                    ];
                }),
            ];
        });

        return Inertia::render('Quiz/Show', [
            'quiz' => [
                'id' => $quizModel->id,
                'title' => $quizModel->title,
                'duration' => $quizModel->duration,
                'questions' => $formattedQuestions
            ]
        ]);
    }

    // 3. SUBMIT: Save Score
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|integer',
            'quiz_title' => 'required|string',
            'score' => 'required|integer',
            'total_questions' => 'required|integer',
        ]);

        // Double Check Limit
        $limit = 3;
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $validated['quiz_id'])
            ->count();

        if ($attempts >= $limit) {
            return redirect()->back()->with('error', 'Max attempts reached.');
        }

        QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $validated['quiz_id'],
            'score' => $validated['score'],
            'total_questions' => $validated['total_questions'],
        ]);

        return redirect()->route('quiz.history', $validated['quiz_id'])
                ->with('success', 'Quiz submitted successfully!');
    }

    // 4. HISTORY: View Results
    public function history($id)
    {
        $quiz = Quiz::findOrFail($id);
        
        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->latest()
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'total' => $attempt->total_questions,
                    'date' => $attempt->created_at->format('d M Y, h:i A'),
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
            'quizTitle' => $quiz->title,
            'attempts' => $attempts,
            'stats' => $stats
        ]);
    }
}