@extends('layouts.app')

@section('title', 'Messages | Digital Builder')

@section('content')
    <section class="relative pt-20 pb-8 px-4 md:px-6 overflow-hidden min-h-[calc(100vh-5rem)]">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-100/60 via-blue-50/20 to-transparent dark:from-slate-950/95 dark:via-slate-900/80 dark:to-slate-900/0 pointer-events-none"></div>

        <div
            class="relative max-w-6xl mx-auto"
            data-messages
            data-users-search-url="{{ route('users.search') }}"
            data-csrf="{{ csrf_token() }}"
            @if ($errors->hasAny(['name', 'user_ids', 'user_ids.*']))
                data-reopen-group-modal="true"
            @endif
            @if ($activeConversation)
                data-active-conversation-id="{{ $activeConversation->id }}"
                data-messages-url="{{ route('messages.messages.index', $activeConversation) }}"
                data-send-url="{{ route('messages.messages.store', $activeConversation) }}"
            @endif
        >
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden flex flex-col md:flex-row min-h-[calc(100vh-8rem)]">
                {{-- Conversation list --}}
                <aside class="w-full md:w-80 lg:w-96 shrink-0 border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-700 flex flex-col @if($activeConversation) hidden md:flex @endif">
                    <div class="px-4 py-4 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between gap-3">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Messages</h1>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    data-messages-new-dm
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                                    aria-label="New message"
                                    title="New message"
                                >
                                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                                </button>
                                <button
                                    type="button"
                                    data-messages-new-group
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors"
                                    aria-label="Create group"
                                    title="Create group"
                                >
                                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        @if ($messageRequests->isNotEmpty())
                            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                    Requests
                                </h2>
                            </div>
                            @foreach ($messageRequests as $conversation)
                                <x-conversation-list-item
                                    :conversation="$conversation"
                                    :active="$activeConversation?->is($conversation) ?? false"
                                />
                            @endforeach
                            @if ($conversations->isNotEmpty())
                                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                        Messages
                                    </h2>
                                </div>
                            @endif
                        @endif

                        @forelse ($conversations as $conversation)
                            <x-conversation-list-item
                                :conversation="$conversation"
                                :active="$activeConversation?->is($conversation) ?? false"
                            />
                        @empty
                            @if ($messageRequests->isEmpty())
                            <div class="px-4 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 mb-3">
                                    <i class="fa-solid fa-paper-plane text-2xl text-slate-400" aria-hidden="true"></i>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    No conversations yet. Start a chat or create a group.
                                </p>
                            </div>
                            @endif
                        @endforelse
                    </div>
                </aside>

                {{-- Chat panel --}}
                <div class="flex-1 flex flex-col min-h-0 @if(! $activeConversation) hidden md:flex @endif">
                    @if ($activeConversation)
                        <div class="px-4 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                            <a
                                href="{{ route('messages.index') }}"
                                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                                aria-label="Back to conversations"
                            >
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            </a>

                            @if ($activeConversation->isDirect() && $activeConversation->otherParticipant(auth()->user()))
                                <x-user-avatar :user="$activeConversation->otherParticipant(auth()->user())" size="sm" />
                            @elseif ($activeConversation->isGroup())
                                <form
                                    method="POST"
                                    action="{{ route('messages.groups.avatar.update', $activeConversation) }}"
                                    enctype="multipart/form-data"
                                    data-messages-group-avatar-form
                                >
                                    @csrf
                                    @method('PATCH')
                                    <label class="relative block cursor-pointer group" title="Change group photo">
                                        <x-conversation-avatar :conversation="$activeConversation" size="sm" />
                                        <span class="absolute inset-0 flex items-center justify-center rounded-full bg-slate-900/50 text-white opacity-0 transition-opacity group-hover:opacity-100">
                                            <i class="fa-solid fa-camera text-xs" aria-hidden="true"></i>
                                        </span>
                                        <input
                                            type="file"
                                            name="avatar"
                                            accept="image/jpeg,image/jpg,image/png,image/webp"
                                            class="sr-only"
                                            data-messages-group-avatar-input
                                        />
                                    </label>
                                </form>
                            @endif

                            @php
                                $other = $activeConversation->isDirect()
                                    ? $activeConversation->otherParticipant(auth()->user())
                                    : null;
                            @endphp

                            <div class="min-w-0 flex-1">
                                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 truncate">
                                    @if ($other)
                                        <a
                                            href="{{ route('users.show', $other) }}"
                                            class="hover:text-primary transition-colors"
                                        >
                                            {{ $activeConversation->displayNameFor(auth()->user()) }}
                                        </a>
                                    @else
                                        {{ $activeConversation->displayNameFor(auth()->user()) }}
                                    @endif
                                </h2>
                                @if ($activeConversation->isGroup())
                                    <button
                                        type="button"
                                        data-messages-group-members-open
                                        class="text-xs text-slate-500 dark:text-slate-400 truncate hover:text-primary dark:hover:text-primary transition-colors"
                                    >
                                        {{ $activeConversation->users->count() }} {{ str('member')->plural($activeConversation->users->count()) }}
                                    </button>
                                @elseif ($other)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ '@'.$other->handle }}</p>
                                @endif
                            </div>

                            @if ($activeConversation->isGroup())
                                @php
                                    $notificationsMuted = $activeConversation->notificationsMutedFor(auth()->user());
                                @endphp
                                <div class="flex items-center gap-1 shrink-0">
                                    <button
                                        type="button"
                                        data-messages-notifications-toggle
                                        data-url="{{ route('messages.notifications.toggle', $activeConversation) }}"
                                        data-muted="{{ $notificationsMuted ? 'true' : 'false' }}"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        aria-label="{{ $notificationsMuted ? 'Turn on notifications' : 'Turn off notifications' }}"
                                        title="{{ $notificationsMuted ? 'Notifications off' : 'Notifications on' }}"
                                    >
                                        <i
                                            data-messages-notifications-icon
                                            @class([
                                                'text-base',
                                                'fa-solid fa-bell-slash' => $notificationsMuted,
                                                'fa-solid fa-bell' => ! $notificationsMuted,
                                            ])
                                            aria-hidden="true"
                                        ></i>
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{ route('messages.groups.leave', $activeConversation) }}"
                                        onsubmit="return confirm('Leave this group?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                            aria-label="Leave group"
                                            title="Leave group"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div
                            class="flex-1 overflow-y-auto px-4 py-4 space-y-3"
                            data-messages-list
                        >
                            @foreach ($messages as $message)
                                <x-message-bubble :message="$message" :is-mine="$message->user_id === auth()->id()" />
                            @endforeach
                        </div>

                        @if ($isMessageRequest ?? false)
                            <div class="px-4 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                                    Accept this request to reply. People you follow or have chatted with before can always message you.
                                </p>
                                <div class="flex flex-wrap items-center gap-3">
                                    <form method="POST" action="{{ route('messages.requests.accept', $activeConversation) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-primary text-white hover:bg-primary/90 transition-colors"
                                        >
                                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                                            Accept
                                        </button>
                                    </form>
                                    <form
                                        method="POST"
                                        action="{{ route('messages.requests.decline', $activeConversation) }}"
                                        onsubmit="return confirm('Delete this message request?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                        <form
                            class="px-4 py-4 border-t border-slate-200 dark:border-slate-700"
                            data-messages-form
                        >
                            <div class="flex items-end gap-3">
                                <label class="sr-only" for="message-body">Message</label>
                                <textarea
                                    id="message-body"
                                    name="body"
                                    rows="1"
                                    placeholder="Type a message..."
                                    data-messages-input
                                    class="flex-1 resize-none rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20"
                                ></textarea>
                                <button
                                    type="submit"
                                    data-messages-submit
                                    disabled
                                    class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-primary text-white hover:bg-primary/90 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shrink-0"
                                    aria-label="Send message"
                                >
                                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </div>
                        </form>
                        @endif
                    @else
                        <div class="flex-1 flex items-center justify-center px-6 py-16 text-center">
                            <div>
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 mb-4">
                                    <i class="fa-solid fa-comments text-3xl text-slate-400" aria-hidden="true"></i>
                                </div>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Your messages</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-6">
                                    Send private messages to people you follow, or create a group chat with multiple members.
                                </p>
                                <div class="flex flex-wrap items-center justify-center gap-3">
                                    <button
                                        type="button"
                                        data-messages-new-dm
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                                    >
                                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                                        New message
                                    </button>
                                    <button
                                        type="button"
                                        data-messages-new-group
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold bg-primary text-white hover:bg-primary/90 transition-colors"
                                    >
                                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                                        Create group
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- New DM modal --}}
            <div
                data-messages-dm-modal
        class="hidden fixed inset-0 z-[70] items-center justify-center p-4 bg-slate-900/40"
        role="dialog"
        aria-modal="true"
        aria-labelledby="new-dm-title"
    >
        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h2 id="new-dm-title" class="text-lg font-bold text-slate-900 dark:text-slate-100">New message</h2>
                <button type="button" data-messages-close-modal class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('messages.store') }}" class="p-5 space-y-4">
                @csrf
                <div class="relative">
                    <label for="dm-user-search" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Search for someone</label>
                    <input
                        type="text"
                        id="dm-user-search"
                        data-messages-user-search
                        data-messages-search-target="dm"
                        autocomplete="off"
                        placeholder="Search by name or handle..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                    <input type="hidden" name="user_id" data-messages-selected-user-id />
                    <div
                        data-messages-search-results="dm"
                        class="hidden absolute left-0 right-0 top-full z-10 mt-1 max-h-60 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg"
                    ></div>
                </div>
                <div data-messages-selected-user="dm" class="hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3"></div>
                <button
                    type="submit"
                    data-messages-dm-submit
                    disabled
                    class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white hover:bg-primary/90 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Start conversation
                </button>
            </form>
            </div>
            </div>

            {{-- New group modal --}}
            <div
                data-messages-group-modal
        class="hidden fixed inset-0 z-[70] items-center justify-center p-4 bg-slate-900/40"
        role="dialog"
        aria-modal="true"
        aria-labelledby="new-group-title"
    >
        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white dark:bg-slate-900 z-10">
                <h2 id="new-group-title" class="text-lg font-bold text-slate-900 dark:text-slate-100">Create group</h2>
                <button type="button" data-messages-close-modal class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('messages.groups.store') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label for="group-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Group name</label>
                    <input
                        type="text"
                        id="group-name"
                        name="name"
                        required
                        maxlength="100"
                        value="{{ old('name') }}"
                        placeholder="e.g. Project team"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 @error('name') border-red-500 @enderror"
                    />
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="relative">
                    <label for="group-user-search" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Add members</label>
                    <input
                        type="text"
                        id="group-user-search"
                        data-messages-user-search
                        data-messages-search-target="group"
                        autocomplete="off"
                        placeholder="Search by name or handle..."
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                    <div
                        data-messages-search-results="group"
                        class="hidden absolute left-0 right-0 top-full z-10 mt-1 max-h-60 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg"
                    ></div>
                </div>
                <div data-messages-group-members class="flex flex-wrap gap-2"></div>
                @error('user_ids')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @error('user_ids.*')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <button
                    type="submit"
                    data-messages-group-submit
                    aria-disabled="true"
                    class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white hover:bg-primary/90 transition-colors opacity-40 cursor-not-allowed"
                >
                    Create group
                </button>
            </form>
        </div>
            </div>

            @if ($activeConversation?->isGroup())
                <x-group-members-modal :conversation="$activeConversation" />
            @endif
        </div>
    </section>
@endsection
