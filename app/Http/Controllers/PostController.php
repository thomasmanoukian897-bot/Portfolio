<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
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

        $categories = Category::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->orderBy('name')
            ->get();

        $postsQuery = Post::query()
            ->published()
            ->with(['user', 'categories'])
            ->latest('published_at');

        if ($categorySlug !== null && $categories->contains('slug', $categorySlug)) {
            $postsQuery->whereHas('categories', fn ($query) => $query->where('slug', $categorySlug));
        } else {
            $categorySlug = null;
        }

        $posts = $postsQuery
            ->paginate(9)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $categorySlug,
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
                ? $request->file('image')->store('posts', 'public')
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

        return view('posts.show', [
            'post' => $post,
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
