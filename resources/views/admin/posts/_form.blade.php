@php
    $selectedCategoryIds = old('category_ids', isset($post) ? $post->categories->pluck('id')->all() : []);
@endphp

<div class="space-y-2">
    <label for="title" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Title
    </label>
    <input
        id="title"
        name="title"
        type="text"
        value="{{ old('title', $post->title ?? '') }}"
        required
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('title') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('title')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label for="slug" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Slug <span class="text-slate-400 font-normal normal-case">(optional)</span>
    </label>
    <input
        id="slug"
        name="slug"
        type="text"
        value="{{ old('slug', $post->slug ?? '') }}"
        placeholder="auto-generated from title"
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('slug') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('slug')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label for="image" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Featured Image <span class="text-slate-400 font-normal normal-case">(optional)</span>
    </label>

    @if (isset($post) && $post->featuredImageUrl())
        <div class="mb-3">
            <img
                src="{{ $post->featuredImageUrl() }}"
                alt="{{ $post->title }}"
                class="h-40 w-auto max-w-full rounded-xl border border-slate-200 object-cover"
            />
        </div>

        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs cursor-pointer hover:border-red-200 transition-colors">
            <input
                type="checkbox"
                name="remove_image"
                value="1"
                @checked(old('remove_image'))
                class="rounded border-slate-300 text-red-600 focus:ring-red-600"
            />
            <span>Remove current image</span>
        </label>
    @endif

    <input
        id="image"
        name="image"
        type="file"
        accept="image/jpeg,image/jpg,image/png,image/webp"
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 @error('image') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('image')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <p class="text-xs text-slate-500">JPEG, PNG, or WebP up to 2 MB.</p>
</div>

<div class="space-y-2">
    <span class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Categories
    </span>

    @if ($categories->isNotEmpty())
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($categories as $category)
                <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs cursor-pointer hover:border-blue-200 transition-colors">
                    <input
                        type="checkbox"
                        name="category_ids[]"
                        value="{{ $category->id }}"
                        @checked(in_array($category->id, $selectedCategoryIds))
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-600"
                    />
                    <span>{{ $category->name }}</span>
                </label>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500">
            No categories yet.
            <a href="{{ route('admin.categories.create') }}" class="font-semibold text-blue-600 hover:text-blue-700">Create one</a>
            before assigning it to this post.
        </p>
    @endif

    @error('category_ids')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('category_ids.*')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label for="excerpt" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Excerpt <span class="text-slate-400 font-normal normal-case">(optional)</span>
    </label>
    <textarea
        id="excerpt"
        name="excerpt"
        rows="3"
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('excerpt') border-red-400 focus:ring-red-500 @enderror"
    >{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
    @error('excerpt')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label for="content" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Content
    </label>
    <textarea
        id="content"
        name="content"
        rows="12"
        required
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono @error('content') border-red-400 focus:ring-red-500 @enderror"
    >{{ old('content', $post->content ?? '') }}</textarea>
    @error('content')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <p class="text-xs text-slate-500">HTML is supported for formatting.</p>
</div>

<div class="space-y-2">
    <label for="published_at" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Publish Date <span class="text-slate-400 font-normal normal-case">(leave empty for draft)</span>
    </label>
    <input
        id="published_at"
        name="published_at"
        type="datetime-local"
        value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('published_at') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('published_at')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
