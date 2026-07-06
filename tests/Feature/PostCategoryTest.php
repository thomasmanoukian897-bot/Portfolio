<?php

use App\Models\Category;
use App\Models\Post;
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

test('posts index can be sorted by published date', function () {
    $user = User::factory()->create();

    $olderPost = Post::factory()->for($user)->published()->create([
        'title' => 'Older Post',
        'published_at' => now()->subDays(2),
    ]);

    $newerPost = Post::factory()->for($user)->published()->create([
        'title' => 'Newer Post',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer Post', 'Older Post']);

    $this->get(route('posts.index', ['sort' => 'oldest']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Older Post', 'Newer Post']);

    $this->get(route('posts.index', ['sort' => 'invalid']))
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer Post', 'Older Post']);
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
        ->assertDontSee('Design Post');

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
        ->assertSee('Development');
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

test('uploaded post images are cropped to a 16:9 aspect ratio', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('tall.jpg', 800, 1200);

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'Cropped Image Post',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
            'image' => $image,
        ])
        ->assertRedirect(route('posts.show', 'cropped-image-post'));

    $post = Post::query()->where('slug', 'cropped-image-post')->firstOrFail();
    $storedImage = imagecreatefromstring(Storage::disk('public')->get($post->image_path));

    expect($storedImage)->not->toBeFalse();
    expect(imagesx($storedImage))->toBe(1280);
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

test('guests cannot delete posts', function () {
    $post = Post::factory()->published()->create();

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('login'));

    expect(Post::query()->find($post->id))->not->toBeNull();
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
