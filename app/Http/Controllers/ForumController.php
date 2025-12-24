<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post;
use App\Models\Reply;

class ForumController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function index(Request $request)
    {
        $posts = Post::query()
            ->with('user:id,name')
            ->withCount('allReplies as replies_count')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Forum/Index', [
            'posts' => $posts,
            // Check if user is logged in first, then check Spatie permission
            'can_create' => $request->user()?->can('create posts') ?? false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user(); 
        
        // Now Intelephense knows $user has the posts() relationship
        $post = $user->posts()->create($validated);

        return redirect(route('forum.show', $post))->with('success', 'Post created successfully!');
    }
    
    // ... rest of your methods
}