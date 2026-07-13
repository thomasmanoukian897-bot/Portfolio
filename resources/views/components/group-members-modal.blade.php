@props([
    'conversation',
])

@php
    $members = $conversation->users->sortBy('name')->values();
    $viewer = auth()->user();
    $canKickMembers = $conversation->isAdmin($viewer);
@endphp

<div
    data-messages-group-members-modal
    class="hidden fixed inset-0 z-[70] items-center justify-center p-4 bg-slate-900/40"
    role="dialog"
    aria-modal="true"
    aria-labelledby="group-members-title"
>
    <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 shrink-0 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h2 id="group-members-title" class="text-lg font-bold text-slate-900 dark:text-slate-100">
                    Group members
                </h2>
                <button type="button" data-messages-close-modal class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('messages.groups.name.update', $conversation) }}" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <label for="group-members-name" class="sr-only">Group name</label>
                <input
                    type="text"
                    id="group-members-name"
                    name="name"
                    required
                    maxlength="100"
                    value="{{ $conversation->name }}"
                    class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-primary focus:ring-2 focus:ring-primary/20"
                />
                <button
                    type="submit"
                    class="shrink-0 rounded-xl bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-primary/90 transition-colors"
                >
                    Save
                </button>
            </form>

            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ $members->count() }} {{ str('member')->plural($members->count()) }}
            </p>
        </div>

        <div class="overflow-y-auto" role="list">
            @foreach ($members as $member)
                <div class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/70" role="listitem">
                    <a href="{{ route('users.show', $member) }}" class="flex items-center gap-3 min-w-0 flex-1">
                        <x-user-avatar :user="$member" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">
                                {{ $member->name }}
                                @if ($conversation->isAdmin($member))
                                    <span class="ml-1 text-xs font-medium text-primary">Admin</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ '@'.$member->handle }}</p>
                        </div>
                    </a>

                    @if ($canKickMembers && ! $conversation->isAdmin($member))
                        <form
                            method="POST"
                            action="{{ route('messages.groups.members.kick', [$conversation, $member]) }}"
                            onsubmit="return confirm('Remove {{ $member->name }} from this group?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                aria-label="Remove {{ $member->name }}"
                                title="Remove member"
                            >
                                <i class="fa-solid fa-user-minus" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
