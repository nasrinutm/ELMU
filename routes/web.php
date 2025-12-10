<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Models\User;
use App\Models\Material;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotUploadController;
use App\Http\Controllers\ForumController;
use Gemini\Laravel\Facades\Gemini;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// --- AUTHENTICATED ROUTES ---
Route::middleware(['auth'])->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', function () {
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
    })->middleware(['verified'])->name('dashboard');

    // 2. CHATBOT (Moved outside of dashboard closure)
    Route::post('/chat/send', [ChatbotController::class, 'send'])->name('chat.send');

    // 3. ADMIN ROUTES (Consolidated into one block)
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
});

require __DIR__.'/settings.php';