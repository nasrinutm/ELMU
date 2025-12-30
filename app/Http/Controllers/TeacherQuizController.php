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
        // Get quizzes with count of how many students took them
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

    // 3. STORE: Save new quiz (Questions saved as JSON)
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'duration' => 'required|integer', // in seconds
            'difficulty' => 'required|string',
            // Validate the JSON structure for questions
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        // Transform frontend data into simple JSON format
        $jsonContent = collect($data['questions'])->map(function ($q, $index) {
            return [
                'id' => $index + 1, // Assign simple IDs
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

        // Create the Quiz
        Quiz::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'duration' => $data['duration'],
            'difficulty' => $data['difficulty'],
            'content' => $jsonContent, // <--- SAVED AS JSON
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

    // 5. STUDENT PERFORMANCE: See results
    public function results($id)
    {
        $quiz = Quiz::findOrFail($id);
        
        // Fetch all attempts for this quiz with User details
        $attempts = QuizAttempt::where('quiz_id', $id)
            ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
            ->select('quiz_attempts.*', 'users.name as user_name', 'users.email')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group attempts by User
        $groupedAttempts = $attempts->groupBy('user_id')->map(function ($userAttempts, $userId) {
            
            $maxAllowed = 3; // Hardcoded limit for simple system
            
            return [
                'user_id' => $userId,
                'user_name' => $userAttempts->first()->user_name,
                'email' => $userAttempts->first()->email,
                'attempts_count' => $userAttempts->count(),
                'max_allowed' => $maxAllowed,
                'is_locked' => $userAttempts->count() >= $maxAllowed,
                'history' => $userAttempts
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

        // Transform JSON back to format expected by Frontend Form
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

        // Transform data to JSON
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

        // Update the Quiz
        $quiz->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'duration' => $data['duration'],
            'difficulty' => $data['difficulty'],
            'content' => $jsonContent, // <--- UPDATE JSON
        ]);

        return redirect()->route('teacher.quiz.index')->with('success', 'Quiz Updated Successfully');
    }

    // 8. GRANT ATTEMPT: Reset student score (Re-added this function!)
    public function grantAttempt($quizId, $userId)
    {
        // Because of Route Model Binding, these might be objects. Get IDs safely.
        $qId = is_object($quizId) ? $quizId->id : $quizId;
        $uId = is_object($userId) ? $userId->id : $userId;

        // Find the student's latest attempt for this quiz
        $attempt = QuizAttempt::where('quiz_id', $qId)
            ->where('user_id', $uId)
            ->latest() // Get the newest one
            ->first();

        if ($attempt) {
            $attempt->delete(); // Delete it to lower their attempt count
            return redirect()->back()->with('success', 'Latest attempt removed. Student can retake now.');
        }

        return redirect()->back()->with('error', 'Student has no attempts to reset.');
    }
}