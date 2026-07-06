<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SquadsController;
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

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::middleware('auth')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post:slug}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('posts.comments.store');
    Route::delete('/posts/{post:slug}/comments/{comment}', [CommentController::class, 'destroy'])
        ->scopeBindings()
        ->name('posts.comments.destroy');
    Route::post('/posts/{post:slug}/like', [PostLikeController::class, 'toggle'])
        ->middleware('throttle:30,1')
        ->name('posts.like.toggle');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'requestPasswordChange'])
        ->middleware('throttle:3,1')
        ->name('profile.password.request');
    Route::post('/profile/password/verify', [ProfileController::class, 'verifyPasswordChange'])
        ->middleware('throttle:5,1')
        ->name('profile.password.verify');
});

Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
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
    });
