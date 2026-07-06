<?php

use App\Enums\CommentVoteType;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can upvote a comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();

    $this->actingAs($user)
        ->postJson(route('posts.comments.vote', [$post, $comment]), [
            'type' => 'up',
        ])
        ->assertSuccessful()
        ->assertJson([
            'vote' => 'up',
            'up_count' => 1,
            'down_count' => 0,
        ]);

    expect(CommentVote::query()->count())->toBe(1);
    expect(CommentVote::query()->first()->type)->toBe(CommentVoteType::Up);
});

test('authenticated users can downvote a comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();

    $this->actingAs($user)
        ->postJson(route('posts.comments.vote', [$post, $comment]), [
            'type' => 'down',
        ])
        ->assertSuccessful()
        ->assertJson([
            'vote' => 'down',
            'up_count' => 0,
            'down_count' => 1,
        ]);
});

test('users can remove their vote by pressing the same button again', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();
    CommentVote::factory()->for($comment)->for($user)->upvote()->create();

    $this->actingAs($user)
        ->postJson(route('posts.comments.vote', [$post, $comment]), [
            'type' => 'up',
        ])
        ->assertSuccessful()
        ->assertJson([
            'vote' => null,
            'up_count' => 0,
            'down_count' => 0,
        ]);

    expect(CommentVote::query()->count())->toBe(0);
});

test('users can switch from upvote to downvote', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();
    CommentVote::factory()->for($comment)->for($user)->upvote()->create();

    $this->actingAs($user)
        ->postJson(route('posts.comments.vote', [$post, $comment]), [
            'type' => 'down',
        ])
        ->assertSuccessful()
        ->assertJson([
            'vote' => 'down',
            'up_count' => 0,
            'down_count' => 1,
        ]);

    expect(CommentVote::query()->count())->toBe(1);
    expect(CommentVote::query()->first()->type)->toBe(CommentVoteType::Down);
});

test('each user can only have one vote per comment', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();

    $this->actingAs($firstUser)
        ->postJson(route('posts.comments.vote', [$post, $comment]), ['type' => 'up'])
        ->assertSuccessful();

    $this->actingAs($secondUser)
        ->postJson(route('posts.comments.vote', [$post, $comment]), ['type' => 'down'])
        ->assertSuccessful()
        ->assertJson([
            'vote' => 'down',
            'up_count' => 1,
            'down_count' => 1,
        ]);

    expect(CommentVote::query()->count())->toBe(2);
});

test('guests cannot vote on comments', function () {
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();

    $this->postJson(route('posts.comments.vote', [$post, $comment]), [
        'type' => 'up',
    ])->assertUnauthorized();

    expect(CommentVote::query()->count())->toBe(0);
});

test('users cannot vote on comments from unpublished posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->draft()->create();
    $comment = Comment::factory()->for($post)->create();

    $this->actingAs($user)
        ->postJson(route('posts.comments.vote', [$post, $comment]), [
            'type' => 'up',
        ])
        ->assertNotFound();

    expect(CommentVote::query()->count())->toBe(0);
});

test('comment votes are scoped to their post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $otherPost = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();

    $this->actingAs($user)
        ->postJson(route('posts.comments.vote', [$otherPost, $comment]), [
            'type' => 'up',
        ])
        ->assertNotFound();

    expect(CommentVote::query()->count())->toBe(0);
});

test('vote type is required', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create();

    $this->actingAs($user)
        ->postJson(route('posts.comments.vote', [$post, $comment]), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('type');
});

test('post show page displays comment vote controls and counts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create(['body' => 'A thoughtful comment']);
    CommentVote::factory()->for($comment)->for($user)->upvote()->create();

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('A thoughtful comment')
        ->assertSee('fa-solid fa-thumbs-up', false)
        ->assertSee('style="color: rgb(255, 212, 59);"', false)
        ->assertSee('fa-regular fa-thumbs-down', false);
});

test('guests see sign in links for comment votes', function () {
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->create(['body' => 'Visible comment']);

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Visible comment')
        ->assertSee('fa-regular fa-thumbs-up', false)
        ->assertSee('fa-regular fa-thumbs-down', false)
        ->assertSee(route('login'), false);
});
