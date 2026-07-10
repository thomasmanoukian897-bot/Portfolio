<?php

use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\PostLike;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserPostSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can view a user profile', function () {
    $user = User::factory()->create(['name' => 'Profile Owner']);
    Post::factory()->published()->for($user)->create(['title' => 'Visible Post']);

    $this->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee('Profile Owner')
        ->assertSee('Visible Post')
        ->assertDontSee('Edit profile')
        ->assertDontSee('View archive');
});

test('authenticated users see edit profile on their own profile', function () {
    $user = User::factory()->create(['name' => 'My Profile']);

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee('Edit profile')
        ->assertSee(route('profile.edit'), false);
});

test('users can follow and unfollow other users', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create(['name' => 'Follow Target']);

    $this->actingAs($follower)
        ->post(route('users.follow.toggle', $followed))
        ->assertRedirect(route('users.show', $followed))
        ->assertSessionHas('status');

    expect(UserFollow::query()->count())->toBe(1);

    $this->actingAs($follower)
        ->post(route('users.follow.toggle', $followed))
        ->assertRedirect(route('users.show', $followed));

    expect(UserFollow::query()->count())->toBe(0);
});

test('users cannot follow themselves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('users.follow.toggle', $user))
        ->assertForbidden();
});

test('profile shows post notification bell only when following', function () {
    $viewer = User::factory()->create();
    $profileUser = User::factory()->create(['name' => 'Bell Target']);

    $this->actingAs($viewer)
        ->get(route('users.show', $profileUser))
        ->assertSuccessful()
        ->assertDontSee(route('users.post-subscription.toggle', $profileUser), false)
        ->assertDontSee('fa-regular fa-bell', false);

    UserFollow::query()->create([
        'follower_id' => $viewer->id,
        'following_id' => $profileUser->id,
    ]);

    $this->actingAs($viewer)
        ->get(route('users.show', $profileUser))
        ->assertSuccessful()
        ->assertSee(route('users.post-subscription.toggle', $profileUser), false)
        ->assertSee('fa-regular fa-bell', false);

    UserPostSubscription::query()->create([
        'subscriber_id' => $viewer->id,
        'subscribed_to_id' => $profileUser->id,
    ]);

    $this->actingAs($viewer)
        ->get(route('users.show', $profileUser))
        ->assertSuccessful()
        ->assertSee('fa-solid fa-bell', false);
});

test('profile shows follower and following counts', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $following = User::factory()->create();

    UserFollow::query()->create([
        'follower_id' => $follower->id,
        'following_id' => $user->id,
    ]);

    UserFollow::query()->create([
        'follower_id' => $user->id,
        'following_id' => $following->id,
    ]);

    $this->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee('follower')
        ->assertSee('following');

    expect($user->fresh()->followers()->count())->toBe(1)
        ->and($user->fresh()->following()->count())->toBe(1);
});

test('profile sidebar link is visible to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('fa-user', false)
        ->assertSee(route('users.show', $user), false);
});

test('private likes and bookmarks are hidden from other users', function () {
    $owner = User::factory()->create(['likes_public' => false, 'bookmarks_public' => false]);
    $viewer = User::factory()->create();
    $likedPost = Post::factory()->published()->create(['title' => 'Secret Like']);
    $savedPost = Post::factory()->published()->create(['title' => 'Secret Bookmark']);

    PostLike::factory()->for($likedPost)->for($owner)->create();
    PostBookmark::factory()->for($savedPost)->for($owner)->create();

    $this->actingAs($viewer)
        ->get(route('users.show', ['user' => $owner, 'section' => 'liked']))
        ->assertSuccessful()
        ->assertDontSee('Secret Like')
        ->assertDontSee('aria-label="Liked posts"', false);

    $this->actingAs($viewer)
        ->get(route('users.show', ['user' => $owner, 'section' => 'bookmarks']))
        ->assertSuccessful()
        ->assertDontSee('Secret Bookmark')
        ->assertDontSee('aria-label="Bookmarked posts"', false);
});

