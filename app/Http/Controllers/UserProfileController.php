<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();
        $isOwnProfile = $viewer?->is($user) ?? false;

        $allowedSections = ['posts'];

        if ($isOwnProfile || $user->likes_public) {
            $allowedSections[] = 'liked';
        }

        if ($isOwnProfile || $user->bookmarks_public) {
            $allowedSections[] = 'bookmarks';
        }

        $section = $request->string('section')->toString();
        if (! in_array($section, $allowedSections, true)) {
            $section = 'posts';
        }

        $user->loadCount([
            'posts' => fn ($query) => $query->published(),
            'followers',
            'following',
        ]);

        $posts = match ($section) {
            'liked' => Post::query()
                ->published()
                ->join('post_likes', 'posts.id', '=', 'post_likes.post_id')
                ->where('post_likes.user_id', $user->id)
                ->select('posts.*')
                ->with(['user', 'categories'])
                ->withCount(['likes', 'comments'])
                ->orderByDesc('post_likes.created_at')
                ->paginate(12)
                ->withQueryString(),
            'bookmarks' => Post::query()
                ->published()
                ->join('post_bookmarks', 'posts.id', '=', 'post_bookmarks.post_id')
                ->where('post_bookmarks.user_id', $user->id)
                ->select('posts.*')
                ->with(['user', 'categories'])
                ->withCount(['likes', 'comments'])
                ->orderByDesc('post_bookmarks.created_at')
                ->paginate(12)
                ->withQueryString(),
            default => $user->posts()
                ->published()
                ->with(['user', 'categories'])
                ->withCount(['likes', 'comments'])
                ->latest('published_at')
                ->paginate(12)
                ->withQueryString(),
        };

        $sectionCounts = [
            'posts' => $user->posts()->published()->count(),
            'liked' => ($isOwnProfile || $user->likes_public)
                ? $user->postLikes()->whereHas('post', fn ($query) => $query->published())->count()
                : null,
            'bookmarks' => ($isOwnProfile || $user->bookmarks_public)
                ? $user->postBookmarks()->whereHas('post', fn ($query) => $query->published())->count()
                : null,
        ];

        return view('profile.show', [
            'profileUser' => $user,
            'isOwnProfile' => $isOwnProfile,
            'isFollowedByViewer' => $user->isFollowedBy($viewer),
            'section' => $section,
            'allowedSections' => $allowedSections,
            'sectionCounts' => $sectionCounts,
            'posts' => $posts,
        ]);
    }
}
