<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify; // 👈 1. Import Fortify
use App\Models\User;         // 👈 2. Import User
use Illuminate\Support\Facades\Hash;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::authenticateUsing(function ($request) {
            // Check for a user by email OR username
            $user = User::where('email', $request->email)
                        ->orWhere('username', $request->email)
                        ->first();

            if ($user &&
                Hash::check($request->password, $user->password)) {
                return $user;
            }
        });
    }
}
