<?php

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('admins can create categories', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Laravel',
        ])
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('status');

    expect(Category::query()->where('slug', 'laravel')->exists())->toBeTrue();
});

test('posts require at least one category', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('posts.create'))
        ->post(route('posts.store'), [
            'title' => 'Uncategorized Post',
            'content' => '<p>Hello world</p>',
        ])
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors('category_ids');

    expect(Post::query()->where('slug', 'uncategorized-post')->exists())->toBeFalse();
});

test('admin posts require at least one category', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('admin.posts.create'))
        ->post(route('admin.posts.store'), [
            'title' => 'Uncategorized Post',
            'content' => '<p>Hello world</p>',
        ])
        ->assertRedirect(route('admin.posts.create'))
        ->assertSessionHasErrors('category_ids');

    expect(Post::query()->where('slug', 'uncategorized-post')->exists())->toBeFalse();
});

test('admins can assign multiple categories to a post', function () {
    $admin = User::factory()->admin()->create();
    $development = Category::factory()->create(['name' => 'Development', 'slug' => 'development']);
    $design = Category::factory()->create(['name' => 'Design', 'slug' => 'design']);

    $this->actingAs($admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Building with Laravel',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$development->id, $design->id],
        ])
        ->assertRedirect(route('admin.posts.index'));

    $post = Post::query()->where('slug', 'building-with-laravel')->first();

    expect($post)->not->toBeNull();
    expect($post->categories->pluck('id')->all())->toEqualCanonicalizing([
        $development->id,
        $design->id,
    ]);
});

test('admins can update post categories', function () {
    $admin = User::factory()->admin()->create();
    $development = Category::factory()->create(['name' => 'Development', 'slug' => 'development']);
    $business = Category::factory()->create(['name' => 'Business', 'slug' => 'business']);
    $post = Post::factory()->for($admin)->create();
    $post->categories()->attach($development);

    $this->actingAs($admin)
        ->put(route('admin.posts.update', $post), [
            'title' => $post->title,
            'content' => $post->content,
            'category_ids' => [$business->id],
        ])
        ->assertRedirect(route('admin.posts.index'));

    expect($post->fresh()->categories->pluck('id')->all())->toBe([$business->id]);
});

test('admin post edit form includes saved content for the wysiwyg editor', function () {
    $admin = User::factory()->admin()->create();
    $content = '<ul><li>Windows 11 Installation</li><li>Laravel</li></ul>';
    $post = Post::factory()->for($admin)->create(['content' => $content]);

    $this->actingAs($admin)
        ->get(route('admin.posts.edit', $post))
        ->assertSuccessful()
        ->assertSee('data-wysiwyg-input', false)
        ->assertSee(e($content), false);
});

test('posts index can be searched by title or excerpt', function () {
    $user = User::factory()->create();

    Post::factory()->for($user)->published()->create([
        'title' => 'Laravel Tips',
        'excerpt' => 'Helpful advice for beginners.',
    ]);

    Post::factory()->for($user)->published()->create([
        'title' => 'Design Systems',
        'excerpt' => 'Building consistent UI patterns.',
    ]);

    $this->get(route('posts.index', ['search' => 'Laravel']))
        ->assertSuccessful()
        ->assertSee('Laravel Tips')
        ->assertDontSee('Design Systems');

    $this->get(route('posts.index', ['search' => 'consistent']))
        ->assertSuccessful()
        ->assertSee('Design Systems')
        ->assertDontSee('Laravel Tips');

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee('fa-magnifying-glass', false);
});

test('posts index includes layout view toggle controls', function () {
    $user = User::factory()->create();

    Post::factory()->for($user)->published()->create(['title' => 'Toggle Test Post']);

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee('id="posts-feed"', false)
        ->assertSee('data-posts-view="grid"', false)
        ->assertSee('data-posts-view-toggle="grid"', false)
        ->assertSee('data-posts-view-toggle="list"', false)
        ->assertSee('aria-label="Grid view"', false)
        ->assertSee('aria-label="List view"', false);
});

