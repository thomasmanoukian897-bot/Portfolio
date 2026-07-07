@props(['reservation'])

@php
    $timezone = config('reservations.timezone');
    $now = now()->timezone($timezone);
    $isUpcoming = $reservation->starts_at->gte($now);
@endphp

<article {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm']) }}>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span @class([
                    'inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono',
                    'bg-green-50 text-green-700 dark:bg-green-950/50 dark:text-green-400' => $isUpcoming,
                    'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' => ! $isUpcoming,
                ])>
                    {{ $isUpcoming ? 'Upcoming' : 'Past' }}
                </span>
            </div>

            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 font-display">
                {{ $reservation->starts_at->format('l, F j, Y') }}
            </h3>

            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                <i class="fa-solid fa-clock mr-1.5 opacity-60" aria-hidden="true"></i>
                {{ $reservation->starts_at->format('g:i A') }}
                &ndash;
                {{ $reservation->ends_at->format('g:i A') }}
                <span class="text-slate-400 dark:text-slate-500">({{ $timezone }})</span>
            </p>
        </div>

        <div class="flex items-start gap-2 shrink-0">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 border border-primary/20">
                <i class="fa-solid fa-calendar-check text-primary" aria-hidden="true"></i>
            </div>

            @can('delete', $reservation)
                <form
                    method="POST"
                    action="{{ route('reservations.destroy', $reservation) }}"
                    onsubmit="return confirm('Cancel this booking? This cannot be undone.')"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        aria-label="Delete booking"
                        class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-950 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 transition-colors"
                    >
                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @if ($reservation->notes)
        <div class="mt-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-widest font-mono text-slate-500 dark:text-slate-400 mb-1">Notes</p>
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $reservation->notes }}</p>
        </div>
    @endif
</article>
