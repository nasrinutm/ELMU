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
        
        // Handle Search - Changed body to content
        ->when($request->input('search'), function ($query, $search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
        })
        
        ->when($request->input('sort'), function ($query, $sort) {
            if ($sort === 'replies') {
                $query->orderBy('replies_count', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        }, function ($query) {
            $query->latest();
        })
        ->paginate(20)
        ->withQueryString();

        return Inertia::render('Forum/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search', 'sort']),
            'can_create' => $request->user()?->can('create', Post::class) ?? true,
        ]);
    }

    public function create()
    {
        return Inertia::render('Forum/Create');
    }

    public function store(Request $request)
    {
        // Validation updated: body -> content
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ]);

        $post = $request->user()->posts()->create($validated);

        return redirect(route('forum.show', $post))->with('success', 'Post created successfully!');
    }

    public function show(Post $post)
    {
        $post->load([
            'user:id,name,username', // Get the post's author

            // Load top-level replies and...
            'replies' => function ($query) {
                $query->with('user:id,name,username')
                      // ...recursively load all children, also with their authors
                      ->with('children.user:id,name,username');
            }
        ]);

        return Inertia::render('Forum/Show', [
            'post' => array_merge($post->toArray(), [
                'can_update' => \Illuminate\Support\Facades\Gate::allows('update', $post),
                'can_delete' => \Illuminate\Support\Facades\Gate::allows('delete', $post),
            ])
        ]);
    }

    public function storeReply(Request $request)
    {
        // Replies correctly use 'body'
        $request->validate([
            'body' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'parent_id' => 'nullable|exists:replies,id',
        ]);

        Reply::create([
            'user_id' => $request->user()->id,
            'post_id' => $request->post_id,
            'parent_id' => $request->parent_id,
            'body' => $request->body,
        ]);

        return back()->with('success', 'Reply posted!');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        return Inertia::render('Forum/Edit', [
            'post' => $post
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        // Validation updated: body -> content
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ]);

        $post->update($validated);

        return redirect(route('forum.show', $post))->with('success', 'Post updated!');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return redirect(route('forum.index'))->with('success', 'Post deleted!');
    }

    public function updateReply(Request $request, Reply $reply)
    {
        $this->authorize('update', $reply);
        $validated = $request->validate(['body' => 'required|string']);
        $reply->update($validated);
        return back()->with('success', 'Reply updated!');
    }

    public function destroyReply(Reply $reply)
    {
        $this->authorize('delete', $reply);
        $reply->delete();
        return back()->with('success', 'Reply deleted!');
    }
}
