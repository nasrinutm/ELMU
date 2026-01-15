<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuizController extends Controller
{
    // --- NEW HELPER: Calculates Limits Dynamically ---
    private function calculateLimits($quizId, $userId)
    {
        $attempts = QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->get();

        // Count how many times teacher clicked "Grant Attempt"
        $grantedCount = $attempts->filter(function ($attempt) {
            $answers = $attempt->answers ?? [];
            return collect($answers)->contains('revoked_attempt', true);
        })->count();

        $totalTaken = $attempts->count();
        $baseLimit = 3;
        $maxLimit = $baseLimit + $grantedCount; // e.g. 3 + 1 = 4

        return [
            'taken' => $totalTaken,
            'max' => $maxLimit,
            'remaining' => max(0, $maxLimit - $totalTaken),
            'is_locked' => $totalTaken >= $maxLimit
        ];
    }

    // 1. Fetch Quizzes
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
            
            // Use Helper Logic
            $status = $this->calculateLimits($quiz->id, Auth::id());
            
            $questionCount = is_array($quiz->content) ? count($quiz->content) : 0;

            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'questions_count' => $questionCount,
                'time_limit' => floor($quiz->duration / 60) . ' mins',
                'difficulty' => $quiz->difficulty,
                
                // Dynamic Data
                'attempts_taken' => $status['taken'],
                'attempts_max' => $status['max'],
                'attempts_left' => $status['remaining'],
                'is_locked' => $status['is_locked'],
            ];
        })->values();

        return Inertia::render('Quiz/Index', [
            'quizzes' => $quizzes,
            'filters' => $request->only(['search', 'difficulty']),
        ]);
    }

    // 2. Take Quiz
    public function show($id)
    {
        $quizModel = Quiz::findOrFail($id);
        
        // Check dynamic lock
        $status = $this->calculateLimits($id, Auth::id());

        if ($status['is_locked']) {
            return redirect()->route('quizzes.index')->with('error', "Maximum attempts reached.");
        }

        $rawQuestions = $quizModel->content ?? [];
        
        $formattedQuestions = collect($rawQuestions)->map(function($q, $i) {
            $correctOptionId = null;
            $qId = $q['id'] ?? ($i + 1);

            if (isset($q['options']) && is_array($q['options'])) {
                foreach ($q['options'] as $index => $opt) {
                    if (isset($opt['is_correct']) && $opt['is_correct'] === true) {
                        $correctOptionId = $opt['text'] ?? $opt['id'] ?? $index;
                        break;
                    }
                }
            }

            return [
                'id' => $qId,
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

    // 3. Submit Quiz
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|integer',
            'answers' => 'required|array',
        ]);

        $quiz = Quiz::findOrFail($validated['quiz_id']);
        
        // Double check lock before saving
        $status = $this->calculateLimits($quiz->id, Auth::id());

        if ($status['is_locked']) {
            return response()->json(['error' => 'Max attempts reached.'], 403);
        }

        $content = $quiz->content ?? [];
        $score = 0;
        $totalQuestions = count($content);
        $savedAnswers = [];

        foreach ($content as $i => $q) {
            $qId = $q['id'] ?? ($i + 1);
            $correctValue = null;
            
            if (isset($q['options']) && is_array($q['options'])) {
                foreach ($q['options'] as $opt) {
                    if (isset($opt['is_correct']) && $opt['is_correct'] === true) {
                        $correctValue = $opt['text'];
                        break;
                    }
                }
            }

            $userAnswer = $validated['answers'][$qId] ?? null;

            if (trim((string)$userAnswer) == trim((string)$correctValue)) {
                $score++;
            }

            $selectedOptionId = null;
            if (isset($q['options']) && is_array($q['options'])) {
                foreach ($q['options'] as $idx => $opt) {
                    if (trim((string)($opt['text'] ?? '')) == trim((string)$userAnswer)) {
                        $selectedOptionId = $opt['text'] ?? $opt['id'] ?? $idx;
                        break;
                    }
                }
            }

            $savedAnswers[] = [
                'question_id' => $qId,
                'option_id' => $selectedOptionId,
                'user_text' => $userAnswer,
                'is_correct' => (trim((string)$userAnswer) == trim((string)$correctValue))
            ];
        }

        QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'answers' => $savedAnswers,
        ]);

        return back()->with('success', 'Evaluation submitted successfully.');
    }

    // 4. History
    public function history($id)
    {
        $quiz = Quiz::findOrFail($id);

        $attempts = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->latest()
            ->get()
            ->map(function ($attempt) {
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