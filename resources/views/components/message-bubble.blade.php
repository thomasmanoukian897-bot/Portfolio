@props([
    'message',
    'isMine' => false,
])

@if ($message->isSystem())
    <div class="flex justify-center py-1" data-message-id="{{ $message->id }}">
        <p class="text-xs text-slate-500 dark:text-slate-400 text-center px-3 py-1.5 rounded-full bg-slate-100/80 dark:bg-slate-800/80">
            {{ $message->body }}
        </p>
    </div>
@else
    <div
        @class([
            'flex',
            'justify-end' => $isMine,
            'justify-start' => ! $isMine,
        ])
        data-message-id="{{ $message->id }}"
    >
        <div @class([
            'flex items-start gap-3 max-w-[85%] md:max-w-[70%]',
            'flex-row-reverse' => $isMine,
        ])>
            @if (! $isMine)
                <x-user-avatar :user="$message->user" size="sm" class="mt-0.5" />
            @endif

            <div>
                @if (! $isMine)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 px-1">
                        <a
                            href="{{ route('users.show', $message->user) }}"
                            class="hover:text-primary transition-colors"
                        >
                            {{ $message->user->name }}
                        </a>
                    </p>
                @endif
                <div @class([
                    'rounded-2xl px-4 py-2.5 text-sm leading-relaxed',
                    'bg-primary text-white rounded-br-md' => $isMine,
                    'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-bl-md' => ! $isMine,
                ])>
                    <p class="whitespace-pre-wrap break-words">{{ $message->body }}</p>
                </div>
                <p @class([
                    'text-[11px] text-slate-400 mt-1 px-1',
                    'text-right' => $isMine,
                ])>
                    {{ $message->created_at?->format('g:i A') }}
                </p>
            </div>
        </div>
    </div>
@endif
