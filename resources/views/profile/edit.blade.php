@extends('layouts.app')

@section('title', 'Profile | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-16 px-6 md:px-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/50 to-transparent dark:from-blue-950/20 pointer-events-none"></div>

        <div class="relative max-w-2xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-primary hover:text-blue-700 transition-colors">
                    &larr; Back to home
                </a>
            </div>

            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary font-mono mb-4">Your Account</p>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-slate-100 font-display tracking-tight">
                    Profile
                </h1>
                <p class="mt-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                    Manage your account details and security settings.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 dark:bg-green-950/30 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('password_status'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 dark:bg-green-950/30 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                    {{ session('password_status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="flex border-b border-slate-200 dark:border-slate-700">
                    <a
                        href="{{ route('profile.edit') }}"
                        @class([
                            'flex-1 px-6 py-4 text-sm font-semibold text-center transition-colors',
                            'text-primary border-b-2 border-primary bg-primary/5' => $activeTab === 'account',
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' => $activeTab !== 'account',
                        ])
                    >
                        Account
                    </a>
                    <a
                        href="{{ route('profile.edit', ['tab' => 'password']) }}"
                        @class([
                            'flex-1 px-6 py-4 text-sm font-semibold text-center transition-colors',
                            'text-primary border-b-2 border-primary bg-primary/5' => $activeTab === 'password',
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' => $activeTab !== 'password',
                        ])
                    >
                        Change Password
                    </a>
                </div>

                @if ($activeTab === 'account')
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-8 space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-slate-200 dark:border-slate-700">
                            <x-user-avatar :user="$user" size="lg" />

                            <div class="text-center sm:text-left space-y-1">
                                <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $user->name }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 capitalize">{{ $user->role->value }} account</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                                Display Name
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('name') border-red-400 focus:ring-red-500 @enderror"
                            />
                            @error('name')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="avatar" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                                Profile Picture
                            </label>
                            <input
                                id="avatar"
                                name="avatar"
                                type="file"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200 dark:hover:file:bg-slate-600 @error('avatar') border-red-400 focus:ring-red-500 @enderror"
                            />
                            @error('avatar')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-slate-500 dark:text-slate-400">JPEG, PNG, or WebP up to 2 MB. Leave empty to keep your current picture.</p>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                class="btn-gradient text-white px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-8 space-y-8">
                        @if ($pendingPasswordChange)
                            <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 dark:border-blue-800 px-4 py-3 text-sm text-blue-800 dark:text-blue-200">
                                A verification code has been sent to <strong>{{ $user->email }}</strong>. Enter it below to complete your password change.
                            </div>

                            <form method="POST" action="{{ route('profile.password.verify') }}" class="space-y-6">
                                @csrf

                                <div class="space-y-2">
                                    <label for="code" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                                        Verification Code
                                    </label>
                                    <input
                                        id="code"
                                        name="code"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]{6}"
                                        maxlength="6"
                                        value="{{ old('code') }}"
                                        required
                                        autofocus
                                        placeholder="000000"
                                        class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 tracking-[0.3em] font-mono @error('code') border-red-400 focus:ring-red-500 @enderror"
                                    />
                                    @error('code')
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-slate-500 dark:text-slate-400">The code expires in 15 minutes.</p>
                                </div>

                                <button
                                    type="submit"
                                    class="btn-gradient text-white px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all hover:scale-[1.02] active:scale-[0.98]"
                                >
                                    Verify &amp; Change Password
                                </button>
                            </form>

                            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Need a new code? Submit the password form again.</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.password.request') }}" class="space-y-6">
                            @csrf

                            <div class="space-y-2">
                                <label for="current_password" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                                    Current Password
                                </label>
                                <input
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('current_password') border-red-400 focus:ring-red-500 @enderror"
                                />
                                @error('current_password')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                                    New Password
                                </label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('password') border-red-400 focus:ring-red-500 @enderror"
                                />
                                @error('password')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="password_confirmation" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                                    Confirm New Password
                                </label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('password_confirmation') border-red-400 focus:ring-red-500 @enderror"
                                />
                                @error('password_confirmation')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                A 6-digit verification code will be sent to your email before the password is changed.
                            </p>

                            <button
                                type="submit"
                                class="btn-gradient text-white px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                {{ $pendingPasswordChange ? 'Resend Verification Code' : 'Send Verification Code' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
