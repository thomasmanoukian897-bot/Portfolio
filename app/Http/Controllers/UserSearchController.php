<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->string('q')->trim()->toString();

        $query = User::query()
            ->select('id', 'name', 'handle', 'avatar_path')
            ->orderBy('name')
            ->limit(8);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('handle', 'like', $search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        $users = $query->get()->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'handle' => $user->handle,
            'avatar_url' => $user->avatarUrl(),
            'avatar_initial' => $user->avatarInitial(),
            'profile_url' => route('users.show', $user),
        ]);

        return response()->json([
            'users' => $users,
        ]);
    }
}
