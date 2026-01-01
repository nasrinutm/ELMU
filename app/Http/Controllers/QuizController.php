<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuizController extends Controller
{
    /**
     * 1. Fetch Quizzes for Dashboard
     */
    public function index(Request $request)
    {
        $query = Quiz::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('difficulty') && $request->difficulty !== 'All') {
            $query->where('difficulty', $request->difficulty);
        }

        $quizzes = $query->get()->map(function ($quiz) {
            $attempts = QuizAttempt::where('user_id', Auth::id())->where('quiz_id', $quiz->id)->count();
            $limit = 3;
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

    /**
     * 2. Take Quiz: Prepare JSON
     */
    public function show($id)
    {
        $quizModel = Quiz::findOrFail($id);
        $attempts = QuizAttempt::where('user_id', Auth::id())->where('quiz_id', $id)->count();

        if ($attempts >= 3) {
            return redirect()->route('quizzes.index')->with('error', "Maximum attempts reached.");
        }

        $rawQuestions = $quizModel->content ?? [];
        $formattedQuestions = collect($rawQuestions)->map(function($q) {
            $correctOptionId = null;
            if (isset($q['options']) && is_array($q['options'])) {
                foreach ($q['options'] as $index => $opt) {
                    if (isset($opt['is_correct']) && $opt['is_correct'] === true) {
                        // Use text or id specifically
                        $correctOptionId = $opt['text'] ?? $opt['id'] ?? $index;
                        break;
                    }
                }
            }

            return [
                'id' => $q['id'] ?? rand(1000, 9999),
                'text' => $q['question'] ?? $q['text'] ?? 'No text',
                'explanation' => $q['explanation'] ?? '',
                'correct_option_id' => $correctOptionId,
                'options' => collect($q['options'] ?? [])->map(fn($opt, $idx) => [
                    'id' => $opt['text'] ?? $opt['id'] ?? $idx,
                    'text' => $opt['text'] ?? '',
                ]),
            ];
        });

        return Inertia::render('Quiz/Show', [
            'quiz' => [
                'id' => $quizModel->id,
                'title' => $quizModel->title,
                'time_limit' => $quizModel->duration,
                'questions' => $formattedQuestions
            ]
        ]);
    }

    /**
     * 3. SUBMIT: Secure Score & Strictly Enforce Limits
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|integer',
            'answers' => 'required|array',
        ]);

        $quiz = Quiz::findOrFail($validated['quiz_id']);

        // FIX: STRICT ATTEMPT LIMIT CHECK inside Store method
        $attemptsCount = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $quiz->id)
            ->count();

        if ($attemptsCount >= 3) {
            return response()->json(['error' => 'Max attempts reached.'], 403);
        }

        // FIX: SECURE SERVER-SIDE SCORE CALCULATION
        $content = $quiz->content;
        $score = 0;
        $totalQuestions = is_array($content) ? count($content) : 0;

        foreach ($content as $q) {
            $qId = $q['id'];
            $correctValue = null;

            // Find correct value from the original DB content
            foreach ($q['options'] as $opt) {
                if (isset($opt['is_correct']) && $opt['is_correct'] === true) {
                    $correctValue = $opt['text'];
                    break;
                }
            }

            // Compare with user submission (using loose equality for safety)
            if (isset($validated['answers'][$qId]) && $validated['answers'][$qId] == $correctValue) {
                $score++;
            }
        }

        QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $totalQuestions,
        ]);

        return back()->with('success', 'Evaluation submitted successfully.');
    }

    /**
     * 4. HISTORY: Accurate Statistics Calculation
     */
    public function history($id)
    {
        $quiz = Quiz::findOrFail($id);

        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->latest()
            ->get()
            ->map(function ($attempt) {
                // Ensure floating point math for percentage
                $pct = $attempt->total_questions > 0
                    ? ($attempt->score / $attempt->total_questions) * 100
                    : 0;

                return [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'total' => $attempt->total_questions,
                    'date' => $attempt->created_at->format('d M Y, h:i A'),
                    'percentage' => round($pct),
                ];
            });

        // FIX: Accurate Collection-based Stats
        $count = $attempts->count();
        $average = $count > 0 ? round($attempts->avg('percentage')) : 0;
        $best = $count > 0 ? $attempts->max('percentage') : 0;

        return Inertia::render('Quiz/History', [
            'quizTitle' => $quiz->title,
            'quizId' => $quiz->id,
            'attempts' => $attempts,
            'stats' => [
                'attempts' => $count,
                'average' => $average,
                'best' => $best,
            ]
        ]);
    }
}
