<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
// use App\Models\Post; // <-- Uncomment this when you have a Post model

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // --- WHEN YOU HAVE A 'Post' MODEL, REPLACE THIS ---
        $posts = (object)[
            'data' => [
                // Sample data so your page doesn't look empty
                [
                    'id' => 1,
                    'title' => 'My first forum post!',
                    'user' => ['name' => 'Admin User'],
                    'created_at' => '2025-11-09T10:30:00.000Z',
                    'replies_count' => 5,
                ],
                [
                    'id' => 2,
                    'title' => 'How to use Laravel with Inertia and Vue?',
                    'user' => ['name' => 'New Student'],
                    'created_at' => '2025-11-08T14:15:00.000Z',
                    'replies_count' => 2,
                ],
            ]
        ];
        // --- END OF REPLACEMENT BLOCK ---

        /*
        // --- USE THIS CODE ONCE YOUR POST MODEL IS READY ---
        // Eager load the 'user' relationship and get a reply count
        $posts = Post::with('user')
            ->withCount('replies') // Assumes you have a 'replies' relationship
            ->latest() // Newest first
            ->paginate(20) // Paginate the results
            ->withQueryString(); // Append filters to pagination links
        */


        return Inertia::render('Forum/Index', [
            'posts' => $posts,
            // You can add filters here later, just like your users page
            'filters' => (object)[], 
        ]);
    }
}