<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use App\Notifications\PostLikedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostLikeController extends Controller
{
    public function toggle(Request $request, Post $post): JsonResponse
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $user = $request->user();

        $existingLike = PostLike::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike !== null) {
            $existingLike->delete();
            $liked = false;
        } else {
            $post->likes()->create([
                'user_id' => $user->id,
            ]);
            $liked = true;

            if (! $post->user->is($user)) {
                $post->user->notify(new PostLikedNotification($user, $post));
            }
        }

        return response()->json([
            'liked' => $liked,
            'count' => $post->likes()->count(),
        ]);
    }
}
