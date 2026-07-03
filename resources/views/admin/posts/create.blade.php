@extends('layouts.admin')

@section('title', 'New Post | Admin')
@section('heading', 'New Post')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.posts.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                &larr; Back to posts
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-xl font-bold text-slate-900 font-display mb-6">Create Post</h2>

            <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @include('admin.posts._form', ['categories' => $categories])

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors"
                    >
                        Create Post
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
