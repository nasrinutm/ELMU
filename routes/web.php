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
use Gemini\Laravel\Facades\Gemini;

Route::get('/test-models', function () {
    try {
        // This fetches all available models from Google
        $response = Gemini::models()->list();

        // Extract just the names to make it readable
        $models = collect($response->models)
            ->map(fn ($model) => [
                'name' => $model->name,
                'display_name' => $model->displayName,
                'capabilities' => $model->supportedGenerationMethods
            ]);

        return $models;
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/setup-ai', [App\Http\Controllers\ChatbotController::class, 'setupStore']);

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index'); // view all user
        Route::get('/users/add', [UserController::class, 'create'])->name('users.create');// add user
        Route::post('/users', [UserController::class, 'store'])->name('users.store');//submit new user
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); // Show edit form
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/chatbot', [ChatbotUploadController::class, 'index'])
                ->name('chatbot.details');

            // Show the upload form
            Route::get('/chatbot/upload', [ChatbotUploadController::class, 'create'])
                ->name('upload.create');

            // Handle the upload
            Route::post('/chatbot/upload', [ChatbotUploadController::class, 'store'])
                ->name('upload.store');

            // Delete a file
            Route::delete('/chatbot/{fileName}', [ChatbotUploadController::class, 'destroy'])
                ->where('fileName', '.*') // Allows slashes in ID if needed
                ->name('upload.delete');
    });

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
});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// --- AUTHENTICATED ROUTES ---
Route::middleware(['auth'])->group(function () {

    // DASHBOARD (With Stats)
    Route::get('/dashboard', function () {
        $stats = [
            'users' => User::count(),
            'materials' => Material::count(),
            'my_materials' => Material::where('user_id', Auth::id())->count(),
        ];
Route::post('/chat', [ChatbotController::class, 'send'])->name('chat.send');

require __DIR__.'/settings.php';

        $recentMaterials = Material::with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentMaterials' => $recentMaterials
        ]);
    })->middleware(['verified'])->name('dashboard');

    // ADMIN: User Management
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/add', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // MATERIALS: Shared Routes (View/Download)
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    // MATERIALS: Teacher Only (CRUD)
    Route::middleware(['role:teacher'])->prefix('materials')->group(function () {
        Route::get('/create', [MaterialController::class, 'create'])->name('materials.create');
        Route::post('/', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    });
});
