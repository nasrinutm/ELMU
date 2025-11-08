<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {
    Route::middleware('can:isAdmin')->group(function () {
        Route::get('/admin/register', [UserManagementController::class, 'create'])->name('admin.register');
        Route::post('/admin/register', [UserManagementController::class, 'store']);
    });
});

Route::get('/users', [UserController::class, 'index']);

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

    // Debug everything
    return [
        'class' => get_class($user),
        'attributes' => $user->getAttributes(), // raw database values
        'role_property' => $user->role,
        'isAdmin' => Gate::allows('isAdmin'),
    ];
});