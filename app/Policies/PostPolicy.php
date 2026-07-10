<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->isAdmin() || $post->isOwnedBy($user);
    }

    /**
     * Delete a post from the public article page (authors only, including admins on their own posts).
     */
    public function delete(User $user, Post $post): bool
    {
        return $post->isOwnedBy($user);
    }

    /**
     * Delete any post from the admin panel (admins only).
     */
    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
