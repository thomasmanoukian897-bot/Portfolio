<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserPostSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserBlockController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $blocker = $request->user();

        if ($blocker->is($user)) {
            abort(403);
        }

        if (! $blocker->hasBlocked($user)) {
            UserBlock::query()->create([
                'blocker_id' => $blocker->id,
                'blocked_id' => $user->id,
            ]);

            UserFollow::query()
                ->where(function ($query) use ($blocker, $user) {
                    $query->where('follower_id', $blocker->id)
                        ->where('following_id', $user->id);
                })
                ->orWhere(function ($query) use ($blocker, $user) {
                    $query->where('follower_id', $user->id)
                        ->where('following_id', $blocker->id);
                })
                ->delete();

            UserPostSubscription::query()
                ->where(function ($query) use ($blocker, $user) {
                    $query->where('subscriber_id', $blocker->id)
                        ->where('subscribed_to_id', $user->id);
                })
                ->orWhere(function ($query) use ($blocker, $user) {
                    $query->where('subscriber_id', $user->id)
                        ->where('subscribed_to_id', $blocker->id);
                })
                ->delete();
        }

        return redirect()
            ->route('posts.index')
            ->with('status', 'You blocked '.$user->name.'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $blocker = $request->user();

        UserBlock::query()
            ->where('blocker_id', $blocker->id)
            ->where('blocked_id', $user->id)
            ->delete();

        return redirect()
            ->route('profile.edit', ['tab' => 'settings'])
            ->with('status', 'You unblocked '.$user->name.'.');
    }
}
