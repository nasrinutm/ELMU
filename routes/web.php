<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\UserController;



// Route::middleware(['auth'])->group(function () {
//     Route::middleware('can:isAdmin')->group(function () {
//         Route::get('/admin/register', [UserManagementController::class, 'create'])->name('admin.register');
//         Route::post('/admin/register', [UserManagementController::class, 'store']);
//     });
// });

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index'); // view all user
        Route::get('/users/add', [UserController::class, 'create'])->name('users.create');// add user
        Route::post('/users', [UserController::class, 'store'])->name('users.store');//submit new user
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); // Show edit form
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/create', [ForumController::class, 'create'])->name('forum.create');
    Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
    
    // We add this route so the 'store' method can redirect to the new post
    // You will need to create the show() method and Forum/Show.vue component later
    Route::get('/forum/{post}', [ForumController::class, 'show'])->name('forum.show');
});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
