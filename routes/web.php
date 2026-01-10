<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Gemini\Laravel\Facades\Gemini;

// --- CONTROLLER IMPORTS ---
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotUploadController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TeacherQuizController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// AI Capability Test Route
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

Route::get('/setup-ai', [ChatbotController::class, 'setupStore']);

Route::middleware(['auth', 'verified'])->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', [StudentController::class, 'dashboardStats'])->name('dashboard');

    // 2. CHATBOT INTERACTION
    Route::post('/chat/send', [ChatbotController::class, 'send'])->name('chat.send');

    // 3. ADMIN & CHATBOT MANAGEMENT (Separate logic for AI Training)
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/add', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Chatbot Knowledge Base (Friend's Logic)
        Route::get('/chatbot', [ChatbotUploadController::class, 'index'])->name('chatbot.details');
        Route::get('/chatbot/upload', [ChatbotUploadController::class, 'create'])->name('upload.create');
        Route::post('/chatbot/upload', [ChatbotUploadController::class, 'store'])->name('upload.store');
        Route::delete('/chatbot/{fileName}', [ChatbotUploadController::class, 'destroy'])
            ->where('fileName', '.*')
            ->name('upload.delete');
        Route::put('/chatbot/upload/{geminiDocumentName}', [ChatbotUploadController::class, 'update'])
            ->name('upload.update')
            ->where('geminiDocumentName', '.*');
        Route::get('/chatbot/download/{id}', [ChatbotUploadController::class, 'download'])->name('chatbot.download');

        Route::get('/chatbot/prompt', [ChatbotController::class, 'editPrompt'])->name('chatbot.prompt.edit');
        Route::put('/chatbot/prompt', [ChatbotController::class, 'updatePrompt'])->name('chatbot.prompt.update');
    });

    // 4. FORUM
    Route::resource('forum', ForumController::class);
    Route::post('/replies', [ForumController::class, 'storeReply'])->name('replies.store');
    Route::put('/replies/{reply}', [ForumController::class, 'updateReply'])->name('replies.update');
    Route::delete('/replies/{reply}', [ForumController::class, 'destroyReply'])->name('replies.destroy');

    // 5. GENERAL MATERIALS (Student resources - Your Logic)
    // Public Access: View and Cloud Download
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/download/{material}', [MaterialController::class, 'download'])->name('materials.download');

    // Management: Restricted to Teacher/Admin
    Route::middleware(['role:teacher|admin'])->prefix('materials')->group(function () {
        Route::get('/create', [MaterialController::class, 'create'])->name('materials.create');
        Route::post('/', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    });

    // 6. ACTIVITIES & SUBMISSIONS
    Route::resource('activities', ActivityController::class);
    Route::get('/activities/{activity}/download', [ActivityController::class, 'download'])->name('activities.download');
    Route::get('/activities/{activity}/play', [ActivityController::class, 'play'])->name('activities.play');
    Route::post('/activities/{activity}/score', [ActivityController::class, 'submitScore'])->name('activities.score');
    Route::post('/activities/{activity}/submit', [ActivityController::class, 'submit'])->name('activities.submit');
    Route::get('/activities/download-submission/{submission}', [ActivityController::class, 'downloadSubmission'])->name('activities.downloadSubmission');
    Route::delete('/activities/{activity}/unsubmit', [ActivityController::class, 'unsubmit'])->name('activities.unsubmit');
    Route::delete('/submissions/{submission}', [ActivityController::class, 'destroySubmission'])->name('submissions.destroy')->middleware('role:teacher|admin');

    // 7. QUIZZES (Student & Teacher)
    Route::get('/quiz', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quiz/submit', [QuizController::class, 'store'])->name('quizzes.submit');
    Route::get('/quiz/{id}/history', [QuizController::class, 'history'])->name('quizzes.history');

    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::resource('quiz', TeacherQuizController::class);
        Route::get('/quizzes/{id}/results', [TeacherQuizController::class, 'results'])->name('quiz.results');
        Route::post('/quizzes/{quiz}/{user}/grant', [TeacherQuizController::class, 'grantAttempt'])->name('attempt.grant');
        Route::get('/attempts/{id}/review', [TeacherQuizController::class, 'showAttempt'])->name('attempt.review');
        Route::delete('/attempts/{id}', [TeacherQuizController::class, 'destroyAttempt'])->name('attempt.destroy');
    });

    // 8. STUDENTS ROSTER & REPORTS
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/students/{student}/activities', [StudentController::class, 'storeActivity'])->name('students.activities.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/student/{student}', [ReportController::class, 'showStudentPerformance'])->name('reports.student.detail');
    Route::middleware(['role:teacher|admin'])->group(function () {
        Route::post('/reports/remark', [ReportController::class, 'saveRemark'])->name('reports.remark.save');
        Route::delete('/reports/remark/{report}', [ReportController::class, 'deleteRemark'])->name('reports.remark.delete');
    });
});

require __DIR__.'/settings.php';
