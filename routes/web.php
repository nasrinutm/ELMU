<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotUploadController;
use App\Http\Controllers\ForumController;
use Gemini\Laravel\Facades\Gemini;
use App\Models\User;
use App\Models\Material;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\QuizController; 
use App\Http\Controllers\TeacherQuizController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ReportController;

// --- PUBLIC ROUTES ---

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/test-models', function () {
    try {
        $response = Gemini::models()->list();
        return collect($response->models)->map(fn ($model) => [
            'name' => $model->name,
            'display_name' => $model->displayName,
            'capabilities' => $model->supportedGenerationMethods
        ]);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Setup AI Store
Route::get('/setup-ai', [ChatbotController::class, 'setupStore']);

// --- AUTHENTICATED ROUTES ---
Route::middleware(['auth', 'verified'])->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // If the user is an Admin, redirect to User Management
        if ($user && $user->hasRole('admin')) {
            return redirect()->route('users.index'); 
        }

        // Standard Dashboard Logic
        $stats = [
            'users' => User::count(),
            'materials' => Material::count(),
            'my_materials' => Material::where('user_id', Auth::id())->count(),
        ];

        $recentMaterials = Material::with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentMaterials' => $recentMaterials
        ]);
    })->name('dashboard');

    // 2. CHATBOT
    Route::post('/chat/send', [ChatbotController::class, 'send'])->name('chat.send');

    // 3. ADMIN ROUTES
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/add', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Chatbot Knowledge Base
        Route::get('/chatbot', [ChatbotUploadController::class, 'index'])->name('chatbot.details');
        Route::get('/chatbot/upload', [ChatbotUploadController::class, 'create'])->name('upload.create');
        Route::post('/chatbot/upload', [ChatbotUploadController::class, 'store'])->name('upload.store');
        Route::delete('/chatbot/{fileName}', [ChatbotUploadController::class, 'destroy'])
            ->where('fileName', '.*')
            ->name('upload.delete');
        Route::get('/chatbot/prompt', [ChatbotController::class, 'editPrompt'])->name('chatbot.prompt.edit');
        Route::put('/chatbot/prompt', [ChatbotController::class, 'updatePrompt'])->name('chatbot.prompt.update');
    });

    // 4. FORUM ROUTES
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/create', [ForumController::class, 'create'])->name('forum.create');
    Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
    Route::get('/forum/{post}', [ForumController::class, 'show'])->name('forum.show');
    Route::get('/forum/{post}/edit', [ForumController::class, 'edit'])->name('forum.edit');
    Route::put('/forum/{post}', [ForumController::class, 'update'])->name('forum.update');
    Route::delete('/forum/{post}', [ForumController::class, 'destroy'])->name('forum.destroy');
    
    // Forum Replies
    Route::post('/replies', [ForumController::class, 'storeReply'])->name('replies.store');
    Route::put('/replies/{reply}', [ForumController::class, 'updateReply'])->name('replies.update');
    Route::delete('/replies/{reply}', [ForumController::class, 'destroyReply'])->name('replies.destroy');

    // 5. MATERIALS ROUTES
    // Shared (View/Download)
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    // Teacher Only (CRUD)
    Route::middleware(['role:teacher'])->prefix('materials')->group(function () {
        Route::get('/create', [MaterialController::class, 'create'])->name('materials.create');
        Route::post('/', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    });

    // 6. ACTIVITY ROUTES
    Route::resource('activities', ActivityController::class);
    Route::get('/activities/{activity}/download', [ActivityController::class, 'download'])->name('activities.download');
    Route::get('/activities/{activity}/play', [ActivityController::class, 'play'])->name('activities.play');
    Route::post('/activities/{activity}/score', [ActivityController::class, 'submitScore'])->name('activities.score');
     Route::post('/activities/{activity}/submit', [\App\Http\Controllers\ActivityController::class, 'submit'])
        ->name('activities.submit');
        Route::delete('/activities/{activity}/unsubmit', [ActivityController::class, 'unsubmit'])->name('activities.unsubmit');

    Route::get('/activities/{activity}/submission/download', [\App\Http\Controllers\ActivityController::class, 'downloadSubmission'])
    ->name('activities.submission.download');

// Route for teachers to delete a specific submission
// Update this in web.php
Route::get('/activities/download-submission/{submission}', [ActivityController::class, 'downloadSubmission'])
    ->name('activities.downloadSubmission');
    Route::delete('/submissions/{submission}', [ActivityController::class, 'destroySubmission'])
    ->name('submissions.destroy')
    ->middleware('role:teacher');
    Route::middleware(['auth', 'verified', 'role:teacher|admin'])->group(function () {
    // Route to delete a student's submission record and file
    Route::delete('/submissions/{submission}', [ActivityController::class, 'destroySubmission'])
        ->name('submissions.destroy');});

    // 7. STUDENT QUIZ ROUTES (From branch-ASHYIL)
    Route::resource('quizzes', QuizController::class);
    Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
    Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('/quiz/submit', [QuizController::class, 'store'])->name('quiz.submit');
    Route::get('/quiz/{id}/history', [QuizController::class, 'history'])->name('quiz.history');

    Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    
    Route::post('/quizzes/{quiz}/{user}/grant', [TeacherQuizController::class, 'grantAttempt'])->name('attempt.grant');

    // Quiz Management
    Route::get('/quizzes', [TeacherQuizController::class, 'index'])->name('quiz.index');
    Route::get('/quizzes/create', [TeacherQuizController::class, 'create'])->name('quiz.create');
    Route::post('/quizzes', [TeacherQuizController::class, 'store'])->name('quiz.store');
    Route::delete('/quizzes/{id}', [TeacherQuizController::class, 'destroy'])->name('quiz.destroy');

    // Performance & Unlocking
    Route::get('/quizzes/{id}/results', [TeacherQuizController::class, 'results'])->name('quiz.results');
    Route::delete('/attempts/{id}/unlock', [TeacherQuizController::class, 'unlockAttempt'])->name('attempt.unlock');

    // Edit Quiz
    Route::get('/quizzes/{id}/edit', [TeacherQuizController::class, 'edit'])->name('quiz.edit');
    Route::put('/quizzes/{id}', [TeacherQuizController::class, 'update'])->name('quiz.update');
    });

    // 8. STUDENT ROUTES (List, Progress, & Manual Activities)
    // Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    // Route::get('/students/{user}', [StudentController::class, 'show'])->name('students.show');

    // // NEW: Manual Activity Management for Students
    // Route::post('/students/{user}/activities', [StudentController::class, 'storeActivity'])->name('students.activities.store');
    // Route::put('/students/{user}/activities/{activity}', [StudentController::class, 'updateActivity'])->name('students.activities.update');
    // Route::delete('/students/{user}/activities/{activity}', [StudentController::class, 'destroyActivity'])->name('students.activities.destroy');

    // List Students
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    
    // View Specific Student
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    
    // Manual Activity CRUD
    Route::post('/students/{student}/activities', [StudentController::class, 'storeActivity'])->name('students.activities.store');
    Route::put('/students/{student}/activities/{activity}', [StudentController::class, 'updateActivity'])->name('students.activities.update');
    Route::delete('/students/{student}/activities/{activity}', [StudentController::class, 'destroyActivity'])->name('students.activities.destroy');

    // 9. REPORT ROUTES (UC0014: Generate Reports)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Detailed Performance View Route (When clicking a student name)
    Route::get('/reports/student/{student}', [ReportController::class, 'showStudentPerformance'])
        ->name('reports.student.detail');

    // Only Teachers and Admins can create or store reports
    Route::middleware(['role:teacher|admin'])->group(function () {
        Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

        // Placeholders for future View/Edit logic
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
        Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');

        // Remark Logic (Moved Inside Teacher Middleware for Security)
        Route::post('/reports/remark', [ReportController::class, 'saveRemark'])->name('reports.remark.save');
        Route::delete('/reports/remark/{report}', [ReportController::class, 'deleteRemark'])->name('reports.remark.delete');
    });

    

}); // End Auth Middleware

// --- REQUIRED IMPORTS ---
require __DIR__.'/settings.php';