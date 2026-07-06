<?php

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can like a published post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'liked' => true,
            'count' => 1,
        ]);

    expect(PostLike::query()->count())->toBe(1);
    expect(PostLike::query()->first()->user_id)->toBe($user->id);
});

test('authenticated users can unlike a post they already liked', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    PostLike::factory()->for($post)->for($user)->create();

    $this->actingAs($user)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'liked' => false,
            'count' => 0,
        ]);

    expect(PostLike::query()->count())->toBe(0);
});

test('each user can only like a post once', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful();

    $this->actingAs($user)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'liked' => false,
            'count' => 0,
        ]);

    expect(PostLike::query()->count())->toBe(0);
});

test('multiple users can like the same post', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($firstUser)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'liked' => true,
            'count' => 1,
        ]);

    $this->actingAs($secondUser)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful()
        ->assertJson([
            'liked' => true,
            'count' => 2,
        ]);

    expect(PostLike::query()->count())->toBe(2);
});

test('guests cannot like posts', function () {
    $post = Post::factory()->published()->create();

    $this->postJson(route('posts.like.toggle', $post))
        ->assertUnauthorized();

    expect(PostLike::query()->count())->toBe(0);
});

test('users cannot like unpublished posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->draft()->create();

    $this->actingAs($user)
        ->postJson(route('posts.like.toggle', $post))
        ->assertNotFound();

    expect(PostLike::query()->count())->toBe(0);
});

test('post show page displays like count and heart icon', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    PostLike::factory()->for($post)->for($user)->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('fa-regular fa-heart', false)
        ->assertSee('1', false);
});

test('post show page shows filled heart when user has liked the post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    PostLike::factory()->for($post)->for($user)->create();

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('fa-solid fa-heart', false)
        ->assertSee('style="color: rgb(255, 0, 0);"', false);
});

test('guests see sign in link for likes on post show page', function () {
    $post = Post::factory()->published()->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee(route('login'), false)
        ->assertSee('fa-regular fa-heart', false);
});
