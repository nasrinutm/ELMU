<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post; // <-- Uncomment this when you have a Post model
use App\Models\Reply;

class ForumController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
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

    public function storeReply(Request $request)
    {
        // 1. Validate incoming data
        $request->validate([
            'body' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'parent_id' => 'nullable|exists:replies,id',
        ]);

        // 2. Create the reply
        Reply::create([
            'user_id' => auth()->id(),
            'post_id' => $request->post_id,
            'parent_id' => $request->parent_id,
            'body' => $request->body,
        ]);

        // 3. Redirect back to the post page
        return back()->with('success', 'Reply posted!');
    }

    public function edit(Post $post)
    {
        // 1. Authorize the action
        $this->authorize('update', $post);

        // 2. Return the Edit page 
        return Inertia::render('Forum/Edit', [
            'post' => $post
        ]);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post)
    {
        // 1. Authorize
        $this->authorize('update', $post);

        // 2. Validate
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10',
        ]);

        // 3. Update
        $post->update($validated);

        // 4. Redirect
        return redirect(route('forum.show', $post))->with('success', 'Post updated!');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        // 1. Authorize
        $this->authorize('delete', $post);

        // 2. Delete
        $post->delete();

        // 3. Redirect
        return redirect(route('forum.index'))->with('success', 'Post deleted!');
    }

    // --- 🤷‍♂️ REPLY ACTIONS ---

    /**
     * Update the specified reply in storage.
     */
    public function updateReply(Request $request, Reply $reply)
    {
        // 1. Authorize
        $this->authorize('update', $reply);
        
        // 2. Validate
        $validated = $request->validate(['body' => 'required|string']);

        // 3. Update
        $reply->update($validated);

        // 4. Redirect back
        return back()->with('success', 'Reply updated!');
    }

    /**
     * Remove the specified reply from storage.
     */
    public function destroyReply(Reply $reply)
    {
        // 1. Authorize
        $this->authorize('delete', $reply);

        // 2. Delete
        $reply->delete();

        // 3. Redirect back
        return back()->with('success', 'Reply deleted!');
    }
}