@extends('layouts.admin')

@section('title', "Edit {$post->title} | Admin")
@section('heading', 'Edit Post')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.posts.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                &larr; Back to posts
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-xl font-bold text-slate-900 font-display mb-6">Edit {{ $post->title }}</h2>

            <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.posts._form', ['post' => $post, 'categories' => $categories])

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors"
                    >
                        Save Changes
                    </button>

                    <a
                        href="{{ route('admin.posts.index') }}"
                        class="px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
