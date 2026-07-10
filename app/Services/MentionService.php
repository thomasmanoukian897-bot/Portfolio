<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\UserMentionedNotification;

class MentionService
{
    public function __construct(private MentionParser $mentionParser) {}

    public function syncAndNotify(Comment $comment, User $commenter, Post $post): void
    {
        $handles = $this->mentionParser->extractHandles($comment->body);

        if ($handles === []) {
            return;
        }

        $mentionedUsers = User::query()
            ->whereIn('handle', $handles)
            ->get();

        if ($mentionedUsers->isEmpty()) {
            return;
        }

        $comment->mentionedUsers()->sync($mentionedUsers->pluck('id'));

        foreach ($mentionedUsers as $mentionedUser) {
            if ($mentionedUser->is($commenter)) {
                continue;
            }

            $mentionedUser->notify(new UserMentionedNotification($commenter, $post, $comment));
        }
    }
}
