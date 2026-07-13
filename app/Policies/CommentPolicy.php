<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Post $post): bool
    {
        return $post->isPublished() && $post->comments_enabled;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->isAdmin()
            || $user->id === $comment->user_id
            || $comment->post->isOwnedBy($user);
    }
}
