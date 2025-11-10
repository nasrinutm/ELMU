<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request) // <-- Add Request $request here
    {
        // Start a query builder
        $query = User::query();

        // Apply the role filter if it exists
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
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
            
            // This is the part that fixes your error
            'filters' => [
                'role' => $request->input('role', ''), // Default to empty string
                'sort' => $request->input('sort', 'latest'), // Default to 'latest'
            ]
        ]);
    }
}