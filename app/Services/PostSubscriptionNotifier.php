<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Notifications\UserPostedNotification;

class PostSubscriptionNotifier
{
    public function notifySubscribers(Post $post): void
    {
        $author = $post->user;

        if ($author === null) {
            return;
        }

        $author->postSubscribers()
            ->where('users.id', '!=', $author->id)
            ->each(fn (User $subscriber) => $subscriber->notify(
                new UserPostedNotification($author, $post)
            ));
    }
}
