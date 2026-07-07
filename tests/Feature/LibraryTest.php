<?php

use App\Models\Post;
use App\Models\PostBookmark;
use App\Models\PostLike;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access the library', function () {
    $this->get(route('library.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view their own posts in the library', function () {
    $user = User::factory()->create();
    $ownPost = Post::factory()->published()->for($user)->create(['title' => 'My Published Post']);
    Post::factory()->published()->create(['title' => 'Someone Elses Post']);

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'posts']))
        ->assertSuccessful()
        ->assertSee('My Published Post')
        ->assertDontSee('Someone Elses Post');
});

test('authenticated users can view liked posts in the library', function () {
    $user = User::factory()->create();
    $likedPost = Post::factory()->published()->create(['title' => 'Liked Article']);
    $otherPost = Post::factory()->published()->create(['title' => 'Not Liked Article']);

    PostLike::factory()->for($likedPost)->for($user)->create();

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'liked']))
        ->assertSuccessful()
        ->assertSee('Liked Article')
        ->assertDontSee('Not Liked Article');
});

test('authenticated users can view bookmarked posts in the library', function () {
    $user = User::factory()->create();
    $bookmarkedPost = Post::factory()->published()->create(['title' => 'Saved Article']);
    $otherPost = Post::factory()->published()->create(['title' => 'Unsaved Article']);

    PostBookmark::factory()->for($bookmarkedPost)->for($user)->create();

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'bookmarks']))
        ->assertSuccessful()
        ->assertSee('Saved Article')
        ->assertDontSee('Unsaved Article');
});

test('authenticated users can view their bookings in the library', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $booking = Reservation::factory()->forUser($user)->create([
        'starts_at' => now()->addDays(3)->setTime(10, 0),
        'ends_at' => now()->addDays(3)->setTime(11, 0),
        'notes' => 'Discuss portfolio redesign.',
    ]);

    Reservation::factory()->forUser($otherUser)->create([
        'notes' => 'Someone elses booking.',
    ]);

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'bookings']))
        ->assertSuccessful()
        ->assertSee('Your Bookings')
        ->assertSee('Discuss portfolio redesign.')
        ->assertSee('Upcoming')
        ->assertDontSee('Someone elses booking.');
});

test('library sidebar links are visible to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('data-library-dropdown-toggle', false)
        ->assertSee('href="'.route('library.index').'"', false)
        ->assertSee(route('library.index', ['section' => 'posts']), false)
        ->assertSee(route('library.index', ['section' => 'liked']), false)
        ->assertSee(route('library.index', ['section' => 'bookmarks']), false)
        ->assertSee(route('library.index', ['section' => 'bookings']), false)
        ->assertSee('fa-book-bookmark', false);
});

test('library sidebar links are hidden from guests', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee(route('library.index', ['section' => 'bookmarks']), false);
});

test('library defaults to the posts section for invalid section values', function () {
    $user = User::factory()->create();
    $ownPost = Post::factory()->published()->for($user)->create(['title' => 'Default Section Post']);

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'invalid']))
        ->assertSuccessful()
        ->assertSee('Your Posts')
        ->assertSee('Default Section Post');
});

test('library filter buttons show section counts', function () {
    $user = User::factory()->create();
    Post::factory()->published()->for($user)->count(2)->create();
    Post::factory()->published()->count(3)->create()->each(function (Post $post) use ($user): void {
        PostLike::factory()->for($post)->for($user)->create();
    });
    Post::factory()->published()->count(1)->create()->each(function (Post $post) use ($user): void {
        PostBookmark::factory()->for($post)->for($user)->create();
    });
    Reservation::factory()->forUser($user)->count(4)->create();

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'posts']))
        ->assertSuccessful()
        ->assertSee('Your Posts')
        ->assertSee('(2)', false)
        ->assertSee('Liked Posts')
        ->assertSee('(3)', false)
        ->assertSee('Your Bookmarks')
        ->assertSee('(1)', false)
        ->assertSee('Your Bookings')
        ->assertSee('(4)', false);
});

test('library shows empty state when user has no posts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'posts']))
        ->assertSuccessful()
        ->assertSee('You have not published any posts yet.');
});

test('library shows empty state when user has no bookings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'bookings']))
        ->assertSuccessful()
        ->assertSee('You have not booked any sessions yet.')
        ->assertSee(route('reservations.index'), false);
});

test('authenticated users can delete their bookings from the library', function () {
    $user = User::factory()->create();
    $booking = Reservation::factory()->forUser($user)->create();

    $this->actingAs($user)
        ->delete(route('reservations.destroy', $booking))
        ->assertRedirect(route('library.index', ['section' => 'bookings']))
        ->assertSessionHas('status');

    expect(Reservation::query()->count())->toBe(0);
});

test('users cannot delete another users booking', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $booking = Reservation::factory()->forUser($otherUser)->create();

    $this->actingAs($user)
        ->delete(route('reservations.destroy', $booking))
        ->assertForbidden();

    expect(Reservation::query()->count())->toBe(1);
});

test('library booking cards show a delete button for owned bookings', function () {
    $user = User::factory()->create();
    Reservation::factory()->forUser($user)->create();

    $this->actingAs($user)
        ->get(route('library.index', ['section' => 'bookings']))
        ->assertSuccessful()
        ->assertSee(route('reservations.destroy', Reservation::query()->first()), false)
        ->assertSee('fa-trash', false);
});
