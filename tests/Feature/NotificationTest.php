<?php

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Post;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserPostSubscription;
use App\Notifications\NewMessageNotification;
use App\Notifications\PostCommentedNotification;
use App\Notifications\PostLikedNotification;
use App\Notifications\UserFollowedNotification;
use App\Notifications\UserPostedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function unreadNotificationDotCountInSidebar(string $html): int
{
    if (! preg_match('/<aside[^>]*data-mobile-drawer[^>]*>.*?<\/aside>/s', $html, $matches)) {
        return 0;
    }

    return substr_count($matches[0], 'data-unread-notification-dot');
}

test('guests cannot access notifications', function () {
    $this->get(route('notifications.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view their notifications page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Notifications')
        ->assertSee('No notifications yet');
});

test('following a user creates a follow notification', function () {
    Notification::fake();

    $follower = User::factory()->create(['name' => 'Jane Follower']);
    $followed = User::factory()->create(['name' => 'John Followed']);

    $this->actingAs($follower)
        ->post(route('users.follow.toggle', $followed))
        ->assertRedirect(route('users.show', $followed));

    Notification::assertSentTo($followed, UserFollowedNotification::class);
});

test('following a user again after unfollowing does not create another follow notification', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($follower)
        ->post(route('users.follow.toggle', $followed));

    $this->actingAs($follower)
        ->post(route('users.follow.toggle', $followed));

    $this->actingAs($follower)
        ->post(route('users.follow.toggle', $followed));

    expect($followed->fresh()->notifications()->count())->toBe(1);
});

test('liking a post creates a notification for the post author', function () {
    Notification::fake();

    $author = User::factory()->create();
    $liker = User::factory()->create();
    $post = Post::factory()->published()->for($author)->create();

    $this->actingAs($liker)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful();

    Notification::assertSentTo($author, PostLikedNotification::class);
});

test('liking your own post does not create a notification', function () {
    Notification::fake();

    $author = User::factory()->create();
    $post = Post::factory()->published()->for($author)->create();

    $this->actingAs($author)
        ->postJson(route('posts.like.toggle', $post))
        ->assertSuccessful();

    Notification::assertNothingSent();
});

test('commenting on a post creates a notification for the post author', function () {
    Notification::fake();

    $author = User::factory()->create();
    $commenter = User::factory()->create();
    $post = Post::factory()->published()->for($author)->create();

    $this->actingAs($commenter)
        ->post(route('posts.comments.store', $post), ['body' => 'Great article!'])
        ->assertRedirect();

    Notification::assertSentTo($author, PostCommentedNotification::class);
});

test('mentioning a user in a comment creates a notification in the notifications tab', function () {
    $author = User::factory()->create();
    $commenter = User::factory()->create();
    $mentioned = User::factory()->create(['name' => 'Tagged User', 'handle' => 'tagged-user']);
    $post = Post::factory()->published()->for($author)->create();

    $this->actingAs($commenter)
        ->post(route('posts.comments.store', $post), [
            'body' => 'Hey @tagged-user check this out',
        ])
        ->assertRedirect();

    $notification = $mentioned->fresh()->notifications->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['type'])->toBe('user_mentioned');

    $this->actingAs($mentioned)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('tagged-user')
        ->assertSee('mentioned you in a comment:')
        ->assertSee('Hey @tagged-user check this out');
});

test('replying to a comment creates a notification for the post author', function () {
    Notification::fake();

    $author = User::factory()->create();
    $commenter = User::factory()->create();
    $post = Post::factory()->published()->for($author)->create();
    $rootComment = $post->comments()->create([
        'user_id' => $author->id,
        'parent_id' => null,
        'body' => 'Thanks for reading.',
    ]);

    $this->actingAs($commenter)
        ->post(route('posts.comments.reply', [$post, $rootComment]), ['body' => 'Glad you liked it.'])
        ->assertRedirect();

    Notification::assertSentTo($author, PostCommentedNotification::class);
});

test('commenting on your own post does not create a notification', function () {
    Notification::fake();

    $author = User::factory()->create();
    $post = Post::factory()->published()->for($author)->create();

    $this->actingAs($author)
        ->post(route('posts.comments.store', $post), ['body' => 'My own comment'])
        ->assertRedirect();

    Notification::assertNothingSent();
});

