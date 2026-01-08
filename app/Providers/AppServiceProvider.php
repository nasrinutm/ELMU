<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Fortify; // 👈 1. Import Fortify
use App\Models\User;         // 👈 2. Import User
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\UrlGenerator;

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
    public function boot(UrlGenerator $url): void
    {
        Gate::define('manage activities', function (User $user) {
            // Check if the user's role is 'teacher' (or 'admin')
            // This matches the logic you used in your RoleMiddleware
            if (method_exists($user, 'hasRole')) {
                return $user->hasRole('teacher');
            }
            return $user->roles->contains('name', 'teacher');
        });

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

        // if (env('APP_ENV') == 'production') {
        //     $url->forceScheme('https');
        // }
    }
}
