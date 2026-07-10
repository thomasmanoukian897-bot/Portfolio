<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserConnectionController extends Controller
{
    public function followers(Request $request, User $user): JsonResponse
    {
        return response()->json([
            'users' => $this->connectionUsers(
                $user->followers(),
                $request->string('search')->trim()->toString(),
                $request->user(),
            ),
        ]);
    }

    public function following(Request $request, User $user): JsonResponse
    {
        return response()->json([
            'users' => $this->connectionUsers(
                $user->following(),
                $request->string('search')->trim()->toString(),
                $request->user(),
            ),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function connectionUsers($relation, string $search, ?User $viewer): array
    {
        $query = $relation
            ->select('users.id', 'users.name', 'users.avatar_path')
            ->orderBy('users.name');

        if ($search !== '') {
            $query->where('users.name', 'like', '%'.$search.'%');
        }

        return $query
            ->limit(50)
            ->get()
            ->map(fn (User $connectionUser): array => [
                'id' => $connectionUser->id,
                'name' => $connectionUser->name,
                'handle' => $connectionUser->handle(),
                'avatar_url' => $connectionUser->avatarUrl(),
                'avatar_initial' => $connectionUser->avatarInitial(),
                'profile_url' => route('users.show', $connectionUser),
                'is_viewer' => $viewer?->is($connectionUser) ?? false,
                'is_followed_by_viewer' => $connectionUser->isFollowedBy($viewer),
            ])
            ->values()
            ->all();
    }
}
