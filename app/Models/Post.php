<?php

namespace App\Models;

use App\Enums\CommentVoteType;
use App\Services\FeaturedImageProcessor;
use App\Services\FeaturedVideoProcessor;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'image_path',
        'video_path',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function rootComments(): HasMany
    {
        return $this->hasMany(Comment::class)->root();
    }

    public function rootCommentPage(Comment $rootComment): int
    {
        $position = $this->rootComments()
            ->withCount([
                'votes as upvotes_count' => fn (Builder $votesQuery) => $votesQuery->where('type', CommentVoteType::Up),
            ])
            ->orderByDesc('upvotes_count')
            ->oldest()
            ->pluck('id')
            ->search($rootComment->id);

        if ($position === false) {
            return 1;
        }

        return max(1, (int) ceil(($position + 1) / Comment::ROOT_PER_PAGE));
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(PostBookmark::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isBookmarkedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function featuredImageUrl(): ?string
    {
        if ($this->image_path === null) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function hasVideo(): bool
    {
        return $this->video_path !== null;
    }

    public function featuredVideoUrl(): ?string
    {
        if ($this->video_path === null) {
            return null;
        }

        return Storage::disk('public')->url($this->video_path);
    }

    public function storeFeaturedImage(UploadedFile $file): string
    {
        $this->deleteFeaturedImage();

        return app(FeaturedImageProcessor::class)->store($file);
    }

    public function deleteFeaturedImage(): void
    {
        if ($this->image_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->image_path);
    }

    public function storeFeaturedVideo(UploadedFile $file): string
    {
        $this->deleteFeaturedVideo();

        return app(FeaturedVideoProcessor::class)->store($file);
    }

    public function deleteFeaturedVideo(): void
    {
        if ($this->video_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->video_path);
    }

    protected static function booted(): void
    {
        static::deleting(function (Post $post): void {
            $post->deleteFeaturedImage();
            $post->deleteFeaturedVideo();
        });
    }
}
