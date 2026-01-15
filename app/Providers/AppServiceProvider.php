<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

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
        // Force HTTPS in production to prevent mixed content errors on Railway
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Define permissions for teachers and admins
        Gate::define('manage activities', function (User $user) {
            $privilegedRoles = ['teacher', 'admin'];

            if (method_exists($user, 'hasRole')) {
                return $user->hasAnyRole($privilegedRoles);
            }

            return $user->roles->pluck('name')->intersect($privilegedRoles)->isNotEmpty();
        });

        // Custom Fortify authentication: allows login via Email OR Username
        Fortify::authenticateUsing(function ($request) {
            $user = User::where('email', $request->email)
                ->orWhere('username', $request->email)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });
    }
}
