@props([
    'conversation',
    'size' => 'md',
])

@php
    $sizeClasses = match ($size) {
        'xs' => 'w-5 h-5 text-[10px]',
        'nav' => 'w-7 h-7 text-xs',
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-20 h-20 text-2xl',
        'xl' => 'w-28 h-28 md:w-36 md:h-36 text-4xl md:text-5xl',
        default => 'w-9 h-9 text-sm',
    };
@endphp

<div {{ $attributes->merge(['class' => "shrink-0 rounded-full overflow-hidden flex items-center justify-center font-bold {$sizeClasses}"]) }}>
    @if ($conversation->avatarUrl())
        <img
            src="{{ $conversation->avatarUrl() }}"
            alt="{{ $conversation->name ?? 'Group' }} avatar"
            class="w-full h-full object-cover"
        />
    @else
        <span class="w-full h-full flex items-center justify-center bg-primary/15 text-primary border border-primary/25">
            @if ($conversation->isGroup())
                <i class="fa-solid fa-users {{ $size === 'sm' ? 'text-sm' : 'text-base' }}" aria-hidden="true"></i>
            @else
                {{ $conversation->avatarInitial() }}
            @endif
        </span>
    @endif
</div>
