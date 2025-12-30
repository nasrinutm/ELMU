<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TeacherQuizController extends Controller
{
    // 1. DASHBOARD: List all quizzes with stats
    public function index()
    {
        $quizzes = Quiz::withCount('attempts')->latest()->get();

        return Inertia::render('Teacher/Quiz/Index', [
            'quizzes' => $quizzes
        ]);
    }

    // 2. CREATE: Show the form to add a new quiz
    public function create()
    {
        return Inertia::render('Teacher/Quiz/Create');
    }

    // 3. STORE: Save a new quiz + questions + options to Database
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'duration' => 'required|integer', // in seconds
            'difficulty' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        // Use Transaction to ensure everything saves or nothing saves
        DB::transaction(function () use ($data) {
            $quiz = Quiz::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'duration' => $data['duration'],
                'difficulty' => $data['difficulty'],
                'content' => $data['content'],
            ]);

            foreach ($data['questions'] as $qData) {
                $question = $quiz->questions()->create([
                    'text' => $qData['text'],
                    'explanation' => $qData['explanation'] ?? null,
                ]);

                foreach ($qData['options'] as $optData) {
                    $question->options()->create([
                        'text' => $optData['text'],
                        'is_correct' => $optData['is_correct'],
                    ]);
                }
            }
        });

        return redirect()->route('teacher.quiz.index')->with('success', 'Quiz Created Successfully');
    }

    // 4. DELETE: Remove a quiz
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete(); // This cascades and deletes questions/options too

        return redirect()->back()->with('success', 'Quiz deleted');
    }

    // 5. STUDENT PERFORMANCE: See who passed/failed & Manage Attempts
    public function results($id)
    {
        $quiz = Quiz::findOrFail($id);

        // 1. Fetch all attempts for this quiz
        $attempts = QuizAttempt::where('quiz_id', $id)
            ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
            ->select('quiz_attempts.*', 'users.name as user_name', 'users.email')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Fetch Extra Access Data (Who has been given extra tries?)
        $accesses = QuizAccess::where('quiz_id', $id)->get()->keyBy('user_id');

        // 3. Group attempts by User
        $groupedAttempts = $attempts->groupBy('user_id')->map(function ($userAttempts, $userId) use ($accesses, $quiz) {

            // Check if teacher granted extra attempts
            $extra = isset($accesses[$userId]) ? $accesses[$userId]->extra_attempts : 0;

            // Default is 3 + whatever extra was granted
            $maxAllowed = 3 + $extra;

            return [
                'user_id' => $userId,
                'user_name' => $userAttempts->first()->user_name,
                'email' => $userAttempts->first()->email,
                'attempts_count' => $userAttempts->count(),
                'max_allowed' => $maxAllowed,
                'extra_granted' => $extra,
                'is_locked' => $userAttempts->count() >= $maxAllowed, // Logic: Locked if used all attempts
                'history' => $userAttempts // Keep full history
            ];
        })->values();

        return Inertia::render('Teacher/Quiz/Results', [
            'quiz' => $quiz,
            'studentData' => $groupedAttempts // Sending grouped data instead of flat list
        ]);
    }

    // 6. GRANT ATTEMPT: Give a student +1 attempt without deleting history
    public function grantAttempt(Request $request, $quizId, $userId)
    {
        // Fix: Ensure we have IDs, not Model Objects (handles Route Model Binding)
        $qId = is_object($quizId) ? $quizId->id : $quizId;
        $uId = is_object($userId) ? $userId->id : $userId;

        // Find or Create the access record for this user/quiz combo
        $access = QuizAccess::firstOrCreate(
            ['user_id' => $uId, 'quiz_id' => $qId],
            ['extra_attempts' => 0]
        );

        // Add 1 extra attempt
        $access->increment('extra_attempts');

        return redirect()->back()->with('success', 'Extra attempt granted. Limit increased.');
    }

    // 7. EDIT: Show edit form
    public function edit($id)
    {
        // Load quiz with all questions and options so we can populate the form
        $quiz = Quiz::with('questions.options')->findOrFail($id);

        return Inertia::render('Teacher/Quiz/Edit', [
            'quiz' => $quiz
        ]);
    }

    // 8. UPDATE: Save changes
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

        DB::transaction(function () use ($quiz, $data) {
            // 1. Update Quiz Details
            $quiz->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'duration' => $data['duration'],
                'difficulty' => $data['difficulty'],
            ]);

            // 2. Sync Questions (Simplest Strategy: Wipe and Recreate)
            $quiz->questions()->delete();

            foreach ($data['questions'] as $qData) {
                $question = $quiz->questions()->create([
                    'text' => $qData['text'],
                    'explanation' => $qData['explanation'] ?? null,
                ]);

                foreach ($qData['options'] as $optData) {
                    $question->options()->create([
                        'text' => $optData['text'],
                        'is_correct' => $optData['is_correct'],
                    ]);
                }
            }
        });

        return redirect()->route('teacher.quiz.index')->with('success', 'Quiz Updated Successfully');
    }
}
