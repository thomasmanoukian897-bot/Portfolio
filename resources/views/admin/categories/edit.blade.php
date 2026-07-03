@extends('layouts.admin')

@section('title', "Edit {$category->name} | Admin")
@section('heading', 'Edit Category')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.categories.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                &larr; Back to categories
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-xl font-bold text-slate-900 font-display mb-6">Edit {{ $category->name }}</h2>

            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.categories._form', ['category' => $category])

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors"
                    >
                        Save Changes
                    </button>

                    <a
                        href="{{ route('admin.categories.index') }}"
                        class="px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
