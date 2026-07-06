<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()
            ->route('posts.show', $post)
            ->withFragment('comments')
            ->with('status', 'Your comment has been posted.');
    }

    public function destroy(Post $post, Comment $comment): RedirectResponse
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()
            ->route('posts.show', $post)
            ->withFragment('comments')
            ->with('status', 'Comment deleted.');
    }
}
