<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TeacherQuizController extends Controller
{
    // 1. DASHBOARD: List all quizzes
    public function index()
    {
        $quizzes = Quiz::withCount('attempts')->latest()->get();

        return Inertia::render('Teacher/Quiz/Index', [
            'quizzes' => $quizzes
        ]);
    }

    // 2. CREATE: Show form
    public function create()
    {
        return Inertia::render('Teacher/Quiz/Create');
    }

    // 3. STORE: Save new quiz
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

        return redirect()->route('teacher.quiz.index')->with('success', 'Quiz Created Successfully');
    }

    // 4. DELETE: Remove a quiz
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete(); 

        return redirect()->back()->with('success', 'Quiz deleted');
    }

  // 5. STUDENT PERFORMANCE: Dynamic Limit (3/4, 4/4 logic)
    public function results($id)
    {
        $quiz = Quiz::findOrFail($id);
        
        $attempts = QuizAttempt::where('quiz_id', $id)
            ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
            ->select('quiz_attempts.*', 'users.name as user_name', 'users.email')
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedAttempts = $attempts->groupBy('user_id')->map(function ($userAttempts, $userId) {
            
            // 1. Count how many attempts were granted (marked as revoked)
            $grantedCount = $userAttempts->filter(function($att) {
                $answers = $att->answers ?? []; 
                return collect($answers)->contains('revoked_attempt', true);
            })->count();

            // 2. Calculate Dynamic Limit (Base 3 + Granted Extras)
            $baseLimit = 3;
            $currentLimit = $baseLimit + $grantedCount;
            
            // 3. Count Total Attempts (Including the granted ones)
            $totalAttempts = $userAttempts->count();

            return [
                'user_id' => $userId,
                'user_name' => $userAttempts->first()->user_name,
                'email' => $userAttempts->first()->email,
                
                // Show "3" (attempts taken)
                'attempts_count' => $totalAttempts, 
                
                // Show "4" (if 1 granted)
                'max_allowed' => $currentLimit, 
                
                // Locked if 3 >= 4 (False) or 4 >= 4 (True)
                'is_locked' => $totalAttempts >= $currentLimit, 
                
                'history' => $userAttempts->map(function($att) {
                    $isRevoked = collect($att->answers ?? [])->contains('revoked_attempt', true);
                    return [
                        'id' => $att->id,
                        'score' => $att->score,
                        'total_questions' => $att->total_questions,
                        'created_at' => $att->created_at,
                        'is_revoked' => $isRevoked,
                    ];
                })
            ];
        })->values();

        return Inertia::render('Teacher/Quiz/Results', [
            'quiz' => $quiz,
            'studentData' => $groupedAttempts
        ]);
    }
    // 6. EDIT: Show edit form
    public function edit($id)
    {
        $quiz = Quiz::findOrFail($id);
        $rawQuestions = $quiz->content ?? []; 
        $questions = is_array($rawQuestions) ? $rawQuestions : [];

        return Inertia::render('Teacher/Quiz/Edit', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'duration' => $quiz->duration,
                'difficulty' => $quiz->difficulty,
                'questions' => $questions
            ]
        ]);
    }

    // 7. UPDATE: Save changes
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

        return redirect()->route('teacher.quiz.index')->with('success', 'Quiz Updated Successfully');
    }

 // 8. GRANT ATTEMPT: (Smart Update: Keeps history, but allows retake)
    public function grantAttempt($quizId, $userId)
    {
        $qId = is_object($quizId) ? $quizId->id : $quizId;
        $uId = is_object($userId) ? $userId->id : $userId;

        // Find the LATEST attempt
        $attempt = QuizAttempt::where('quiz_id', $qId)
            ->where('user_id', $uId)
            ->latest()
            ->first();

        if ($attempt) {
            // Get existing answers
            $answers = $attempt->answers ?? [];

            // Check if we already granted this one (avoid double flagging)
            $isAlreadyRevoked = collect($answers)->contains('revoked_attempt', true);

            if (!$isAlreadyRevoked) {
                // Add a special hidden flag to the answers list
                $answers[] = ['revoked_attempt' => true];
                
                // Save it back
                $attempt->answers = $answers;
                $attempt->save();

                return redirect()->back()->with('success', 'Attempt granted! The student can now retake the quiz.');
            }
            
            return redirect()->back()->with('warning', 'This attempt was already granted.');
        }

        return redirect()->back()->with('error', 'Student has no attempts to grant.');
    }

    
    

   // --- NEW: SHOW ATTEMPT (Robust Matching Fix) ---
    public function showAttempt($attemptId)
    {
        $attempt = QuizAttempt::with(['user', 'quiz'])->findOrFail($attemptId);

        $quizContent = $attempt->quiz->content ?? []; 
        $studentAnswers = $attempt->answers ?? []; 

        $detailedQuestions = collect($quizContent)->map(function ($q, $qIndex) use ($studentAnswers) {
            
            if (!is_array($q)) return null;

            // Generate stable Question ID
            $qId = $q['id'] ?? ($qIndex + 1);

            // 1. Prepare Options with IDs
            $options = $q['options'] ?? [];
            $mappedOptions = collect($options)->map(function($o, $optIndex) {
                return [
                    // Priority: Text -> ID -> Index
                    // We use TEXT as the ID because that is what your DB seems to rely on
                    'id' => $o['text'] ?? $o['id'] ?? $optIndex,
                    'text' => $o['text'] ?? 'Option text missing',
                    'is_correct' => (bool)($o['is_correct'] ?? false)
                ];
            })->toArray();

            // 2. Find Student's Answer
            $studentSelection = null;
            if (is_array($studentAnswers)) {
                foreach ($studentAnswers as $ans) {
                    // Match Question ID
                    if (isset($ans['question_id']) && $ans['question_id'] == $qId) {
                        
                        // TRY 1: Use the saved option_id
                        $studentSelection = $ans['option_id'] ?? null;

                        // TRY 2: If ID is null, try to match by User Text
                        if (!$studentSelection && isset($ans['user_text'])) {
                            foreach ($mappedOptions as $opt) {
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
                'text' => $q['question'] ?? $q['text'] ?? 'Error: Question text missing',
                'explanation' => $q['explanation'] ?? '',
                
                'student_selected_option_id' => $studentSelection,
                
                // Check if correct
                'is_correct' => collect($mappedOptions)
                                ->where('id', $studentSelection)
                                ->where('is_correct', true)
                                ->isNotEmpty(),
                                
                'options' => $mappedOptions
            ];
        })->filter()->values();

        return Inertia::render('Teacher/Quiz/ReviewAttempt', [
            'attempt' => [
                'id' => $attempt->id,
                'score' => $attempt->score ?? 0,
                'total' => count($quizContent),
                'percentage' => count($quizContent) > 0 ? round(($attempt->score / count($quizContent)) * 100) : 0,
                'date' => $attempt->created_at->format('d M Y, h:i A'),
                'student_name' => $attempt->user->name ?? 'Unknown Student',
            ],
            'quiz_title' => $attempt->quiz->title ?? 'Untitled Quiz',
            'questions' => $detailedQuestions
        ]);
    }

    // --- NEW: DESTROY ATTEMPT (Trash Button) ---
    public function destroyAttempt($id)
    {
        $attempt = QuizAttempt::findOrFail($id);
        $attempt->delete();
        return back()->with('success', 'Attempt record deleted successfully.');
    }
}