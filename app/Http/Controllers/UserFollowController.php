<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserPostSubscription;
use App\Notifications\UserFollowedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserFollowController extends Controller
{
    public function toggle(Request $request, User $user): RedirectResponse
    {
        $follower = $request->user();

        if ($follower->is($user)) {
            abort(403);
        }

        $existingFollow = UserFollow::query()
            ->where('follower_id', $follower->id)
            ->where('following_id', $user->id)
            ->first();

        if ($existingFollow !== null) {
            $existingFollow->delete();

            UserPostSubscription::query()
                ->where('subscriber_id', $follower->id)
                ->where('subscribed_to_id', $user->id)
                ->delete();
        } else {
            UserFollow::query()->create([
                'follower_id' => $follower->id,
                'following_id' => $user->id,
            ]);

            $user->notify(new UserFollowedNotification($follower));
        }

        $status = $existingFollow !== null
            ? 'You unfollowed '.$user->name.'.'
            : 'You are now following '.$user->name.'.';

        if ($request->boolean('from_notifications')) {
            return redirect()
                ->route('notifications.index')
                ->with('status', $status);
        }

        return redirect()
            ->route('users.show', $user)
            ->with('status', $status);
    }
}