test('notifications page displays comment notifications with comment preview', function () {
    $user = User::factory()->create();
    $commenter = User::factory()->create(['name' => 'Casey Commenter']);
    $post = Post::factory()->published()->for($user)->create();
    $comment = $post->comments()->create([
        'user_id' => $commenter->id,
        'parent_id' => null,
        'body' => 'This is a really insightful post.',
    ]);

    $user->notify(new PostCommentedNotification($commenter, $post, $comment));

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('casey-commenter')
        ->assertSee('commented on your post:')
        ->assertSee('This is a really insightful post.');
});

test('notifications page displays message notifications', function () {
    $recipient = User::factory()->create();
    $sender = User::factory()->create(['name' => 'Morgan Messenger']);
    $conversation = Conversation::findOrCreateDirect($sender, $recipient);
    $message = $conversation->messages()->create([
        'user_id' => $sender->id,
        'body' => 'Hey, are you free tomorrow?',
    ]);

    $recipient->notify(new NewMessageNotification($sender, $conversation, $message));

    $this->actingAs($recipient)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('morgan-messenger')
        ->assertSee('has sent you a message')
        ->assertDontSee('sent you a notification');
});

test('notifications page displays follow notifications grouped by period', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create(['name' => 'Alex Rivera']);

    UserFollow::query()->create([
        'follower_id' => $follower->id,
        'following_id' => $user->id,
    ]);

    $user->notify(new UserFollowedNotification($follower));

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Today')
        ->assertSee('alex-rivera')
        ->assertSee('started following you')
        ->assertSee('Follow Back');
});

test('notifications page displays liked post notifications with post thumbnail', function () {
    $user = User::factory()->create();
    $liker = User::factory()->create(['name' => 'Sam Liker']);
    $post = Post::factory()->published()->for($user)->create([
        'title' => 'Featured Article',
        'image_path' => 'posts/test-image.jpg',
    ]);

    $user->notify(new PostLikedNotification($liker, $post));

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('sam-liker')
        ->assertSee('liked your post')
        ->assertSee($post->featuredImageUrl(), false);
});

test('viewing notifications marks them as read', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    $user->notify(new UserFollowedNotification($follower));

    expect($user->unreadNotifications()->count())->toBe(1);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notifications sidebar link is visible to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('href="'.route('notifications.index').'"', false)
        ->assertSee('fa-bell', false)
        ->assertSee('Notifications');
});

test('notifications sidebar shows a red dot when the user has unread notifications', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    $user->notify(new UserFollowedNotification($follower));

    $response = $this->actingAs($user)->get(route('home'));

    expect(unreadNotificationDotCountInSidebar($response->getContent()))->toBe(1);
});

test('notifications sidebar hides the red dot when all notifications are read', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    $user->notify(new UserFollowedNotification($follower));
    $user->unreadNotifications->markAsRead();

    $response = $this->actingAs($user)->get(route('home'));

    expect(unreadNotificationDotCountInSidebar($response->getContent()))->toBe(0);
});

test('visiting the notifications page clears the unread dot', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    $user->notify(new UserFollowedNotification($follower));

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful();

    $response = $this->get(route('home'));

    expect(unreadNotificationDotCountInSidebar($response->getContent()))->toBe(0);
});

test('notifications sidebar link is hidden from guests', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee(route('notifications.index'), false);
});

test('follow back button shows following state when already following', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    UserFollow::query()->create([
        'follower_id' => $follower->id,
        'following_id' => $user->id,
    ]);

    UserFollow::query()->create([
        'follower_id' => $user->id,
        'following_id' => $follower->id,
    ]);

    $user->notify(new UserFollowedNotification($follower));

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Following')
        ->assertDontSee('Follow Back');
});

