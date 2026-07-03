<div class="space-y-2">
    <label for="name" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
        Name
    </label>
    <input
        id="name"
        name="name"
        type="text"
        value="{{ old('name', $category->name ?? '') }}"
        required
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('name') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('name')
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
        value="{{ old('slug', $category->slug ?? '') }}"
        placeholder="auto-generated from name"
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('slug') border-red-400 focus:ring-red-500 @enderror"
    />
    @error('slug')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
