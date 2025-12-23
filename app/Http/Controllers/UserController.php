<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash; // <-- Added this
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule; // <-- ADD THIS
use Illuminate\Support\Arr;      // <-- ADD THIS

class UserController extends Controller
{
    public function index(Request $request) // <-- Add Request $request here
    {
        // Start a query builder
        $query = User::query()->with('roles');;

        // Apply the role filter if it exists
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }

        // Apply the sorting
        // Default to 'latest' (descending) if 'sort' is not 'oldest'
        $query->orderBy(
            'created_at',
            $request->input('sort') === 'oldest' ? 'asc' : 'desc'
        );

        // Paginate the results and append the query string (so filters work on page 2)
        $users = $query->paginate(15)->withQueryString(); // You can change 15 to any number

        // Now, render the component with all the props
        return Inertia::render('Users/Index', [
            'users' => $users,

            'roles' => Role::all()->pluck('name'),

            'filters' => [
                'role' => $request->input('role', ''), // Default to empty string
                'sort' => $request->input('sort', 'latest'), // Default to 'latest'
            ]
        ]);
    }

    public function create()
    {
        // Show register form (Inertia Vue page)
        return Inertia::render('Admin/Users/Add', [
        'roles' => Role::all()->pluck('name')
    ]);
    }

    public function store(Request $request)
    {
        // 1. Update the validation
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        // 2. Update the create() call
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Assign the role (this is still correct)
        $user->assignRole($request->role);

        // 4. Redirect (this is still correct)
        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(), // Get the user's *current* role
            ],
            'roles' => Role::all()->pluck('name') // Get *all* available roles
        ]);
    }

    public function update(Request $request, User $user)
    {
        // 1. VALIDATE THE DATA
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Ignore this user's current username
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id), // Ignore this user's current email
            ],
            'password' => 'nullable|string|min:8|confirmed', // 'nullable' makes it optional
            'role' => 'required|string|exists:roles,name',
        ]);


        // 2. PREPARE THE DATA TO UPDATE
        $data = $request->only('name', 'username', 'email', 'role');

        // 3. HANDLE THE PASSWORD (only update if a new one was typed)
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // 4. UPDATE THE USER
        $user->update(Arr::except($data, ['role'])); // Update user fields, except 'role'

        // 5. SYNC THE ROLE
        $user->syncRoles($data['role']);

        // 6. REDIRECT
        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }
    public function destroy(User $user)
    {
        // Protection: Prevent an admin from deleting their own account
        // if ($user->id === auth()->id()) {
        //     return redirect()->back()
        //         ->with('error', 'You cannot delete your own account.');
        // }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
