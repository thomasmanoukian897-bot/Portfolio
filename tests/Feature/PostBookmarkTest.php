<?php

use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can bookmark a published post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->postJson(route('posts.bookmark.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'bookmarked' => true,
        ]);

    expect(PostBookmark::query()->count())->toBe(1);
    expect(PostBookmark::query()->first()->user_id)->toBe($user->id);
});

test('authenticated users can remove a bookmark from a post they saved', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    PostBookmark::factory()->for($post)->for($user)->create();

    $this->actingAs($user)
        ->postJson(route('posts.bookmark.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'bookmarked' => false,
        ]);

    expect(PostBookmark::query()->count())->toBe(0);
});

test('each user can only bookmark a post once', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->postJson(route('posts.bookmark.toggle', $post))
        ->assertSuccessful();

    $this->actingAs($user)
        ->postJson(route('posts.bookmark.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'bookmarked' => false,
        ]);

    expect(PostBookmark::query()->count())->toBe(0);
});

test('guests cannot bookmark posts', function () {
    $post = Post::factory()->published()->create();

    $this->postJson(route('posts.bookmark.toggle', $post))
        ->assertUnauthorized();

    expect(PostBookmark::query()->count())->toBe(0);
});

test('users cannot bookmark unpublished posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->draft()->create();

    $this->actingAs($user)
        ->postJson(route('posts.bookmark.toggle', $post))
        ->assertNotFound();

    expect(PostBookmark::query()->count())->toBe(0);
});

test('post show page displays bookmark icon', function () {
    $post = Post::factory()->published()->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('fa-regular fa-bookmark', false);
});

test('post show page shows filled bookmark when user has saved the post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    PostBookmark::factory()->for($post)->for($user)->create();

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('fa-solid fa-bookmark', false)
        ->assertSee('style="color: rgb(255, 212, 59);"', false);
});

test('guests see sign in link for bookmarks on post show page', function () {
    $post = Post::factory()->published()->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Sign in to save', false)
        ->assertSee('fa-regular fa-bookmark', false);
});
