<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post; // <-- Uncomment this when you have a Post model

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 2. Replace the sample data with this query
        $posts = Post::query()
            ->with('user:id,name')  // Eager-load the user's name
            ->withCount('allReplies as replies_count') // Get the count from the 'allReplies' relationship
            ->latest() // Newest first
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Forum/Index', [
            'posts' => $posts,
            'filters' => (object)[], 
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This just shows the Vue component you just created
        return Inertia::render('Forum/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the form data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10',
        ]);

        // 2. Create the post and link it to the logged-in user
        // This automatically fills the 'user_id'
        $post = $request->user()->posts()->create($validated);

        // 3. Redirect the user to the new post's page
        // (We'll add this route in the next step)
        return redirect(route('forum.show', $post))->with('success', 'Post created successfully!');
    }

    public function show(Post $post)
    {
        // Load the post's author, and all top-level replies
        $post->load([
            'user:id,name,username', // Get the post's author
            
            // Load top-level replies and...
            'replies' => function ($query) {
                // ...include their authors and...
                $query->with('user:id,name,username')
                      // ...recursively load all children, also with their authors
                      ->with('children.user:id,name,username'); 
            }
        ]);

        return Inertia::render('Forum/Show', [
            'post' => $post
        ]);
    }
}