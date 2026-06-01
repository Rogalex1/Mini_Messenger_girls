<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $posts = Post::with('user.profile', 'comments.user.profile', 'likes.user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($posts);
    }

    public function myPosts()
    {
        $posts = Post::where('user_id', auth()->id())
            ->with('comments.user.profile', 'likes.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:2000',
            'type' => 'required|in:text,image,video',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:102400',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'user_id' => auth()->id(),
            'content' => $request->content,
            'type' => $request->type,
        ];

        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('posts', 'public');
            $data['media_url'] = Storage::url($path);
        }

        $post = Post::create($data);
        $post->load('user.profile');

        return response()->json($post, 201);
    }

    public function show($id)
    {
        $post = Post::with('user.profile', 'comments.user.profile', 'likes.user')->findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $post = Post::where('user_id', auth()->id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:2000',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:102400',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['content']);

        if ($request->hasFile('media')) {
            if ($post->media_url) {
                $path = str_replace('/storage/', '', $post->media_url);
                Storage::disk('public')->delete($path);
            }
            $path = $request->file('media')->store('posts', 'public');
            $data['media_url'] = Storage::url($path);
        }

        $post->update($data);
        $post->load('user.profile', 'comments.user.profile', 'likes.user');

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = Post::where('user_id', auth()->id())->findOrFail($id);

        if ($post->media_url) {
            $path = str_replace('/storage/', '', $post->media_url);
            Storage::disk('public')->delete($path);
        }

        $post->delete();
        return response()->json(['message' => 'Publication supprimée']);
    }

    public function like($id)
    {
        $post = Post::findOrFail($id);

        $like = PostLike::where('post_id', $id)->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            return response()->json(['message' => 'Like retiré', 'liked' => false]);
        } else {
            PostLike::create([
                'post_id' => $id,
                'user_id' => auth()->id(),
            ]);
            $post->increment('likes_count');
            return response()->json(['message' => 'Like ajouté', 'liked' => true]);
        }
    }

    public function comment(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment = PostComment::create([
            'post_id' => $id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        $post->increment('comments_count');
        $comment->load('user.profile');

        return response()->json($comment, 201);
    }

    public function deleteComment($postId, $commentId)
    {
        $comment = PostComment::where('post_id', $postId)
            ->where('user_id', auth()->id())
            ->findOrFail($commentId);

        $post = Post::findOrFail($postId);
        $post->decrement('comments_count');
        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé']);
    }
}

