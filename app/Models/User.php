<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\GroupAddPermission;
use App\Enums\MessagePermission;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'handle',
        'email',
        'google_id',
        'password',
        'role',
        'avatar_path',
        'likes_public',
        'bookmarks_public',
        'group_add_permission',
        'message_permission',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => UserRole::User,
        'group_add_permission' => GroupAddPermission::Everyone,
        'message_permission' => MessagePermission::Everyone,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'likes_public' => 'boolean',
            'bookmarks_public' => 'boolean',
            'group_add_permission' => GroupAddPermission::class,
            'message_permission' => MessagePermission::class,
        ];
    }

    public function hasRole(UserRole|string $role): bool
    {
        if (is_string($role)) {
            $role = UserRole::from($role);
        }

        return $this->role === $role;
    }

    /**
     * @param  list<UserRole|string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function postLikes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function postBookmarks(): HasMany
    {
        return $this->hasMany(PostBookmark::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function commentVotes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function isFollowedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->followers()->where('users.id', $user->id)->exists();
    }

    public function isFollowing(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->following()->where('users.id', $user->id)->exists();
    }

    public function canBeAddedToGroupBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->group_add_permission === GroupAddPermission::Everyone) {
            return true;
        }

        return $this->isFollowing($user);
    }

    public function hasMessagedBefore(?User $other): bool
    {
        if ($other === null) {
            return false;
        }

        return DB::table('user_message_contacts')
            ->where('user_id', $this->id)
            ->where('contact_user_id', $other->id)
            ->exists();
    }

    public function recordMessageContactWith(User $other): void
    {
        $now = now();

        DB::table('user_message_contacts')->insertOrIgnore([
            [
                'user_id' => $this->id,
                'contact_user_id' => $other->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $other->id,
                'contact_user_id' => $this->id,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function canBeMessagedBy(?User $sender): bool
    {
        if ($sender === null) {
            return false;
        }

        if ($this->isBlockedWith($sender)) {
            return false;
        }

        if ($this->isFollowing($sender) || $this->hasMessagedBefore($sender)) {
            return true;
        }

        return match ($this->message_permission) {
            MessagePermission::Everyone => true,
            MessagePermission::FollowersOnly => $this->isFollowedBy($sender),
            MessagePermission::NoOne => false,
        };
    }

    public function receivesMessageAsRequest(?User $sender): bool
    {
        if ($sender === null) {
            return false;
        }

        if (! $this->canBeMessagedBy($sender)) {
            return false;
        }

        if ($this->isFollowing($sender) || $this->hasMessagedBefore($sender)) {
            return false;
        }

        return true;
    }

    public function postSubscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_post_subscriptions', 'subscribed_to_id', 'subscriber_id')
            ->withTimestamps();
    }

    public function postSubscriptions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_post_subscriptions', 'subscriber_id', 'subscribed_to_id')
            ->withTimestamps();
    }

    public function isSubscribedToPostsBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->postSubscribers()->where('users.id', $user->id)->exists();
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_id')
            ->withTimestamps();
    }

    public function blockedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocked_id', 'blocker_id')
            ->withTimestamps();
    }

    public function hasBlocked(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->blockedUsers()->where('users.id', $user->id)->exists();
    }

    public function isBlockedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->blockedBy()->where('users.id', $user->id)->exists();
    }

    public function isBlockedWith(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->hasBlocked($user) || $this->isBlockedBy($user);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('last_read_at', 'notifications_muted', 'accepted_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function hasUnreadMessages(): bool
    {
        return Conversation::query()
            ->forUser($this)
            ->with(['latestMessage', 'users'])
            ->get()
            ->contains(fn (Conversation $conversation): bool => $conversation->hasUnreadMessagesFor($this));
    }

    public static function generateUniqueHandle(string $name): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'user';
        }

        $handle = $base;
        $counter = 1;

        while (static::query()->where('handle', $handle)->exists()) {
            $handle = $base.'-'.$counter;
            $counter++;
        }

        return $handle;
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
        return Str::upper(Str::substr($this->name, 0, 1));
    }

    public function storeAvatar(UploadedFile $file): string
    {
        $this->deleteAvatar();

        return $file->store('avatars', 'public');
    }

    public function deleteAvatar(): void
    {
        if ($this->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->avatar_path);
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (filled($user->handle)) {
                return;
            }

            $user->handle = static::generateUniqueHandle($user->name);
        });

        static::deleting(function (User $user): void {
            $user->deleteAvatar();
        });
    }
}
