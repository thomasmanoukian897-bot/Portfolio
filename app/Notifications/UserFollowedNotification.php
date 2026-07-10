<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserFollowedNotification extends Notification
{
    use Queueable;

    public function __construct(public User $follower) {}

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
            'type' => 'follow',
            'actor_id' => $this->follower->id,
            'actor_name' => $this->follower->name,
            'actor_handle' => $this->follower->handle,
            'actor_avatar_url' => $this->follower->avatarUrl(),
        ];
    }
}
