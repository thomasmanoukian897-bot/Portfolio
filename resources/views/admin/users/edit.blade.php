@extends('layouts.admin')

@section('title', "Edit {$user->name} | Admin")
@section('heading', 'Edit User')

@section('content')
    <div class="max-w-xl">
        <div class="mb-6">
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                &larr; Back to users
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-xl font-bold text-slate-900 font-display mb-6">Edit {{ $user->name }}</h2>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label for="name" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                        Name
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('name') border-red-400 focus:ring-red-500 @enderror"
                    />
                    @error('name')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                        Email Address
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-400 focus:ring-red-500 @enderror"
                    />
                    @error('email')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="role" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                        Role
                    </label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('role') border-red-400 focus:ring-red-500 @enderror"
                    >
                        @foreach (\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $user->role->value) === $role->value)>
                                {{ ucfirst($role->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors"
                    >
                        Save Changes
                    </button>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
