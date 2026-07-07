@props(['post'])

<article {{ $attributes->merge(['class' => 'group flex flex-col bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden hover:shadow-md transition-shadow']) }}>
    <a href="{{ route('posts.show', $post) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100 relative">
        @if ($post->featuredImageUrl())
            <img
                src="{{ $post->featuredImageUrl() }}"
                alt="{{ $post->title }}"
                class="h-full w-full object-cover transition-transform group-hover:scale-[1.02]"
            />
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-blue-50/80 to-slate-200 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900">
                <div class="absolute inset-0 animated-grid opacity-30"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <x-user-avatar :user="$post->user" size="lg" class="w-24 h-24 text-3xl ring-4 ring-white/50 dark:ring-slate-700/50 shadow-lg" />
                </div>
            </div>
        @endif
    </a>
    <div class="p-6 flex flex-col">
        <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
            <x-user-avatar :user="$post->user" size="xs" />
            <span>{{ $post->user->name }}</span>
            @if ($post->published_at)
                <span class="text-slate-300">&middot;</span>
                <time datetime="{{ $post->published_at->toIso8601String() }}">
                    {{ $post->published_at->diffForHumans() }}
                </time>
            @else
                <span class="text-slate-300">&middot;</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400">
                    Draft
                </span>
            @endif
        </div>

        @if ($post->categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($post->categories as $category)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        @endif

        <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 font-display mb-3 group-hover:text-primary transition-colors">
            <a href="{{ route('posts.show', $post) }}" class="hover:underline">
                {{ $post->title }}
            </a>
        </h2>

        @if ($post->excerpt)
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6 line-clamp-4">
                {{ $post->excerpt }}
            </p>
        @endif

        <div class="flex items-center justify-between gap-4 {{ $post->excerpt ? 'mt-2' : 'mt-4' }}">
            <a
                href="{{ route('posts.show', $post) }}"
                class="inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-primary hover:text-blue-700 transition-colors"
            >
                Read More
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 text-slate-500">
                    <span class="inline-flex items-center gap-1.5 text-sm" aria-label="{{ $post->likes_count }} {{ Str::plural('like', $post->likes_count) }}">
                        <i class="fa-solid fa-heart" aria-hidden="true"></i>
                        <span class="font-semibold tabular-nums">{{ $post->likes_count }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sm" aria-label="{{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}">
                        <i class="fa-solid fa-comment" aria-hidden="true"></i>
                        <span class="font-semibold tabular-nums">{{ $post->comments_count }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sm" aria-label="{{ $post->views_count }} {{ Str::plural('view', $post->views_count) }}">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        <span class="font-semibold tabular-nums">{{ $post->views_count }}</span>
                    </span>
                </div>

                @can('delete', $post)
                    <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            aria-label="Delete"
                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-950 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 transition-colors"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</article>
