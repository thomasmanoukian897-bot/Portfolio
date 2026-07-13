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
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $body = $validated['body'] ?? '';
        $imagePath = $this->storeCommentImage($request);

        $comment = null;

        if (! $this->recentRootCommentExists($post, $request->user(), $body, $imagePath)) {
            $comment = $post->comments()->create([
                'user_id' => $request->user()->id,
                'parent_id' => null,
                'body' => $body,
                'image_path' => $imagePath,
            ]);

            if ($this->shouldNotifyPostAuthor($post, $request->user(), $body)) {
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
        $body = $validated['body'] ?? '';

        $reply = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $comment->id,
            'body' => $body,
            'image_path' => $this->storeCommentImage($request),
        ]);

        if ($this->shouldNotifyPostAuthor($post, $request->user(), $body)) {
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

    private function recentRootCommentExists(Post $post, User $user, string $body, ?string $imagePath): bool
    {
        if ($imagePath !== null) {
            return false;
        }

        return $post->comments()
            ->where('user_id', $user->id)
            ->where('body', $body)
            ->whereNull('parent_id')
            ->whereNull('image_path')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();
    }

    private function storeCommentImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        /** @var UploadedFile $image */
        $image = $request->file('image');

        return $image->store('comment-images', 'public');
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