test('public likes and bookmarks are visible to other users', function () {
    $owner = User::factory()->create(['likes_public' => true, 'bookmarks_public' => true]);
    $viewer = User::factory()->create();
    $likedPost = Post::factory()->published()->create(['title' => 'Public Like']);
    $savedPost = Post::factory()->published()->create(['title' => 'Public Bookmark']);

    PostLike::factory()->for($likedPost)->for($owner)->create();
    PostBookmark::factory()->for($savedPost)->for($owner)->create();

    $this->actingAs($viewer)
        ->get(route('users.show', ['user' => $owner, 'section' => 'liked']))
        ->assertSuccessful()
        ->assertSee('Public Like');

    $this->actingAs($viewer)
        ->get(route('users.show', ['user' => $owner, 'section' => 'bookmarks']))
        ->assertSuccessful()
        ->assertSee('Public Bookmark');
});

test('users can update privacy settings from profile settings tab', function () {
    $user = User::factory()->create([
        'likes_public' => false,
        'bookmarks_public' => false,
    ]);

    $this->actingAs($user)
        ->put(route('profile.privacy.update'), [
            'likes_public' => '1',
            'bookmarks_public' => '1',
        ])
        ->assertRedirect(route('profile.edit', ['tab' => 'settings']))
        ->assertSessionHas('status');

    $user->refresh();

    expect($user->likes_public)->toBeTrue()
        ->and($user->bookmarks_public)->toBeTrue();
});

test('profile settings tab is available on edit profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit', ['tab' => 'settings']))
        ->assertSuccessful()
        ->assertSee('Public likes')
        ->assertSee('Public bookmarks');
});

test('profile defaults to posts section for invalid section values', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->for($user)->create(['title' => 'Home Post']);

    $this->get(route('users.show', ['user' => $user, 'section' => 'invalid']))
        ->assertSuccessful()
        ->assertSee('Home Post');
});

test('guests see follow link that leads to login', function () {
    $user = User::factory()->create();

    $this->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee(route('login'), false);
});

test('profile page includes followers and following modal triggers', function () {
    $user = User::factory()->create();

    $this->get(route('users.show', $user))
        ->assertSuccessful()
        ->assertSee('data-user-connections-open="followers"', false)
        ->assertSee('data-user-connections-open="following"', false)
        ->assertSee(route('users.followers', $user), false)
        ->assertSee(route('users.following', $user), false);
});

test('followers endpoint returns users who follow the profile', function () {
    $user = User::factory()->create(['name' => 'Profile Owner']);
    $follower = User::factory()->create(['name' => 'Active Follower']);
    $other = User::factory()->create(['name' => 'Someone Else']);

    UserFollow::query()->create([
        'follower_id' => $follower->id,
        'following_id' => $user->id,
    ]);

    $this->getJson(route('users.followers', $user))
        ->assertSuccessful()
        ->assertJsonCount(1, 'users')
        ->assertJsonPath('users.0.name', 'Active Follower')
        ->assertJsonPath('users.0.handle', 'active-follower')
        ->assertJsonPath('users.0.profile_url', route('users.show', $follower));

    $this->getJson(route('users.followers', ['user' => $user, 'search' => 'Someone']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'users');
});

test('following endpoint returns users the profile follows', function () {
    $user = User::factory()->create(['name' => 'Profile Owner']);
    $followed = User::factory()->create(['name' => 'Followed Creator']);

    UserFollow::query()->create([
        'follower_id' => $user->id,
        'following_id' => $followed->id,
    ]);

    $this->getJson(route('users.following', $user))
        ->assertSuccessful()
        ->assertJsonCount(1, 'users')
        ->assertJsonPath('users.0.name', 'Followed Creator')
        ->assertJsonPath('users.0.profile_url', route('users.show', $followed));
});

test('connection endpoints include follow state for authenticated viewers', function () {
    $viewer = User::factory()->create();
    $user = User::factory()->create();
    $followed = User::factory()->create();

    UserFollow::query()->create([
        'follower_id' => $viewer->id,
        'following_id' => $followed->id,
    ]);

    UserFollow::query()->create([
        'follower_id' => $user->id,
        'following_id' => $followed->id,
    ]);

    $this->actingAs($viewer)
        ->getJson(route('users.following', $user))
        ->assertSuccessful()
        ->assertJsonPath('users.0.is_followed_by_viewer', true);
});
