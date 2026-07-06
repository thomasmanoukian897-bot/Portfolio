<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can comment on published posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => 'Great article, thanks for sharing!',
        ])
        ->assertRedirect(route('posts.show', $post).'#comments')
        ->assertSessionHas('status');

    $comment = Comment::query()->first();

    expect($comment)->not->toBeNull();
    expect($comment->post_id)->toBe($post->id);
    expect($comment->user_id)->toBe($user->id);
    expect($comment->body)->toBe('Great article, thanks for sharing!');

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Great article, thanks for sharing!')
        ->assertSee($user->name);
});

test('guests cannot post comments', function () {
    $post = Post::factory()->published()->create();

    $this->post(route('posts.comments.store', $post), [
        'body' => 'I should not be able to post this.',
    ])
        ->assertRedirect(route('login'));

    expect(Comment::query()->count())->toBe(0);
});

test('users cannot comment on unpublished posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->draft()->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => 'This should not work.',
        ])
        ->assertForbidden();

    expect(Comment::query()->count())->toBe(0);
});

test('comment body is required', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => '',
        ])
        ->assertSessionHasErrors('body');

    expect(Comment::query()->count())->toBe(0);
});

test('comment authors can delete their own comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->for($user)->create([
        'body' => 'My comment',
    ]);

    $this->actingAs($user)
        ->delete(route('posts.comments.destroy', [$post, $comment]))
        ->assertRedirect(route('posts.show', $post).'#comments')
        ->assertSessionHas('status');

    expect(Comment::query()->find($comment->id))->toBeNull();
});

test('post authors can delete comments on their posts', function () {
    $author = User::factory()->create();
    $commenter = User::factory()->create();
    $post = Post::factory()->for($author)->published()->create();
    $comment = Comment::factory()->for($post)->for($commenter)->create([
        'body' => 'A comment on the post',
    ]);

    $this->actingAs($author)
        ->delete(route('posts.comments.destroy', [$post, $comment]))
        ->assertRedirect(route('posts.show', $post).'#comments');

    expect(Comment::query()->find($comment->id))->toBeNull();
});

test('admins can delete any comment', function () {
    $admin = User::factory()->admin()->create();
    $commenter = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->for($commenter)->create();

    $this->actingAs($admin)
        ->delete(route('posts.comments.destroy', [$post, $comment]))
        ->assertRedirect(route('posts.show', $post).'#comments');

    expect(Comment::query()->find($comment->id))->toBeNull();
});

test('users cannot delete comments they do not own', function () {
    $commenter = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->for($commenter)->create();

    $this->actingAs($otherUser)
        ->delete(route('posts.comments.destroy', [$post, $comment]))
        ->assertForbidden();

    expect(Comment::query()->find($comment->id))->not->toBeNull();
});

test('comments are scoped to their post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $otherPost = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->for($user)->create();

    $this->actingAs($user)
        ->delete(route('posts.comments.destroy', [$otherPost, $comment]))
        ->assertNotFound();

    expect(Comment::query()->find($comment->id))->not->toBeNull();
});

test('guests see a sign in prompt instead of the comment form', function () {
    $post = Post::factory()->published()->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Sign in')
        ->assertDontSee('Post comment');
});

test('authenticated users see the comment form on published posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Add a comment')
        ->assertSee('Post comment');
});
