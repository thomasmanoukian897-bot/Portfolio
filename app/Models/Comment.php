<?php

namespace App\Models;

use App\Services\MentionParser;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Comment extends Model
{
    public const ROOT_PER_PAGE = 10;

    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'body',
        'image_path',
    ];

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function mentionedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function formattedBody(): string
    {
        $parser = app(MentionParser::class);
        $users = $this->relationLoaded('mentionedUsers')
            ? $this->mentionedUsers
            : User::query()->whereIn('handle', $parser->extractHandles($this->body))->get();

        return $parser->render($this->body, $users);
    }

    public function imageUrl(): ?string
    {
        if ($this->image_path === null) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function hasImage(): bool
    {
        return $this->image_path !== null;
    }

    public function deleteImage(): void
    {
        if ($this->image_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->image_path);
    }

    protected static function booted(): void
    {
        static::deleting(function (Comment $comment): void {
            $comment->deleteImage();
        });
    }
}
