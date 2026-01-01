<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gemini\Laravel\Facades\Gemini;

// --- MODEL IMPORTS ---
use App\Models\User;
use App\Models\Material;
use App\Models\Quiz;

// --- CONTROLLER IMPORTS ---
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotUploadController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
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

    // 3. ADMIN ROUTES (Prefix applied for cleaner URL structure)
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/add', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // AI Knowledge Base Management
        Route::get('/chatbot', [ChatbotUploadController::class, 'index'])->name('chatbot.details');
        Route::get('/chatbot/upload', [ChatbotUploadController::class, 'create'])->name('upload.create');
        Route::post('/chatbot/upload', [ChatbotUploadController::class, 'store'])->name('upload.store');
        Route::delete('/chatbot/{fileName}', [ChatbotUploadController::class, 'destroy'])
            ->where('fileName', '.*')
            ->name('upload.delete');

        // System Prompt Management
        Route::get('/chatbot/prompt', [ChatbotController::class, 'editPrompt'])->name('chatbot.prompt.edit');
        Route::put('/chatbot/prompt', [ChatbotController::class, 'updatePrompt'])->name('chatbot.prompt.update');
    });

    // 4. FORUM
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/create', [ForumController::class, 'create'])->name('forum.create');
    Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
    Route::get('/forum/{post}', [ForumController::class, 'show'])->name('forum.show');
    Route::get('/forum/{post}/edit', [ForumController::class, 'edit'])->name('forum.edit');
    Route::put('/forum/{post}', [ForumController::class, 'update'])->name('forum.update');
    Route::delete('/forum/{post}', [ForumController::class, 'destroy'])->name('forum.destroy');

    Route::post('/replies', [ForumController::class, 'storeReply'])->name('replies.store');
    Route::put('/replies/{reply}', [ForumController::class, 'updateReply'])->name('replies.update');
    Route::delete('/replies/{reply}', [ForumController::class, 'destroyReply'])->name('replies.destroy');

    // 5. MATERIALS (General Study Materials)
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    Route::middleware(['role:teacher'])->prefix('materials')->group(function () {
        Route::get('/create', [MaterialController::class, 'create'])->name('materials.create');
        Route::post('/', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    });

    // 6. ACTIVITIES
    Route::resource('activities', ActivityController::class);
    Route::get('/activities/{activity}/download', [ActivityController::class, 'download'])->name('activities.download');
    Route::get('/activities/{activity}/play', [ActivityController::class, 'play'])->name('activities.play');
    Route::post('/activities/{activity}/score', [ActivityController::class, 'submitScore'])->name('activities.score');

    // 7. STUDENT SUBMISSIONS
    Route::post('/activities/{activity}/submit', [ActivityController::class, 'submit'])->name('activities.submit');
    Route::delete('/activities/{activity}/unsubmit', [ActivityController::class, 'unsubmit'])->name('activities.unsubmit');

    Route::get('/activities/download-submission/{submission}', [ActivityController::class, 'downloadSubmission'])->name('activities.downloadSubmission');
    Route::delete('/submissions/{submission}', [ActivityController::class, 'destroySubmission'])->name('submissions.destroy')->middleware('role:teacher|admin');

    // 8. QUIZZES
    Route::get('/quiz', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quiz/submit', [QuizController::class, 'store'])->name('quizzes.submit');
    Route::get('/quiz/{id}/history', [QuizController::class, 'history'])->name('quizzes.history');

    // 9. TEACHER QUIZ MANAGEMENT
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/quizzes', [TeacherQuizController::class, 'index'])->name('quiz.index');
        Route::get('/quizzes/create', [TeacherQuizController::class, 'create'])->name('quiz.create');
        Route::post('/quizzes', [TeacherQuizController::class, 'store'])->name('quiz.store');
        Route::get('/quizzes/{id}/edit', [TeacherQuizController::class, 'edit'])->name('quiz.edit');
        Route::put('/quizzes/{id}', [TeacherQuizController::class, 'update'])->name('quiz.update');
        Route::delete('/quizzes/{id}', [TeacherQuizController::class, 'destroy'])->name('quiz.destroy');
        Route::get('/quizzes/{id}/results', [TeacherQuizController::class, 'results'])->name('quiz.results');
        Route::post('/quizzes/{quiz}/{user}/grant', [TeacherQuizController::class, 'grantAttempt'])->name('attempt.grant');
        Route::delete('/attempts/{id}/unlock', [TeacherQuizController::class, 'unlockAttempt'])->name('attempt.unlock');
    });

    // 10. STUDENTS ROSTER
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::post('/students/{student}/activities', [StudentController::class, 'storeActivity'])->name('students.activities.store');
    Route::put('/students/{student}/activities/{activity}', [StudentController::class, 'updateActivity'])->name('students.activities.update');
    Route::delete('/students/{student}/activities/{activity}', [StudentController::class, 'destroyActivity'])->name('students.activities.destroy');

    // 11. REPORTS
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/student/{student}', [ReportController::class, 'showStudentPerformance'])->name('reports.student.detail');

    Route::middleware(['role:teacher|admin'])->group(function () {
        Route::post('/reports/remark', [ReportController::class, 'saveRemark'])->name('reports.remark.save');
        Route::delete('/reports/remark/{report}', [ReportController::class, 'deleteRemark'])->name('reports.remark.delete');
    });

});

require __DIR__.'/settings.php';
