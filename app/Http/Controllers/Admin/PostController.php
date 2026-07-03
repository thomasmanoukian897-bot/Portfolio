<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Post::class);

        $posts = Post::query()
            ->with(['user', 'categories'])
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('admin.posts.create', [
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
            'slug' => $this->resolveSlug($validated['title'], $validated['slug'] ?? null),
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('posts', 'public')
                : null,
        ]);

        $post->categories()->sync($categoryIds);

        return redirect()
            ->route('admin.posts.index')
            ->with('status', "Post \"{$post->title}\" created successfully.");
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('admin.posts.edit', [
            'post' => $post->load('categories'),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();
        $categoryIds = $validated['category_ids'];
        unset($validated['category_ids'], $validated['image'], $validated['remove_image']);

        if ($request->boolean('remove_image')) {
            $post->deleteFeaturedImage();
            $validated['image_path'] = null;
        } elseif ($request->hasFile('image')) {
            $validated['image_path'] = $post->storeFeaturedImage($request->file('image'));
        }

        $post->update([
            ...$validated,
            'slug' => $this->resolveSlug($validated['title'], $validated['slug'] ?? null, $post),
        ]);

        $post->categories()->sync($categoryIds);

        return redirect()
            ->route('admin.posts.index')
            ->with('status', "Post \"{$post->title}\" updated successfully.");
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $title = $post->title;
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('status', "Post \"{$title}\" deleted successfully.");
    }

    private function resolveSlug(string $title, ?string $slug, ?Post $post = null): string
    {
        $baseSlug = Str::slug($slug ?: $title);
        $candidate = $baseSlug;
        $suffix = 1;

        while (
            Post::query()
                ->where('slug', $candidate)
                ->when($post, fn ($query) => $query->whereKeyNot($post->id))
                ->exists()
        ) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
