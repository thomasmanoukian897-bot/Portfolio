<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $sender,
        public Conversation $conversation,
        public Message $message,
    ) {}

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
            'type' => 'message',
            'actor_id' => $this->sender->id,
            'actor_name' => $this->sender->name,
            'actor_handle' => $this->sender->handle,
            'actor_avatar_url' => $this->sender->avatarUrl(),
            'conversation_id' => $this->conversation->id,
            'conversation_name' => $this->conversation->displayNameFor($notifiable),
            'message_preview' => str($this->message->body)->limit(120)->toString(),
        ];
    }
}
