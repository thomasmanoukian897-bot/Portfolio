<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPostSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserPostSubscriptionController extends Controller
{
    public function toggle(Request $request, User $user): RedirectResponse
    {
        $subscriber = $request->user();

        if ($subscriber->is($user)) {
            abort(403);
        }

        $existingSubscription = UserPostSubscription::query()
            ->where('subscriber_id', $subscriber->id)
            ->where('subscribed_to_id', $user->id)
            ->first();

        if ($existingSubscription !== null) {
            $existingSubscription->delete();
        } else {
            if (! $user->isFollowedBy($subscriber)) {
                abort(403);
            }

            UserPostSubscription::query()->create([
                'subscriber_id' => $subscriber->id,
                'subscribed_to_id' => $user->id,
            ]);
        }

        $status = $existingSubscription !== null
            ? 'You will no longer be notified when '.$user->name.' posts.'
            : 'You will be notified when '.$user->name.' posts.';

        return redirect()
            ->route('users.show', $user)
            ->with('status', $status);
    }
}
