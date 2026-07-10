@props(['post'])

<article class="group py-5">
    <a href="{{ route('posts.show', $post) }}" class="flex items-start gap-4 md:gap-6">
        @if ($post->published_at)
            <time
                datetime="{{ $post->published_at->toIso8601String() }}"
                class="shrink-0 w-12 pt-0.5 text-sm text-slate-400 dark:text-slate-500 tabular-nums"
            >
                {{ $post->published_at->format('H:i') }}
            </time>
        @endif

        <div class="min-w-0 flex-1">
            <h2 class="text-base md:text-lg font-bold text-slate-900 dark:text-slate-100 leading-snug group-hover:text-primary transition-colors">
                {{ $post->title }}
            </h2>

            @if ($post->categories->isNotEmpty())
                <p class="mt-1.5 text-xs font-bold uppercase tracking-wider text-primary">
                    {{ $post->categories->pluck('name')->join(' · ') }}
                </p>
            @endif
        </div>

        <div class="shrink-0 w-24 h-16 md:w-28 md:h-[4.5rem] overflow-hidden rounded bg-slate-100 dark:bg-slate-800">
            @if ($post->featuredImageUrl())
                <img
                    src="{{ $post->featuredImageUrl() }}"
                    alt="{{ $post->title }}"
                    class="h-full w-full object-cover transition-transform group-hover:scale-[1.02]"
                />
            @else
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                    <x-user-avatar :user="$post->user" size="sm" />
                </div>
            @endif
        </div>
    </a>
</article>
