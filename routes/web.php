<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\UserController;
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

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/chat', [ChatbotController::class, 'send'])->name('chat.send');

require __DIR__.'/settings.php';

Route::get('/test-gate', function () {
    $user = Auth::user();
    if (!$user) return 'No user logged in';

    // This is the Spatie function that checks the 'model_has_roles' table
    $user->hasRole('admin');

    return [
        'class' => get_class($user),
        'attributes' => $user->getAttributes(),
        'roles' => $user->getRoleNames(), // This will show ['admin']
        'isAdmin' => Gate::allows('isAdmin'), // This will be true *after* you fix AuthServiceProvider
    ];
});
