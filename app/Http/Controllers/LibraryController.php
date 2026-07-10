<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $section = $request->query('section', 'posts');

        $allowedSections = ['posts', 'liked', 'bookmarks', 'history', 'bookings'];

        if (! in_array($section, $allowedSections, true)) {
            $section = 'posts';
        }

        $user = $request->user();

        $viewData = [
            'section' => $section,
            'sectionCounts' => $this->sectionCounts($user),
        ];

        if ($section === 'bookings') {
            return view('library.index', [
                ...$viewData,
                'bookings' => $user->reservations()
                    ->latest('starts_at')
                    ->paginate(9)
                    ->withQueryString(),
            ]);
        }

        $postsQuery = match ($section) {
            'liked' => Post::query()
                ->published()
                ->join('post_likes', 'posts.id', '=', 'post_likes.post_id')
                ->where('post_likes.user_id', $user->id)
                ->select('posts.*')
                ->orderByDesc('post_likes.created_at'),
            'bookmarks' => Post::query()
                ->published()
                ->join('post_bookmarks', 'posts.id', '=', 'post_bookmarks.post_id')
                ->where('post_bookmarks.user_id', $user->id)
                ->select('posts.*')
                ->orderByDesc('post_bookmarks.created_at'),
            'history' => Post::query()
                ->published()
                ->join('post_views', 'posts.id', '=', 'post_views.post_id')
                ->where('post_views.viewer_identifier', 'user:'.$user->id)
                ->select('posts.*')
                ->orderByDesc('post_views.created_at'),
            default => $user->posts()->latest('created_at'),
        };

        return view('library.index', [
            ...$viewData,
            'posts' => $postsQuery
                ->with(['user', 'categories'])
                ->withCount(['likes', 'comments'])
                ->paginate(9)
                ->withQueryString(),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function sectionCounts(User $user): array
    {
        return [
            'posts' => $user->posts()->count(),
            'liked' => Post::query()
                ->published()
                ->join('post_likes', 'posts.id', '=', 'post_likes.post_id')
                ->where('post_likes.user_id', $user->id)
                ->count(),
            'bookmarks' => Post::query()
                ->published()
                ->join('post_bookmarks', 'posts.id', '=', 'post_bookmarks.post_id')
                ->where('post_bookmarks.user_id', $user->id)
                ->count(),
            'history' => Post::query()
                ->published()
                ->join('post_views', 'posts.id', '=', 'post_views.post_id')
                ->where('post_views.viewer_identifier', 'user:'.$user->id)
                ->count(),
            'bookings' => $user->reservations()->count(),
        ];
    }
}
