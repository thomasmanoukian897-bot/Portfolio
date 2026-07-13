<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $afterId = $request->integer('after_id');

        $messages = $conversation->messages()
            ->with('user:id,name,handle,avatar_path')
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->oldest()
            ->limit(50)
            ->get()
            ->map(fn (Message $message): array => $this->formatMessage($message));

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('sendMessage', $conversation);

        $user = $request->user();

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'type' => MessageType::Text,
            'body' => $request->validated('body'),
        ]);

        $conversation->touch();

        $message->load('user:id,name,handle,avatar_path');

        $conversation->users()
            ->where('users.id', '!=', $user->id)
            ->wherePivot('notifications_muted', false)
            ->get()
            ->each(function ($recipient) use ($user, $conversation, $message): void {
                $recipient->notify(new NewMessageNotification($user, $conversation, $message));
            });

        $conversation->markAsReadFor($user);

        return response()->json([
            'message' => $this->formatMessage($message),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'type' => $message->type->value,
            'is_system' => $message->isSystem(),
            'created_at' => $message->created_at?->toIso8601String(),
            'created_at_label' => $message->created_at?->format('g:i A'),
            'is_mine' => $message->user_id === auth()->id(),
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'handle' => $message->user->handle,
                'profile_url' => route('users.show', $message->user),
                'avatar_url' => $message->user->avatarUrl(),
                'avatar_initial' => $message->user->avatarInitial(),
            ],
        ];
    }
}
