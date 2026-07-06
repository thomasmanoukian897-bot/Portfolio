<?php

namespace App\Http\Controllers;

use App\Enums\CommentVoteType;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentVoteController extends Controller
{
    public function store(Request $request, Post $post, Comment $comment): JsonResponse
    {
        if (! $post->isPublished()) {
            throw new NotFoundHttpException;
        }

        $validated = $request->validate([
            'type' => ['required', Rule::enum(CommentVoteType::class)],
        ]);

        $type = CommentVoteType::from($validated['type']);
        $user = $request->user();

        $existingVote = CommentVote::query()
            ->where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingVote !== null) {
            if ($existingVote->type === $type) {
                $existingVote->delete();
                $activeVote = null;
            } else {
                $existingVote->update(['type' => $type]);
                $activeVote = $type;
            }
        } else {
            $comment->votes()->create([
                'user_id' => $user->id,
                'type' => $type,
            ]);
            $activeVote = $type;
        }

        return response()->json([
            'vote' => $activeVote?->value,
            'up_count' => $comment->votes()->where('type', CommentVoteType::Up)->count(),
            'down_count' => $comment->votes()->where('type', CommentVoteType::Down)->count(),
        ]);
    }
}