test('posts index supports all public sort filters', function () {
    $user = User::factory()->create();

    $olderPost = Post::factory()->for($user)->published()->create([
        'title' => 'Older Post',
        'published_at' => now()->subDays(2),
        'views_count' => 1,
    ]);

    $newerPost = Post::factory()->for($user)->published()->create([
        'title' => 'Newer Post',
        'published_at' => now()->subDay(),
        'views_count' => 9,
    ]);

    PostLike::factory()->for($olderPost)->count(3)->create();
    PostLike::factory()->for($newerPost)->count(1)->create();

    Comment::factory()->for($olderPost)->for($user)->count(1)->create();
    Comment::factory()->for($newerPost)->for($user)->count(4)->create();

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer Post', 'Older Post']);

    $this->get(route('posts.index', ['sort' => 'oldest']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Older Post', 'Newer Post']);

    $this->get(route('posts.index', ['sort' => 'invalid']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer Post', 'Older Post']);

    $this->get(route('posts.index', ['sort' => 'most-liked']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Older Post', 'Newer Post']);

    $this->get(route('posts.index', ['sort' => 'least-liked']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer Post', 'Older Post']);

    $this->get(route('posts.index', ['sort' => 'most-commented']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer Post', 'Older Post']);

    $this->get(route('posts.index', ['sort' => 'least-commented']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Older Post', 'Newer Post']);

    $this->get(route('posts.index', ['sort' => 'most-viewed']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer Post', 'Older Post']);

    $this->get(route('posts.index', ['sort' => 'least-viewed']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Older Post', 'Newer Post']);
});

test('posts index can be filtered by category', function () {
    $user = User::factory()->create();
    $development = Category::factory()->create(['name' => 'Development', 'slug' => 'development']);
    $design = Category::factory()->create(['name' => 'Design', 'slug' => 'design']);

    $devPost = Post::factory()->for($user)->published()->create(['title' => 'Dev Post']);
    $devPost->categories()->attach($development);

    $designPost = Post::factory()->for($user)->published()->create(['title' => 'Design Post']);
    $designPost->categories()->attach($design);

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee('Development')
        ->assertSee('Design')
        ->assertSee('Dev Post')
        ->assertSee('Design Post');

    $this->get(route('posts.index', ['category' => 'development']))
        ->assertSuccessful()
        ->assertSee('Dev Post')
        ->assertDontSee('Design Post')
        ->assertSee('href="'.route('posts.index').'"', false);

    $this->get(route('posts.index', ['category' => 'invalid']))
        ->assertSuccessful()
        ->assertSee('Dev Post')
        ->assertSee('Design Post');
});

test('published posts display their categories publicly', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Development', 'slug' => 'development']);
    $post = Post::factory()->for($user)->published()->create(['title' => 'Public Post']);
    $post->categories()->attach($category);

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Development')
        ->assertSee(route('posts.index', ['category' => 'development']), false);
});

test('post unique views are tracked and displayed on the posts index', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::factory()->for($author)->published()->create([
        'title' => 'Viewed Post',
    ]);

    $this->get(route('posts.show', $post))->assertSuccessful();
    $this->get(route('posts.show', $post))->assertSuccessful();
    $this->actingAs($viewer)->get(route('posts.show', $post))->assertSuccessful();

    expect($post->fresh()->views_count)->toBe(2);

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee('fa-eye', false)
        ->assertSee('Viewed Post')
        ->assertSee('2');
});

test('deleting a category detaches it from posts', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->for($admin)->create();
    $post->categories()->attach($category);

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    expect($post->fresh()->categories)->toBeEmpty();
});

test('guests cannot access the create post form', function () {
    $this->get(route('posts.create'))
        ->assertRedirect(route('login'));
});

test('authenticated users can create and publish posts', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Development', 'slug' => 'development']);

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'My First Post',
            'excerpt' => 'A short summary.',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
        ])
        ->assertRedirect(route('posts.show', 'my-first-post'))
        ->assertSessionHas('status');

    $post = Post::query()->where('slug', 'my-first-post')->first();

    expect($post)->not->toBeNull();
    expect($post->user_id)->toBe($user->id);
    expect($post->isPublished())->toBeTrue();
    expect($post->categories->pluck('id')->all())->toBe([$category->id]);

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('My First Post')
        ->assertSee('Hello world');
});

test('regular users cannot access admin post management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.posts.index'))
        ->assertForbidden();
});

test('admins can upload an image when creating a post', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('featured.jpg');

    $this->actingAs($admin)
        ->post(route('admin.posts.store'), [
            'title' => 'Post with Image',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
            'image' => $image,
        ])
        ->assertRedirect(route('admin.posts.index'));

    $post = Post::query()->where('slug', 'post-with-image')->first();

    expect($post)->not->toBeNull();
    expect($post->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($post->image_path);
});

test('admins can replace a post image on update', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->for($admin)->create([
        'image_path' => UploadedFile::fake()->image('old.jpg')->store('posts', 'public'),
    ]);
    $post->categories()->attach($category);
    $newImage = UploadedFile::fake()->image('new.jpg');

    $this->actingAs($admin)
        ->put(route('admin.posts.update', $post), [
            'title' => $post->title,
            'content' => $post->content,
            'category_ids' => [$category->id],
            'image' => $newImage,
        ])
        ->assertRedirect(route('admin.posts.index'));

    $oldPath = $post->image_path;
    $post->refresh();

    expect($post->image_path)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($post->image_path);
});

test('uploaded post images are resized without cropping', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('tall.jpg', 800, 1200);

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'Resized Image Post',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
            'image' => $image,
        ])
        ->assertRedirect(route('posts.show', 'resized-image-post'));

    $post = Post::query()->where('slug', 'resized-image-post')->firstOrFail();
    $storedImage = imagecreatefromstring(Storage::disk('public')->get($post->image_path));

    expect($storedImage)->not->toBeFalse();
    expect(imagesx($storedImage))->toBe(480);
    expect(imagesy($storedImage))->toBe(720);

    imagedestroy($storedImage);
});

test('authenticated users can upload an image when publishing a post', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('featured.jpg');

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'Image Post',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
            'image' => $image,
        ])
        ->assertRedirect(route('posts.show', 'image-post'));

    $post = Post::query()->where('slug', 'image-post')->first();

    expect($post->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($post->image_path);

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee($post->featuredImageUrl(), false);
});

test('post authors can delete their own posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->published()->create(['title' => 'My Post']);

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('status');

    expect(Post::query()->find($post->id))->toBeNull();
});

