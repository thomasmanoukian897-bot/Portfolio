<?php

namespace App\Http\Controllers;

use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Http\Requests\StoreDirectConversationRequest;
use App\Http\Requests\StoreGroupConversationRequest;
use App\Http\Requests\UpdateGroupAvatarRequest;
use App\Http\Requests\UpdateGroupNameRequest;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        [$conversations, $messageRequests] = $this->partitionConversations($user);

        return view('messages.index', [
            'conversations' => $conversations,
            'messageRequests' => $messageRequests,
            'activeConversation' => null,
            'messages' => collect(),
            'isMessageRequest' => false,
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $user = $request->user();

        $conversation->load([
            'users:id,name,handle,avatar_path',
            'latestMessage.user:id,name,handle,avatar_path',
        ]);

        $conversation->markAsReadFor($user);

        $messages = $conversation->messages()
            ->with('user:id,name,handle,avatar_path')
            ->oldest()
            ->limit(100)
            ->get();

        [$conversations, $messageRequests] = $this->partitionConversations($user);

        return view('messages.index', [
            'conversations' => $conversations,
            'messageRequests' => $messageRequests,
            'activeConversation' => $conversation,
            'messages' => $messages,
            'isMessageRequest' => $conversation->isPendingRequestFor($user),
        ]);
    }

    public function storeDirect(StoreDirectConversationRequest $request): RedirectResponse
    {
        $initiator = $request->user();
        $recipient = $request->recipient();

        if ($initiator->isBlockedWith($recipient)) {
            abort(403);
        }

        if (! $recipient->canBeMessagedBy($initiator)) {
            abort(403);
        }

        $conversation = Conversation::findOrCreateDirect($initiator, $recipient);

        return redirect()->route('messages.show', $conversation);
    }

    public function storeGroup(StoreGroupConversationRequest $request): RedirectResponse
    {
        $creator = $request->user();
        $memberIds = collect($request->validated('user_ids'))
            ->unique()
            ->values();

        $members = User::query()->whereIn('id', $memberIds)->get();

        foreach ($members as $member) {
            if ($creator->isBlockedWith($member)) {
                abort(403);
            }

            if (! $member->canBeAddedToGroupBy($creator)) {
                abort(403);
            }
        }

        $conversation = Conversation::query()->create([
            'type' => ConversationType::Group,
            'name' => $request->validated('name'),
            'created_by' => $creator->id,
        ]);

        $attachData = [
            $creator->id => [
                'last_read_at' => now(),
                'accepted_at' => now(),
            ],
        ];

        foreach ($memberIds as $memberId) {
            $attachData[$memberId] = [
                'last_read_at' => null,
                'accepted_at' => now(),
            ];
        }

        $conversation->users()->attach($attachData);

        return redirect()->route('messages.show', $conversation);
    }

    public function updateAvatar(UpdateGroupAvatarRequest $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('update', $conversation);

        $conversation->update([
            'avatar_path' => $conversation->storeAvatar($request->file('avatar')),
        ]);

        $conversation->recordSystemMessage($request->user(), MessageType::GroupAvatarChanged);

        return redirect()->route('messages.show', $conversation);
    }

    public function updateName(UpdateGroupNameRequest $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('update', $conversation);

        $newName = $request->validated('name');

        if ($conversation->name === $newName) {
            return redirect()->route('messages.show', $conversation);
        }

        $conversation->update([
            'name' => $newName,
        ]);

        $conversation->recordSystemMessage($request->user(), MessageType::GroupNameChanged);

        return redirect()->route('messages.show', $conversation);
    }

    public function kickMember(Request $request, Conversation $conversation, User $user): RedirectResponse
    {
        $this->authorize('kickMember', [$conversation, $user]);

        $conversation->users()->detach($user->id);

        return redirect()->route('messages.show', $conversation);
    }

    public function leave(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('leave', $conversation);

        $conversation->users()->detach($request->user()->id);

        if ($conversation->users()->count() === 0) {
            $conversation->delete();
        }

        return redirect()->route('messages.index');
    }

    public function toggleNotifications(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $muted = $conversation->toggleNotificationsFor($request->user());

        return response()->json([
            'notifications_muted' => $muted,
        ]);
    }

    public function acceptRequest(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('acceptRequest', $conversation);

        $conversation->acceptFor($request->user());

        return redirect()->route('messages.show', $conversation);
    }

    public function declineRequest(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('declineRequest', $conversation);

        $conversation->delete();

        return redirect()->route('messages.index');
    }

    /**
     * @return array{0: Collection<int, Conversation>, 1: Collection<int, Conversation>}
     */
    private function partitionConversations(User $user): array
    {
        $all = Conversation::query()
            ->forUser($user)
            ->with([
                'latestMessage.user:id,name,handle,avatar_path',
                'users:id,name,handle,avatar_path',
            ])
            ->withCount('users')
            ->orderByDesc('updated_at')
            ->get();

        $messageRequests = $all->filter(
            fn (Conversation $conversation): bool => $conversation->isPendingRequestFor($user),
        )->values();

        $conversations = $all->reject(
            fn (Conversation $conversation): bool => $conversation->isPendingRequestFor($user),
        )->values();

        return [$conversations, $messageRequests];
    }
}