test('follow back from notifications stays on notifications page', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    UserFollow::query()->create([
        'follower_id' => $follower->id,
        'following_id' => $user->id,
    ]);

    $user->notify(new UserFollowedNotification($follower));

    $this->actingAs($user)
        ->post(route('users.follow.toggle', $follower), ['from_notifications' => 1])
        ->assertRedirect(route('notifications.index'));

    expect(UserFollow::query()
        ->where('follower_id', $user->id)
        ->where('following_id', $follower->id)
        ->exists())->toBeTrue();

    $this->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Following')
        ->assertDontSee('Follow Back');
});

test('unfollowing from notifications shows follow back again', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    UserFollow::query()->create([
        'follower_id' => $follower->id,
        'following_id' => $user->id,
    ]);

    UserFollow::query()->create([
        'follower_id' => $user->id,
        'following_id' => $follower->id,
    ]);

    $user->notify(new UserFollowedNotification($follower));

    $this->actingAs($user)
        ->post(route('users.follow.toggle', $follower), ['from_notifications' => 1])
        ->assertRedirect(route('notifications.index'));

    expect(UserFollow::query()
        ->where('follower_id', $user->id)
        ->where('following_id', $follower->id)
        ->exists())->toBeFalse();

    $this->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Follow Back')
        ->assertDontSee('>Following<', false);
});

test('subscribing to post notifications toggles subscription', function () {
    $subscriber = User::factory()->create();
    $author = User::factory()->create(['name' => 'Post Author']);

    UserFollow::query()->create([
        'follower_id' => $subscriber->id,
        'following_id' => $author->id,
    ]);

    $this->actingAs($subscriber)
        ->post(route('users.post-subscription.toggle', $author))
        ->assertRedirect(route('users.show', $author))
        ->assertSessionHas('status');

    expect(UserPostSubscription::query()->count())->toBe(1);

    $this->actingAs($subscriber)
        ->post(route('users.post-subscription.toggle', $author))
        ->assertRedirect(route('users.show', $author));

    expect(UserPostSubscription::query()->count())->toBe(0);
});

test('users cannot subscribe to post notifications without following', function () {
    $subscriber = User::factory()->create();
    $author = User::factory()->create();

    $this->actingAs($subscriber)
        ->post(route('users.post-subscription.toggle', $author))
        ->assertForbidden();

    expect(UserPostSubscription::query()->count())->toBe(0);
});

test('unfollowing removes post notification subscription', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    UserFollow::query()->create([
        'follower_id' => $follower->id,
        'following_id' => $followed->id,
    ]);

    UserPostSubscription::query()->create([
        'subscriber_id' => $follower->id,
        'subscribed_to_id' => $followed->id,
    ]);

    $this->actingAs($follower)
        ->post(route('users.follow.toggle', $followed))
        ->assertRedirect(route('users.show', $followed));

    expect(UserPostSubscription::query()->count())->toBe(0);
});

test('users cannot subscribe to their own posts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('users.post-subscription.toggle', $user))
        ->assertForbidden();
});

test('publishing a post notifies subscribers', function () {
    Notification::fake();

    $author = User::factory()->create();
    $subscriber = User::factory()->create();
    $nonSubscriber = User::factory()->create();
    $category = Category::factory()->create();

    UserPostSubscription::query()->create([
        'subscriber_id' => $subscriber->id,
        'subscribed_to_id' => $author->id,
    ]);

    $this->actingAs($author)
        ->post(route('posts.store'), [
            'title' => 'New Story Post',
            'content' => '<p>Hello subscribers</p>',
            'category_ids' => [$category->id],
        ])
        ->assertRedirect();

    Notification::assertSentTo($subscriber, UserPostedNotification::class);
    Notification::assertNotSentTo($nonSubscriber, UserPostedNotification::class);
});

test('notifications page displays new post notifications from subscribed users', function () {
    $subscriber = User::factory()->create();
    $author = User::factory()->create(['name' => 'Story Author', 'handle' => 'story-author']);
    $post = Post::factory()->published()->for($author)->create([
        'title' => 'Fresh Story',
        'image_path' => 'posts/test-image.jpg',
    ]);

    $subscriber->notify(new UserPostedNotification($author, $post));

    $this->actingAs($subscriber)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('story-author')
        ->assertSee('published a new post')
        ->assertSee($post->featuredImageUrl(), false);
});
