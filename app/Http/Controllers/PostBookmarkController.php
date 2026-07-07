<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostBookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostBookmarkController extends Controller
{
    public function toggle(Request $request, Post $post): JsonResponse
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $user = $request->user();

        $existingBookmark = PostBookmark::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingBookmark !== null) {
            $existingBookmark->delete();
            $bookmarked = false;
        } else {
            $post->bookmarks()->create([
                'user_id' => $user->id,
            ]);
            $bookmarked = true;
        }

        return response()->json([
            'bookmarked' => $bookmarked,
        ]);
    }
}
