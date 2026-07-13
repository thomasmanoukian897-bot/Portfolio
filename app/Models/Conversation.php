<?php

namespace App\Models;

use App\Enums\ConversationType;
use App\Enums\MessageType;
use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'name',
        'avatar_path',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ConversationType::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at', 'notifications_muted', 'accepted_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function isGroup(): bool
    {
        return $this->type === ConversationType::Group;
    }

    public function isDirect(): bool
    {
        return $this->type === ConversationType::Direct;
    }

    public function isAdmin(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function avatarUrl(): ?string
    {
        if ($this->avatar_path === null) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    public function avatarInitial(): string
    {
        return Str::upper(Str::substr($this->name ?? 'G', 0, 1));
    }

    public function storeAvatar(UploadedFile $file): string
    {
        $this->deleteAvatar();

        return $file->store('group-avatars', 'public');
    }

    public function deleteAvatar(): void
    {
        if ($this->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->avatar_path);
    }

    public function displayNameFor(User $viewer): string
    {
        if ($this->isGroup()) {
            return $this->name ?? 'Group chat';
        }

        $otherUser = $this->users
            ->first(fn (User $user): bool => $user->id !== $viewer->id);

        return $otherUser?->name ?? 'Direct message';
    }

    public function otherParticipant(User $viewer): ?User
    {
        if ($this->isGroup()) {
            return null;
        }

        return $this->users
            ->first(fn (User $user): bool => $user->id !== $viewer->id);
    }

    public function hasUnreadMessagesFor(User $user): bool
    {
        $lastReadAt = $this->users
            ->firstWhere('id', $user->id)
            ?->pivot
            ?->last_read_at;

        $latestMessage = $this->latestMessage;

        if ($latestMessage === null) {
            return false;
        }

        if ($latestMessage->user_id === $user->id) {
            return false;
        }

        if ($lastReadAt === null) {
            return true;
        }

        return $latestMessage->created_at?->gt($lastReadAt) ?? false;
    }

    public function markAsReadFor(User $user): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);
    }

    public function notificationsMutedFor(User $user): bool
    {
        if ($this->relationLoaded('users')) {
            return (bool) ($this->users
                ->firstWhere('id', $user->id)
                ?->pivot
                ?->notifications_muted ?? false);
        }

        return (bool) ($this->users()
            ->where('users.id', $user->id)
            ->first()
            ?->pivot
            ?->notifications_muted ?? false);
    }

    public function toggleNotificationsFor(User $user): bool
    {
        $muted = ! $this->notificationsMutedFor($user);

        $this->users()->updateExistingPivot($user->id, [
            'notifications_muted' => $muted,
        ]);

        if ($this->relationLoaded('users')) {
            $participant = $this->users->firstWhere('id', $user->id);

            if ($participant !== null) {
                $participant->pivot->notifications_muted = $muted;
            }
        }

        return $muted;
    }

    public function acceptedAtFor(User $user): ?Carbon
    {
        if ($this->relationLoaded('users')) {
            $acceptedAt = $this->users
                ->firstWhere('id', $user->id)
                ?->pivot
                ?->accepted_at;

            return $acceptedAt !== null ? Carbon::parse($acceptedAt) : null;
        }

        $acceptedAt = $this->users()
            ->where('users.id', $user->id)
            ->first()
            ?->pivot
            ?->accepted_at;

        return $acceptedAt !== null ? Carbon::parse($acceptedAt) : null;
    }

    public function isPendingRequestFor(User $user): bool
    {
        if (! $this->isDirect()) {
            return false;
        }

        return $this->acceptedAtFor($user) === null;
    }

    public function acceptFor(User $user): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'accepted_at' => now(),
        ]);

        if ($this->relationLoaded('users')) {
            $participant = $this->users->firstWhere('id', $user->id);

            if ($participant !== null) {
                $participant->pivot->accepted_at = now();
            }
        }

        $otherUser = $this->otherParticipant($user);

        if ($otherUser !== null) {
            $user->recordMessageContactWith($otherUser);
        }
    }

    public function recordSystemMessage(User $actor, MessageType $type): Message
    {
        $body = match ($type) {
            MessageType::GroupNameChanged => "{$actor->name} changed the group name",
            MessageType::GroupAvatarChanged => "{$actor->name} changed the group avatar",
            default => throw new \InvalidArgumentException('Unsupported system message type.'),
        };

        $message = $this->messages()->create([
            'user_id' => $actor->id,
            'type' => $type,
            'body' => $body,
        ]);

        $this->touch();

        return $message;
    }

    /**
     * @param  Builder<Conversation>  $query
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->whereHas('users', fn (Builder $builder): Builder => $builder->where('users.id', $user->id));
    }

    public static function findDirectBetween(User $first, User $second): ?self
    {
        return static::query()
            ->where('type', ConversationType::Direct)
            ->whereHas('users', fn (Builder $query): Builder => $query->where('users.id', $first->id))
            ->whereHas('users', fn (Builder $query): Builder => $query->where('users.id', $second->id))
            ->withCount('users')
            ->get()
            ->first(fn (Conversation $conversation): bool => $conversation->users_count === 2);
    }

    public static function findOrCreateDirect(User $initiator, User $recipient): self
    {
        $existing = static::findDirectBetween($initiator, $recipient);

        if ($existing !== null) {
            return $existing;
        }

        $conversation = static::query()->create([
            'type' => ConversationType::Direct,
            'created_by' => $initiator->id,
        ]);

        $recipientAcceptedAt = $recipient->receivesMessageAsRequest($initiator) ? null : now();

        $conversation->users()->attach([
            $initiator->id => [
                'last_read_at' => null,
                'accepted_at' => now(),
            ],
            $recipient->id => [
                'last_read_at' => null,
                'accepted_at' => $recipientAcceptedAt,
            ],
        ]);

        if ($recipientAcceptedAt !== null) {
            $initiator->recordMessageContactWith($recipient);
        }

        return $conversation;
    }

    protected static function booted(): void
    {
        static::deleting(function (Conversation $conversation): void {
            $conversation->deleteAvatar();
        });
    }
}
