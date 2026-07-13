@php
    $selectedCategoryIds = old('category_ids', []);
@endphp

<div class="space-y-2">
    <label for="title" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Title
    </label>
    <input
        id="title"
        name="title"
        type="text"
        value="{{ old('title') }}"
        required
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('title') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('title')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label for="image" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Featured Image <span class="text-slate-400 font-normal normal-case">(optional)</span>
    </label>
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
    <label for="video" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Featured Video <span class="text-slate-400 font-normal normal-case">(optional)</span>
    </label>
    <input
        id="video"
        name="video"
        type="file"
        accept="video/mp4,video/webm,video/quicktime"
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 @error('video') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('video')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <p class="text-xs text-slate-500">MP4, WebM, or MOV up to 50 MB. Add a featured image above for the thumbnail.</p>
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
        <p class="text-sm text-slate-500">No categories are available yet. Contact an administrator before publishing.</p>
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
    >{{ old('excerpt') }}</textarea>
    @error('excerpt')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-3">
    <span class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Engagement
    </span>

    <label class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-xs cursor-pointer hover:border-blue-200 transition-colors">
        <input type="hidden" name="comments_enabled" value="0" />
        <input
            type="checkbox"
            name="comments_enabled"
            value="1"
            @checked(old('comments_enabled', true))
            class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-600"
        />
        <span>
            <span class="block text-sm font-semibold text-slate-900">Allow comments</span>
            <span class="block text-sm text-slate-500 mt-1">Let others join the conversation on this post.</span>
        </span>
    </label>

    <label class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-xs cursor-pointer hover:border-blue-200 transition-colors">
        <input type="hidden" name="likes_hidden" value="0" />
        <input
            type="checkbox"
            name="likes_hidden"
            value="1"
            @checked(old('likes_hidden', false))
            class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-600"
        />
        <span>
            <span class="block text-sm font-semibold text-slate-900">Hide like count</span>
            <span class="block text-sm text-slate-500 mt-1">People can still like your post, but the total will not be shown.</span>
        </span>
    </label>
</div>

<x-wysiwyg-editor
    name="content"
    :value="old('content')"
    required
/>
