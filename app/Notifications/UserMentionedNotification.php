<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserMentionedNotification extends Notification
{
    use Queueable;

    public function __construct(public User $commenter, public Post $post, public Comment $comment) {}

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
            'type' => 'user_mentioned',
            'actor_id' => $this->commenter->id,
            'actor_name' => $this->commenter->name,
            'actor_handle' => $this->commenter->handle,
            'actor_avatar_url' => $this->commenter->avatarUrl(),
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
            'post_title' => $this->post->title,
            'post_image_url' => $this->post->featuredImageUrl(),
            'comment_id' => $this->comment->id,
            'comment_body' => Str::limit($this->comment->body, 120),
        ];
    }
}
