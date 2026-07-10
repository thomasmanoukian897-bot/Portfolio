@extends('layouts.admin')

@section('title', 'Posts | Admin')
@section('heading', 'Posts')

@section('content')
    <div class="lg:hidden mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Posts</h1>
        <p class="text-sm text-slate-600 mt-1">Manage blog posts and articles.</p>
    </div>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-sm text-slate-600 hidden lg:block">Manage blog posts and articles.</p>

        <a
            href="{{ route('admin.posts.create') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-wider transition-colors"
        >
            New Post
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <form method="GET" action="{{ route('admin.posts.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by title or excerpt..."
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
                />
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-wider transition-colors"
                >
                    Search
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Title</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Categories</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Author</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Status</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Updated</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($posts as $post)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $post->title }}</div>
                                <div class="text-xs text-slate-500 font-mono mt-1">{{ $post->slug }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($post->categories->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($post->categories as $category)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono bg-slate-100 text-slate-600">
                                                {{ $category->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $post->user->name }}</td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono',
                                    'bg-green-100 text-green-700' => $post->isPublished(),
                                    'bg-amber-100 text-amber-700' => ! $post->isPublished(),
                                ])>
                                    {{ $post->isPublished() ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $post->updated_at->format('M j, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($post->isPublished())
                                        <a
                                            href="{{ route('posts.show', $post) }}"
                                            target="_blank"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors"
                                        >
                                            View
                                        </a>
                                    @endif

                                    <a
                                        href="{{ route('admin.posts.edit', $post) }}"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                                    >
                                        Edit
                                    </a>

                                    @can('deleteAny', \App\Models\Post::class)
                                        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                aria-label="Delete"
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs bg-red-50 hover:bg-red-100 text-red-700 transition-colors"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">No posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
