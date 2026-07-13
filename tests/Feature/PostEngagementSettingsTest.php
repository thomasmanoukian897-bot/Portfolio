<?php

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create post form includes engagement settings', function () {
    $user = User::factory()->create();
    Category::factory()->create();

    $this->actingAs($user)
        ->get(route('posts.create'))
        ->assertSuccessful()
        ->assertSee('Allow comments')
        ->assertSee('Hide like count');
});

test('users can publish posts with comments disabled and likes hidden', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'Private Engagement Post',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
            'comments_enabled' => '0',
            'likes_hidden' => '1',
        ])
        ->assertRedirect(route('posts.show', 'private-engagement-post'));

    $post = Post::query()->where('slug', 'private-engagement-post')->first();

    expect($post)->not->toBeNull();
    expect($post->comments_enabled)->toBeFalse();
    expect($post->likes_hidden)->toBeTrue();
});

test('posts default to comments enabled and visible likes', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [
            'title' => 'Default Engagement Post',
            'content' => '<p>Hello world</p>',
            'category_ids' => [$category->id],
        ])
        ->assertRedirect();

    $post = Post::query()->where('slug', 'default-engagement-post')->first();

    expect($post->comments_enabled)->toBeTrue();
    expect($post->likes_hidden)->toBeFalse();
});

test('users cannot comment when comments are disabled on a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->commentsDisabled()->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => 'This should not be allowed.',
        ])
        ->assertForbidden();

    expect(Comment::query()->count())->toBe(0);
});

test('post show hides comment form when comments are disabled', function () {
    $post = Post::factory()->published()->commentsDisabled()->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Comments are turned off for this post.')
        ->assertDontSee('Add a comment')
        ->assertDontSee('Post comment');
});

test('post show hides like count when likes are hidden', function () {
    $post = Post::factory()->published()->likesHidden()->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertDontSee('data-like-count');
});

test('post cards hide like count when likes are hidden', function () {
    $post = Post::factory()->published()->likesHidden()->create();
    PostLike::factory()->count(3)->for($post)->create();

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertDontSee('aria-label="3 likes"', false);
});

test('post cards still show like count when likes are visible', function () {
    $post = Post::factory()->published()->create();
    PostLike::factory()->count(7)->for($post)->create();

    $this->get(route('posts.index'))
        ->assertSuccessful()
        ->assertSee('aria-label="7 likes"', false);
});
