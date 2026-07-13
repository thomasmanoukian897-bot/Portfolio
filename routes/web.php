<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentVoteController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostBookmarkController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserBlockController;
use App\Http\Controllers\UserConnectionController;
use App\Http\Controllers\UserFollowController;
use App\Http\Controllers\UserPostSubscriptionController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('home');

Route::get('/services', function () {
    return view('services');
})->name('services');

Route::get('/portfolio', function () {
    return view('portfolio');
})->name('portfolio');

Route::get('/squads', [SquadsController::class, 'index'])->name('squads');

Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
Route::post('/reservations', [ReservationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('reservations.store');

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/today', [PostController::class, 'today'])->name('posts.today');

Route::middleware('auth')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post:slug}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('posts.comments.store');
    Route::post('/posts/{post:slug}/comments/{comment}/replies', [CommentController::class, 'reply'])
        ->scopeBindings()
        ->middleware('throttle:10,1')
        ->name('posts.comments.reply');
    Route::delete('/posts/{post:slug}/comments/{comment}', [CommentController::class, 'destroy'])
        ->scopeBindings()
        ->name('posts.comments.destroy');
    Route::post('/posts/{post:slug}/comments/{comment}/vote', [CommentVoteController::class, 'store'])
        ->scopeBindings()
        ->middleware('throttle:60,1')
        ->name('posts.comments.vote');
    Route::post('/posts/{post:slug}/like', [PostLikeController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('posts.like.toggle');
    Route::post('/posts/{post:slug}/bookmark', [PostBookmarkController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('posts.bookmark.toggle');

    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::get('/messages', [ConversationController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [ConversationController::class, 'show'])->name('messages.show');
    Route::post('/messages', [ConversationController::class, 'storeDirect'])
        ->middleware('throttle:30,1')
        ->name('messages.store');
    Route::post('/messages/groups', [ConversationController::class, 'storeGroup'])
        ->middleware('throttle:10,1')
        ->name('messages.groups.store');
    Route::patch('/messages/{conversation}/avatar', [ConversationController::class, 'updateAvatar'])
        ->middleware('throttle:10,1')
        ->name('messages.groups.avatar.update');
    Route::patch('/messages/{conversation}/name', [ConversationController::class, 'updateName'])
        ->middleware('throttle:10,1')
        ->name('messages.groups.name.update');
    Route::delete('/messages/{conversation}/members/{user}', [ConversationController::class, 'kickMember'])
        ->middleware('throttle:10,1')
        ->name('messages.groups.members.kick');
    Route::delete('/messages/{conversation}/leave', [ConversationController::class, 'leave'])
        ->middleware('throttle:10,1')
        ->name('messages.groups.leave');
    Route::patch('/messages/{conversation}/notifications', [ConversationController::class, 'toggleNotifications'])
        ->middleware('throttle:30,1')
        ->name('messages.notifications.toggle');
    Route::post('/messages/{conversation}/accept', [ConversationController::class, 'acceptRequest'])
        ->middleware('throttle:30,1')
        ->name('messages.requests.accept');
    Route::delete('/messages/{conversation}/request', [ConversationController::class, 'declineRequest'])
        ->middleware('throttle:30,1')
        ->name('messages.requests.decline');
    Route::get('/messages/{conversation}/messages', [MessageController::class, 'index'])
        ->middleware('throttle:120,1')
        ->name('messages.messages.index');
    Route::post('/messages/{conversation}/messages', [MessageController::class, 'store'])
        ->middleware('throttle:60,1')
        ->name('messages.messages.store');

    Route::get('/users/search', UserSearchController::class)
        ->middleware('throttle:60,1')
        ->name('users.search');

    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/privacy', [ProfileController::class, 'updatePrivacy'])->name('profile.privacy.update');
    Route::post('/users/{user}/follow', [UserFollowController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('users.follow.toggle');
    Route::post('/users/{user}/block', [UserBlockController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('users.block');
    Route::delete('/users/{user}/block', [UserBlockController::class, 'destroy'])
        ->middleware('throttle:30,1')
        ->name('users.block.destroy');
    Route::post('/users/{user}/subscribe', [UserPostSubscriptionController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('users.post-subscription.toggle');
    Route::post('/profile/password', [ProfileController::class, 'requestPasswordChange'])
        ->middleware('throttle:3,1')
        ->name('profile.password.request');
    Route::post('/profile/password/verify', [ProfileController::class, 'verifyPasswordChange'])
        ->middleware('throttle:5,1')
        ->name('profile.password.verify');
});

Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/users/{user}', [UserProfileController::class, 'show'])->name('users.show');
Route::get('/users/{user}/followers', [UserConnectionController::class, 'followers'])->name('users.followers');
Route::get('/users/{user}/following', [UserConnectionController::class, 'following'])->name('users.following');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('posts', AdminPostController::class)->except(['show']);
        Route::resource('bookings', BookingController::class)->only(['index', 'edit', 'update', 'destroy'])->parameters(['bookings' => 'reservation']);
    });
