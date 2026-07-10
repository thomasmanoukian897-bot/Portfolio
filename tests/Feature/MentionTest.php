<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostCommentedNotification;
use App\Notifications\UserMentionedNotification;
use App\Services\MentionParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('mention parser extracts unique handles from comment body', function () {
    $parser = app(MentionParser::class);

    expect($parser->extractHandles('Hey @jane-doe and @john-smith, also @jane-doe'))
        ->toBe(['jane-doe', 'john-smith']);
});

test('mention parser ignores invalid handles', function () {
    $parser = app(MentionParser::class);

    expect($parser->extractHandles('Email me at user@example.com or @Invalid_Handle'))
        ->toBe([]);
});

test('mention parser renders linked handles in comment body', function () {
    $user = User::factory()->create(['handle' => 'jane-doe']);
    $parser = app(MentionParser::class);

    $html = $parser->render('Thanks @jane-doe for the help!', collect([$user]));

    expect($html)
        ->toContain('href="'.route('users.show', $user).'"')
        ->toContain('@jane-doe')
        ->toContain('Thanks ');
});

test('mention parser escapes html in comment body', function () {
    $parser = app(MentionParser::class);

    expect($parser->render('<script>alert("xss")</script>', collect()))
        ->toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
});

test('commenting with a mention links the handle on the post page', function () {
    $commenter = User::factory()->create();
    $mentioned = User::factory()->create(['name' => 'Jane Doe', 'handle' => 'jane-doe']);
    $post = Post::factory()->published()->create();

    $this->actingAs($commenter)
        ->post(route('posts.comments.store', $post), [
            'body' => 'Great post @jane-doe!',
        ])
        ->assertRedirect();

    $this->get(route('posts.show', $post))
        ->assertSuccessful()
        ->assertSee('href="'.route('users.show', $mentioned).'"', false)
        ->assertSee('@jane-doe');
});

test('commenting with a mention records the mentioned user', function () {
    $commenter = User::factory()->create();
    $mentioned = User::factory()->create(['handle' => 'jane-doe']);
    $post = Post::factory()->published()->create();

    $this->actingAs($commenter)
        ->post(route('posts.comments.store', $post), [
            'body' => 'Hey @jane-doe',
        ])
        ->assertRedirect();

    $comment = Comment::query()->first();

    expect($comment->mentionedUsers()->pluck('users.id')->all())->toBe([$mentioned->id]);
});

test('mentioning a user sends them a notification', function () {
    Notification::fake();

    $author = User::factory()->create();
    $commenter = User::factory()->create();
    $mentioned = User::factory()->create(['handle' => 'mentioned-user']);
    $post = Post::factory()->published()->for($author)->create();

    $this->actingAs($commenter)
        ->post(route('posts.comments.store', $post), [
            'body' => 'Shout out to @mentioned-user',
        ])
        ->assertRedirect();

    Notification::assertSentTo($mentioned, UserMentionedNotification::class);
    Notification::assertSentTo($author, PostCommentedNotification::class);
});

test('mentioning yourself does not send a notification', function () {
    Notification::fake();

    $user = User::factory()->create(['handle' => 'my-handle']);
    $post = Post::factory()->published()->for($user)->create();

    $this->actingAs($user)
        ->post(route('posts.comments.store', $post), [
            'body' => 'Note to @my-handle',
        ])
        ->assertRedirect();

    Notification::assertNothingSent();
});

test('mentioning the post author does not send a duplicate mention notification', function () {
    Notification::fake();

    $author = User::factory()->create(['handle' => 'post-author']);
    $commenter = User::factory()->create();
    $post = Post::factory()->published()->for($author)->create();

    $this->actingAs($commenter)
        ->post(route('posts.comments.store', $post), [
            'body' => 'Thanks @post-author for writing this',
        ])
        ->assertRedirect();

    Notification::assertSentTo($author, PostCommentedNotification::class);
    Notification::assertNotSentTo($author, UserMentionedNotification::class);
});

test('replying with a mention notifies the mentioned user', function () {
    Notification::fake();

    $commenter = User::factory()->create();
    $mentioned = User::factory()->create(['handle' => 'reply-target']);
    $post = Post::factory()->published()->create();
    $parentComment = Comment::factory()->for($post)->create();

    $this->actingAs($commenter)
        ->post(route('posts.comments.reply', [$post, $parentComment]), [
            'body' => 'Hey @reply-target check this out',
        ])
        ->assertRedirect();

    Notification::assertSentTo($mentioned, UserMentionedNotification::class);
});

test('authenticated users can search users for mentions', function () {
    $viewer = User::factory()->create();
    $match = User::factory()->create(['name' => 'Jane Doe', 'handle' => 'jane-doe']);
    User::factory()->create(['name' => 'John Smith', 'handle' => 'john-smith']);

    $this->actingAs($viewer)
        ->getJson(route('users.search', ['q' => 'jane']))
        ->assertSuccessful()
        ->assertJsonPath('users.0.handle', 'jane-doe')
        ->assertJsonMissing(['handle' => 'john-smith']);
});

test('guests cannot search users for mentions', function () {
    $this->getJson(route('users.search', ['q' => 'jane']))
        ->assertUnauthorized();
});

test('notifications page displays mention notifications', function () {
    $user = User::factory()->create();
    $commenter = User::factory()->create(['name' => 'Morgan Mentioner', 'handle' => 'morgan-mentioner']);
    $post = Post::factory()->published()->create();
    $comment = $post->comments()->create([
        'user_id' => $commenter->id,
        'parent_id' => null,
        'body' => 'Hey @'.$user->handle.' take a look',
    ]);

    $user->notify(new UserMentionedNotification($commenter, $post, $comment));

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('morgan-mentioner')
        ->assertSee('mentioned you in a comment:')
        ->assertSee('Hey @'.$user->handle.' take a look');
});
