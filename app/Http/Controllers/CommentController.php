<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreReplyCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostCommentedNotification;
use App\Services\MentionParser;
use App\Services\MentionService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentController extends Controller
{
    public function __construct(
        private MentionService $mentionService,
        private MentionParser $mentionParser,
    ) {}

    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $validated = $request->validated();

        $comment = null;

        if (! $this->recentRootCommentExists($post, $request->user(), $validated['body'])) {
            $comment = $post->comments()->create([
                'user_id' => $request->user()->id,
                'parent_id' => null,
                'body' => $validated['body'],
            ]);

            if ($this->shouldNotifyPostAuthor($post, $request->user(), $validated['body'])) {
                $post->user->notify(new PostCommentedNotification($request->user(), $post, $comment));
            }

            $this->mentionService->syncAndNotify($comment, $request->user(), $post);
        }

        return redirect()
            ->to($this->postShowUrl($post, $comment))
            ->withFragment('comments')
            ->with('status', 'Your comment has been posted.');
    }

    public function reply(StoreReplyCommentRequest $request, Post $post, Comment $comment): RedirectResponse
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $validated = $request->validated();

        $reply = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $comment->id,
            'body' => $validated['body'],
        ]);

        if ($this->shouldNotifyPostAuthor($post, $request->user(), $validated['body'])) {
            $post->user->notify(new PostCommentedNotification($request->user(), $post, $reply));
        }

        $this->mentionService->syncAndNotify($reply, $request->user(), $post);

        return redirect()
            ->to($this->postShowUrl($post, $comment))
            ->withFragment("comment-{$comment->id}")
            ->with('status', 'Your reply has been posted.')
            ->with('show_replies_for', $comment->id);
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

    private function recentRootCommentExists(Post $post, User $user, string $body): bool
    {
        return $post->comments()
            ->where('user_id', $user->id)
            ->where('body', $body)
            ->whereNull('parent_id')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();
    }

    private function postShowUrl(Post $post, ?Comment $rootComment = null): string
    {
        $parameters = ['post' => $post];

        if ($rootComment !== null) {
            $page = $post->rootCommentPage($rootComment);

            if ($page > 1) {
                $parameters['page'] = $page;
            }
        }

        return route('posts.show', $parameters);
    }

    private function shouldNotifyPostAuthor(Post $post, User $commenter, string $body): bool
    {
        if ($post->user->is($commenter)) {
            return false;
        }

        return ! in_array($post->user->handle, $this->mentionParser->extractHandles($body), true);
    }
}
