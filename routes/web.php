<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;
// --- CONTROLLER IMPORTS ---
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotUploadController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
// --------------------------
use Gemini\Laravel\Facades\Gemini;
use App\Http\Controllers\ReportController;
use App\Models\User;
use App\Models\Material;


// --- TEST & SETUP ROUTES ---
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


// --- PUBLIC ROUTES ---
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Setup AI Store
Route::get('/setup-ai', [ChatbotController::class, 'setupStore']);


// --- AUTHENTICATED ROUTES GROUP ---
Route::middleware(['auth', 'verified'])->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Admin Redirect Logic
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index');
        }

        // Standard Dashboard Logic (For Teachers & Students)
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

    Route::get('/settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    // Profile: Show form, handle update, and delete account
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // ==========================================
    // MAIN APP ROUTES
    // ==========================================

    // 1. DASHBOARD
    Route::get('/dashboard', function () {
        // If the user is an Admin, redirect them to User Management
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index');
        }

        // Standard Dashboard Logic (For Teachers & Students)
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

    // 3. ADMIN ROUTES GROUP
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

    // Teacher Only Group (CRUD)
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

    // 7. REPORT ROUTES (UC0014: Generate Reports)
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
});

}); // <-- End of auth middleware group
