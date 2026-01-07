<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TeacherQuizController extends Controller
{
    // 1. DASHBOARD
    public function index()
    {
        $quizzes = Quiz::withCount('attempts')->latest()->get();
        return Inertia::render('Teacher/Quiz/Index', ['quizzes' => $quizzes]);
    }

    // 2. CREATE
    public function create()
    {
        return Inertia::render('Teacher/Quiz/Create');
    }

    // 3. STORE
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'duration' => 'required|integer',
            'difficulty' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ], [
            'title.required' => 'This field is required.',
            'questions.*.text.required' => 'This field is required.',
            'questions.*.options.*.text.required' => 'This field is required.',
            'duration.required' => 'This field is required.',
            'difficulty.required' => 'This field is required.',
            'questions.min' => 'You must add at least one question.',
        ]);

        $jsonContent = collect($data['questions'])->map(function ($q, $index) {
            return [
                'id' => $index + 1,
                'text' => $q['text'],
                'explanation' => $q['explanation'],
                'options' => collect($q['options'])->map(function($opt, $optIndex) {
                    return [
                        'id' => $optIndex + 1,
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct']
                    ];
                })->toArray()
            ];
        })->toArray();

        Quiz::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'duration' => $data['duration'],
            'difficulty' => $data['difficulty'],
            'content' => $jsonContent,
        ]);

        // Updated Message
        return redirect()->route('teacher.quiz.index')->with('success', 'Quiz created successfully.');
    }

    // 4. DELETE
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete(); 
        
        // Updated Message
        return redirect()->back()->with('success', 'Quiz deleted successfully.');
    }

    // 5. RESULTS
    public function results($id)
    {
        $quiz = Quiz::findOrFail($id);
        
        $attempts = QuizAttempt::where('quiz_id', $id)
            ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
            ->select('quiz_attempts.*', 'users.name as user_name', 'users.email')
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedAttempts = $attempts->groupBy('user_id')->map(function ($userAttempts, $userId) {
            $grantedCount = $userAttempts->filter(fn($att) => collect($att->answers ?? [])->contains('revoked_attempt', true))->count();
            $baseLimit = 3;
            $currentLimit = $baseLimit + $grantedCount;
            $totalAttempts = $userAttempts->count();

            return [
                'user_id' => $userId,
                'user_name' => $userAttempts->first()->user_name,
                'email' => $userAttempts->first()->email,
                'attempts_count' => $totalAttempts, 
                'max_allowed' => $currentLimit, 
                'is_locked' => $totalAttempts >= $currentLimit, 
                'history' => $userAttempts->map(function($att) {
                    return [
                        'id' => $att->id,
                        'score' => $att->score,
                        'total_questions' => $att->total_questions,
                        'created_at' => $att->created_at,
                        'is_revoked' => collect($att->answers ?? [])->contains('revoked_attempt', true),
                    ];
                })
            ];
        })->values();

        return Inertia::render('Teacher/Quiz/Results', [
            'quiz' => $quiz,
            'studentData' => $groupedAttempts
        ]);
    }

    // 6. EDIT
    public function edit($id)
    {
        $quiz = Quiz::findOrFail($id);
        $questions = $quiz->content ?? []; 
        return Inertia::render('Teacher/Quiz/Edit', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'duration' => $quiz->duration,
                'difficulty' => $quiz->difficulty,
                'questions' => is_array($questions) ? $questions : []
            ]
        ]);
    }

    // 7. UPDATE
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'duration' => 'required|integer',
            'difficulty' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ], [
            'title.required' => 'This field is required.',
            'questions.*.text.required' => 'This field is required.',
            'questions.*.options.*.text.required' => 'This field is required.',
            'duration.required' => 'This field is required.',
            'difficulty.required' => 'This field is required.',
            'questions.min' => 'You must add at least one question.',
        ]);

        $jsonContent = collect($data['questions'])->map(function ($q, $index) {
            return [
                'id' => $index + 1,
                'text' => $q['text'],
                'explanation' => $q['explanation'],
                'options' => collect($q['options'])->map(function($opt, $optIndex) {
                    return [
                        'id' => $optIndex + 1,
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct']
                    ];
                })->toArray()
            ];
        })->toArray();

        $quiz->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'duration' => $data['duration'],
            'difficulty' => $data['difficulty'],
            'content' => $jsonContent,
        ]);

        // Matches your screenshot text exactly
        return redirect()->route('teacher.quiz.index')->with('success', 'Changes saved successfully.');
    }

    // 8. GRANT ATTEMPT
    public function grantAttempt($quizId, $userId)
    {
        $qId = is_object($quizId) ? $quizId->id : $quizId;
        $uId = is_object($userId) ? $userId->id : $userId;

        $attempt = QuizAttempt::where('quiz_id', $qId)->where('user_id', $uId)->latest()->first();

        if ($attempt) {
            $answers = $attempt->answers ?? [];
            if (!collect($answers)->contains('revoked_attempt', true)) {
                $answers[] = ['revoked_attempt' => true];
                $attempt->answers = $answers;
                $attempt->save();
                return redirect()->back()->with('success', 'Attempt granted!');
            }
            return redirect()->back()->with('warning', 'Already granted.');
        }
        return redirect()->back()->with('error', 'No attempts to grant.');
    }

    // 9. SHOW ATTEMPT
    public function showAttempt($attemptId)
    {
        $attempt = QuizAttempt::with(['user', 'quiz'])->findOrFail($attemptId);
        $quizContent = $attempt->quiz->content ?? []; 
        $studentAnswers = $attempt->answers ?? []; 

        $detailedQuestions = collect($quizContent)->map(function ($q, $qIndex) use ($studentAnswers) {
            if (!is_array($q)) return null;
            $qId = $q['id'] ?? ($qIndex + 1);
            
            $options = collect($q['options'] ?? [])->map(function($o, $optIndex) {
                return [
                    'id' => $o['text'] ?? $o['id'] ?? $optIndex,
                    'text' => $o['text'] ?? 'Option text missing',
                    'is_correct' => (bool)($o['is_correct'] ?? false)
                ];
            })->toArray();

            $studentSelection = null;
            if (is_array($studentAnswers)) {
                foreach ($studentAnswers as $ans) {
                    if (isset($ans['question_id']) && $ans['question_id'] == $qId) {
                        $studentSelection = $ans['option_id'] ?? null;
                        if (!$studentSelection && isset($ans['user_text'])) {
                            foreach ($options as $opt) {
                                if (trim((string)$opt['text']) == trim((string)$ans['user_text'])) {
                                    $studentSelection = $opt['id'];
                                    break;
                                }
                            }
                        }
                        break;
                    }
                }
            }

            return [
                'id' => $qId,
                'text' => $q['question'] ?? $q['text'] ?? 'Error',
                'explanation' => $q['explanation'] ?? '',
                'student_selected_option_id' => $studentSelection,
                'is_correct' => collect($options)->where('id', $studentSelection)->where('is_correct', true)->isNotEmpty(),
                'options' => $options
            ];
        })->filter()->values();

        return Inertia::render('Teacher/Quiz/ReviewAttempt', [
            'attempt' => [
                'id' => $attempt->id,
                'score' => $attempt->score,
                'total' => count($quizContent),
                'percentage' => count($quizContent) > 0 ? round(($attempt->score / count($quizContent)) * 100) : 0,
                'date' => $attempt->created_at->format('d M Y, h:i A'),
                'student_name' => $attempt->user->name ?? 'Student',
            ],
            'quiz_title' => $attempt->quiz->title ?? 'Quiz',
            'questions' => $detailedQuestions
        ]);
    }

    public function destroyAttempt($id) {
        QuizAttempt::findOrFail($id)->delete();
        return back()->with('success', 'Deleted successfully.');
    }
}