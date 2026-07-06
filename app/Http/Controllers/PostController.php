<?php

namespace App\Http\Controllers;

use App\Enums\CommentVoteType;
use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use App\Services\FeaturedImageProcessor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString() ?: null;
        $search = $request->string('search')->trim()->toString() ?: null;
        $sort = $request->query('sort', 'newest');

        if (! in_array($sort, ['newest', 'oldest'], true)) {
            $sort = 'newest';
        }

        $categories = Category::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        $totalPostsCount = Post::query()->published()->count();

        $postsQuery = Post::query()
            ->published()
            ->with(['user', 'categories'])
            ->withCount(['likes', 'comments']);

        if ($sort === 'oldest') {
            $postsQuery->oldest('published_at');
        } else {
            $postsQuery->latest('published_at');
        }

        if ($categorySlug !== null && $categories->contains('slug', $categorySlug)) {
            $postsQuery->whereHas('categories', fn ($query) => $query->where('slug', $categorySlug));
        } else {
            $categorySlug = null;
        }

        if ($search !== null) {
            $postsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $postsQuery
            ->paginate(9)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'totalPostsCount' => $totalPostsCount,
            'selectedCategory' => $categorySlug,
            'selectedSort' => $sort,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('posts.create', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $categoryIds = $validated['category_ids'];
        unset($validated['category_ids'], $validated['image']);

        $post = Post::query()->create([
            ...$validated,
            'user_id' => $request->user()->id,
            'slug' => $this->resolveSlug($validated['title']),
            'published_at' => now(),
            'image_path' => $request->hasFile('image')
                ? app(FeaturedImageProcessor::class)->store($request->file('image'))
                : null,
        ]);

        $post->categories()->sync($categoryIds);

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'Your post has been published successfully.');
    }

    public function show(Post $post): View
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $post->load(['user', 'categories']);
        $post->loadCount(['comments', 'likes']);

        $comments = $post->rootComments()
            ->with([
                'user',
                'replies' => fn ($repliesQuery) => $repliesQuery
                    ->with('user')
                    ->withCount([
                        'votes as upvotes_count' => fn ($votesQuery) => $votesQuery->where('type', CommentVoteType::Up),
                        'votes as downvotes_count' => fn ($votesQuery) => $votesQuery->where('type', CommentVoteType::Down),
                    ])
                    ->oldest(),
            ])
            ->withCount([
                'votes as upvotes_count' => fn ($votesQuery) => $votesQuery->where('type', CommentVoteType::Up),
                'votes as downvotes_count' => fn ($votesQuery) => $votesQuery->where('type', CommentVoteType::Down),
            ])
            ->oldest()
            ->paginate(Comment::ROOT_PER_PAGE);

        $commentIds = $comments->getCollection()->flatMap(
            fn (Comment $comment) => $comment->replies->pluck('id')->prepend($comment->id)
        );

        $commentVotes = auth()->check()
            ? CommentVote::query()
                ->whereIn('comment_id', $commentIds)
                ->where('user_id', auth()->id())
                ->get()
                ->mapWithKeys(fn (CommentVote $vote) => [$vote->comment_id => $vote->type->value])
            : collect();

        return view('posts.show', [
            'post' => $post,
            'comments' => $comments,
            'isLikedByUser' => $post->isLikedBy(auth()->user()),
            'commentVotes' => $commentVotes,
        ]);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $title = $post->title;
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('status', "Your post \"{$title}\" has been deleted.");
    }

    private function resolveSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $candidate = $baseSlug;
        $suffix = 1;

        while (Post::query()->where('slug', $candidate)->exists()) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