test('users cannot delete posts they did not create', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->for($author)->published()->create();

    $this->actingAs($otherUser)
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    expect(Post::query()->find($post->id))->not->toBeNull();
});

test('users can delete posts when their email matches the author email on another account', function () {
    $author = User::factory()->create(['email' => 'author@example.com']);
    $post = Post::factory()->for($author)->published()->create(['title' => 'Shared Email Post']);
    $viewer = User::factory()->make([
        'id' => $author->id + 1000,
        'email' => 'author@example.com',
        'google_id' => 'google-123',
    ]);

    expect($post->isOwnedBy($viewer))->toBeTrue();

    $this->actingAs($viewer)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('status');

    expect(Post::query()->find($post->id))->toBeNull();
});

test('users see a delete button when their email matches the author email on another account', function () {
    $author = User::factory()->create(['email' => 'author@example.com']);
    $post = Post::factory()->for($author)->published()->create(['title' => 'Shared Email Post']);
    $viewer = User::factory()->make([
        'id' => $author->id + 1000,
        'email' => 'author@example.com',
        'google_id' => 'google-123',
    ]);

    $this->actingAs($viewer)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('fa-trash', false);
});

test('post ownership matches author email case insensitively', function () {
    $author = User::factory()->make(['id' => 10, 'email' => 'Author@Example.com']);
    $viewer = User::factory()->make(['id' => 20, 'email' => 'author@example.com']);
    $post = Post::factory()->make(['user_id' => 10]);
    $post->setRelation('user', $author);

    expect($post->isOwnedBy($viewer))->toBeTrue();
});

test('admins cannot delete posts from the public site', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->published()->create(['title' => 'Author Post']);

    $this->actingAs($admin)
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    expect(Post::query()->find($post->id))->not->toBeNull();
});

test('admins can delete posts from the admin panel', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->published()->create(['title' => 'Author Post']);

    $this->actingAs($admin)
        ->delete(route('admin.posts.destroy', $post))
        ->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('status');

    expect(Post::query()->find($post->id))->toBeNull();
});

test('admins do not see a delete button on public post pages for other users posts', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->published()->create(['title' => 'Author Post']);

    $this->actingAs($admin)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertDontSee('fa-trash', false);
});

test('admins can delete their own posts from the public site', function () {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->for($admin)->published()->create(['title' => 'Admin Post']);

    $this->actingAs($admin)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('status');

    expect(Post::query()->find($post->id))->toBeNull();
});

test('admins see a delete button on their own public post pages', function () {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->for($admin)->published()->create(['title' => 'Admin Post']);

    $this->actingAs($admin)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('fa-trash', false);
});

test('guests cannot delete posts', function () {
    $post = Post::factory()->published()->create();

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('login'));

    expect(Post::query()->find($post->id))->not->toBeNull();
});

test('deleting a post removes its stored video', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = Category::factory()->create();
    $video = UploadedFile::fake()->create('demo.mp4', 1024, 'video/mp4');

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'Deletable Video Post',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
            'video' => $video,
        ]);

    $post = Post::query()->where('slug', 'deletable-video-post')->firstOrFail();
    $videoPath = $post->video_path;

    Storage::disk('public')->assertExists($videoPath);

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'));

    Storage::disk('public')->assertMissing($videoPath);
});

test('authenticated users can upload a video when publishing a post', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = Category::factory()->create();
    $video = UploadedFile::fake()->create('demo.mp4', 1024, 'video/mp4');
    $image = UploadedFile::fake()->image('thumbnail.jpg');

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'Video Post',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
            'video' => $video,
            'image' => $image,
        ])
        ->assertRedirect(route('posts.show', 'video-post'));

    $post = Post::query()->where('slug', 'video-post')->first();

    expect($post->video_path)->not->toBeNull();
    expect($post->image_path)->not->toBeNull();
    expect($post->hasVideo())->toBeTrue();
    Storage::disk('public')->assertExists($post->video_path);
    Storage::disk('public')->assertExists($post->image_path);

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee($post->featuredVideoUrl(), false);

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee('w-6 h-6', false);
});

test('post authors see a delete button on their posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->published()->create(['title' => 'Deletable Post']);

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('fa-trash', false);

    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertDontSee('fa-trash', false);
});
