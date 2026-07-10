<?php

use App\Http\Requests\StoreReplyCommentRequest;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('post show links author and commenter names to user profiles', function () {
    $author = User::factory()->create(['name' => 'Article Author']);
    $commenter = User::factory()->create(['name' => 'Comment Author']);
    $post = Post::factory()->published()->for($author)->create();
    Comment::factory()->for($post)->for($commenter)->create(['body' => 'Nice read']);

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee(route('users.show', $author), false)
        ->assertSee(route('users.show', $commenter), false);
});

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

test('authenticated users can reply to a comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $parentComment = Comment::factory()->for($post)->create([
        'body' => 'Original comment',
    ]);

    $this->actingAs($user)
        ->post(route('posts.comments.reply', [$post, $parentComment]), [
            'body' => 'Thanks for sharing!',
        ])
        ->assertRedirect(route('posts.show', $post)."#comment-{$parentComment->id}")
        ->assertSessionHas('status')
        ->assertSessionHas('show_replies_for', $parentComment->id);

    $reply = Comment::query()->where('parent_id', $parentComment->id)->first();

    expect($reply)->not->toBeNull();
    expect($reply->post_id)->toBe($post->id);
    expect($reply->user_id)->toBe($user->id);
    expect($reply->body)->toBe('Thanks for sharing!');

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Thanks for sharing!')
        ->assertSee('Hide replies')
        ->assertSee('Reply');
});

test('reply authorization allows string post_id values from the database', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $parentComment = Comment::factory()->for($post)->create();
    $parentComment->setAttribute('post_id', (string) $post->id);

    $request = StoreReplyCommentRequest::create('/', 'POST', ['body' => 'Thanks!']);
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => new class($post, $parentComment)
    {
        public function __construct(private Post $post, private Comment $comment) {}

        public function parameter(string $name): Post|Comment|null
        {
            return match ($name) {
                'post' => $this->post,
                'comment' => $this->comment,
                default => null,
            };
        }
    });

    expect($request->authorize())->toBeTrue();
});

test('users cannot reply to a comment on another post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $otherPost = Post::factory()->published()->create();
    $parentComment = Comment::factory()->for($otherPost)->create();

    $this->actingAs($user)
        ->post(route('posts.comments.reply', [$post, $parentComment]), [
            'body' => 'Sneaky reply',
        ])
        ->assertNotFound();

    expect(Comment::query()->where('body', 'Sneaky reply')->exists())->toBeFalse();
});

test('users cannot reply to a reply', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $parentComment = Comment::factory()->for($post)->create();
    $reply = Comment::factory()->for($post)->create([
        'parent_id' => $parentComment->id,
    ]);

    $this->actingAs($user)
        ->post(route('posts.comments.reply', [$post, $reply]), [
            'body' => 'Nested too deep',
        ])
        ->assertForbidden();

    expect(Comment::query()->where('body', 'Nested too deep')->exists())->toBeFalse();
});

test('top-level comments ignore any parent_id in the request', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $existingComment = Comment::factory()->for($post)->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => 'A new top-level comment',
            'parent_id' => $existingComment->id,
        ])
        ->assertRedirect();

    expect(Comment::query()->where('body', 'A new top-level comment')->first()?->parent_id)->toBeNull();
});

test('posting a top-level comment renders it once on the page', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => 'dasssad',
        ])
        ->assertRedirect();

    $comment = Comment::query()->first();

    $html = $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->getContent();

    expect(substr_count($html, 'id="comment-'.$comment->id.'"'))->toBe(1);
    expect(substr_count($html, 'dasssad'))->toBe(1);
});

test('posting a top-level comment does not create a self-reply', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => 'asddasdas',
        ])
        ->assertRedirect();

    expect(Comment::count())->toBe(1);

    $comment = Comment::query()->first();

    expect($comment->parent_id)->toBeNull();
    expect(Comment::query()->where('parent_id', $comment->id)->count())->toBe(0);
});

test('published posts show a reply button on own comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    Comment::factory()->for($post)->for($user)->create([
        'body' => 'My comment',
    ]);

    $this->actingAs($user)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Reply')
        ->assertSee('My comment');
});

test('published posts show a reply button on comments', function () {
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::factory()->published()->create();
    Comment::factory()->for($post)->for($author)->create([
        'body' => 'A comment from someone else',
    ]);

    $this->actingAs($viewer)
        ->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('Reply')
        ->assertSee('A comment from someone else');
});

test('duplicate top-level comment submissions are ignored', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), ['body' => 'Only once'])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), ['body' => 'Only once'])
        ->assertRedirect();

    expect(Comment::query()->count())->toBe(1);
});

test('users can reply to their own comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->for($post)->for($user)->create([
        'body' => 'My own comment',
    ]);

    $this->actingAs($user)
        ->post(route('posts.comments.reply', [$post, $comment]), [
            'body' => 'Replying to myself',
        ])
        ->assertRedirect(route('posts.show', $post)."#comment-{$comment->id}")
        ->assertSessionHas('status');

    expect(Comment::query()->where('parent_id', $comment->id)->count())->toBe(1);
});

test('deleting a parent comment deletes its replies', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $parentComment = Comment::factory()->for($post)->for($user)->create();
    $reply = Comment::factory()->for($post)->create([
        'parent_id' => $parentComment->id,
    ]);

    $this->actingAs($user)
        ->delete(route('posts.comments.destroy', [$post, $parentComment]))
        ->assertRedirect(route('posts.show', $post).'#comments');

    expect(Comment::query()->find($parentComment->id))->toBeNull();
    expect(Comment::query()->find($reply->id))->toBeNull();
});

test('root comments are paginated ten per page', function () {
    $post = Post::factory()->published()->create();

    $comments = Comment::factory()
        ->count(12)
        ->for($post)
        ->sequence(fn ($sequence) => [
            'body' => 'Comment '.($sequence->index + 1),
            'created_at' => now()->subMinutes(12 - $sequence->index),
        ])
        ->create();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('id="comment-'.$comments[0]->id.'"', false)
        ->assertSee('id="comment-'.$comments[9]->id.'"', false)
        ->assertDontSee('id="comment-'.$comments[10]->id.'"', false);

    $this->get(route('posts.show', ['post' => $post, 'page' => 2]))
        ->assertSuccessful()
        ->assertSee('id="comment-'.$comments[10]->id.'"', false)
        ->assertSee('id="comment-'.$comments[11]->id.'"', false)
        ->assertDontSee('id="comment-'.$comments[0]->id.'"', false);
});

test('most liked root comment is shown first', function () {
    $post = Post::factory()->published()->create();
    $leastLikedComment = Comment::factory()->for($post)->create([
        'body' => 'Least liked comment',
    ]);
    $mostLikedComment = Comment::factory()->for($post)->create([
        'body' => 'Most liked comment',
    ]);

    CommentVote::factory()->upvote()->count(2)->for($mostLikedComment)->create();
    CommentVote::factory()->upvote()->for($leastLikedComment)->create();

    $response = $this->get(route('posts.show', $post))
        ->assertSuccessful();

    $response->assertSeeInOrder([
        'id="comment-'.$mostLikedComment->id.'"',
        'id="comment-'.$leastLikedComment->id.'"',
    ], false);
});
