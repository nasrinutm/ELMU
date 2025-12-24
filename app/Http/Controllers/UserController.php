<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    public function index(Request $request)
    {

        $query = User::query()->with('roles');;


        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }


        $query->orderBy(
            'created_at',
            $request->input('sort') === 'oldest' ? 'asc' : 'desc'
        );


        $users = $query->paginate(15)->withQueryString();


        return Inertia::render('Users/Index', [
            'users' => $users,

            'roles' => Role::all()->pluck('name'),

            'filters' => [
                'role' => $request->input('role', ''),
                'sort' => $request->input('sort', 'latest'),
            ]
        ]);
    }

    public function create()
    {

        return Inertia::render('Admin/Users/Add', [
        'roles' => Role::all()->pluck('name')
    ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);


        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Assign the role
        $user->assignRole($request->role);

        // 4. Redirect
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
                'role' => $user->getRoleNames()->first(),
            ],
            'roles' => Role::all()->pluck('name')
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
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);



        $data = $request->only('name', 'username', 'email', 'role');


        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }


        $user->update(Arr::except($data, ['role']));


        $user->syncRoles($data['role']);


        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }
    public function destroy(User $user)
    {

        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
