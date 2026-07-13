<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->users()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return $conversation->isGroup() && $this->view($user, $conversation);
    }

    public function kickMember(User $user, Conversation $conversation, User $member): bool
    {
        if (! $conversation->isGroup() || ! $this->view($user, $conversation)) {
            return false;
        }

        if ($user->id === $member->id) {
            return false;
        }

        if (! $conversation->users()->where('users.id', $member->id)->exists()) {
            return false;
        }

        return $conversation->isAdmin($user);
    }

    public function leave(User $user, Conversation $conversation): bool
    {
        return $conversation->isGroup() && $this->view($user, $conversation);
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        if (! $this->view($user, $conversation)) {
            return false;
        }

        if ($conversation->isPendingRequestFor($user)) {
            return false;
        }

        if ($conversation->isDirect()) {
            $otherUser = $conversation->otherParticipant($user);

            if ($otherUser !== null && $user->isBlockedWith($otherUser)) {
                return false;
            }
        }

        return true;
    }

    public function acceptRequest(User $user, Conversation $conversation): bool
    {
        return $conversation->isDirect()
            && $this->view($user, $conversation)
            && $conversation->isPendingRequestFor($user);
    }

    public function declineRequest(User $user, Conversation $conversation): bool
    {
        return $this->acceptRequest($user, $conversation);
    }
}
