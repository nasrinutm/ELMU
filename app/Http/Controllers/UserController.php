<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users with search, role filtering, and sorting.
     */
    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        // 1. SEARCH FILTER (Name, Email, or Username)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // 2. ROLE FILTER
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }

        // 3. SORTING (Latest vs Oldest)
        $query->orderBy(
            'created_at',
            $request->input('sort') === 'oldest' ? 'asc' : 'desc'
        );

        return Inertia::render('Users/Index', [
            'users' => $query->paginate(15)->withQueryString(),
            'roles' => Role::all()->pluck('name'),
            'filters' => $request->only(['search', 'role', 'sort']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Add', [
            'roles' => Role::all()->pluck('name')
        ]);
    }

    /**
     * Store a newly created user with success notification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|alpha_dash|max:255|unique:users,username',
            'email' => 'required|email:rfc,dns|max:255|unique:users,email',
            'role' => 'required|string|exists:roles,name',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers()
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        // Success notification with timestamp handshake
        return redirect()->route('users.index')
            ->with('success', 'New user ' . $user->name . ' has been successfully registered. [' . now()->timestamp . ']');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
            ],
            'roles' => Role::all()->pluck('name')
        ]);
    }

    /**
     * Update the user with success notification.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email:rfc,dns', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|exists:roles,name',
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)->letters()->numbers()
            ],
        ]);

        $user->update(Arr::only($validated, ['name', 'username', 'email']));

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles($validated['role']);

        // Success notification with timestamp handshake
        return redirect()->route('users.index')
            ->with('success', 'User profile for ' . $user->name . ' updated successfully. [' . now()->timestamp . ']');
    }

    /**
     * Remove the user with success notification.
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Critical Error: You cannot delete your own administrative account.');
        }

        $userName = $user->name;
        $user->delete();

        // Success notification with timestamp handshake
        return redirect()->route('users.index')
            ->with('success', 'User account ' . $userName . ' permanently removed. [' . now()->timestamp . ']');
    }
}
