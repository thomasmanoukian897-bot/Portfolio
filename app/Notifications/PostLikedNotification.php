<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification
{
    use Queueable;

    public function __construct(public User $liker, public Post $post) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_liked',
            'actor_id' => $this->liker->id,
            'actor_name' => $this->liker->name,
            'actor_handle' => $this->liker->handle,
            'actor_avatar_url' => $this->liker->avatarUrl(),
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
            'post_title' => $this->post->title,
            'post_image_url' => $this->post->featuredImageUrl(),
        ];
    }
}
