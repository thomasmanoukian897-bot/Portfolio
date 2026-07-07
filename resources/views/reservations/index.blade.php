@extends('layouts.app')

@section('title', 'Book a Session | Digital Builder')

@section('content')
    <section class="relative min-h-[85vh] flex flex-col items-center justify-center pt-16 pb-24 overflow-hidden mesh-gradient">
        <div class="absolute inset-0 animated-grid opacity-60 pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-2xl px-6">
            <div class="text-center mb-8 space-y-4">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-bold uppercase tracking-widest font-mono">
                        Schedule a Call
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold tracking-tighter text-slate-900 dark:text-slate-100 font-display">
                    Book a <span class="gradient-text">Session</span>
                </h1>

                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Pick an available hour for a {{ $durationMinutes }}-minute session. All times are shown in {{ $timezone }}.
                </p>
            </div>

            <div class="glass-card rounded-2xl p-8 shadow-md space-y-8">
                @if (session('success'))
                    <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-950/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                        <p class="font-semibold">{{ session('success') }}</p>
                    </div>
                @endif

                @unless ($calendarConfigured)
                    <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                        <p class="font-semibold">Google Calendar is not configured.</p>
                        <p class="mt-1">Reservations are stored locally. Add your calendar credentials to sync with Google Calendar.</p>
                    </div>
                @endunless

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                        <p class="font-semibold">Please fix the errors below</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="GET" action="{{ route('reservations.index') }}" class="space-y-3">
                    <label for="date" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                        Select a Date
                    </label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            id="date"
                            name="date"
                            type="date"
                            value="{{ $selectedDate }}"
                            min="{{ $minDate }}"
                            max="{{ $maxDate }}"
                            required
                            class="flex-1 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
                        />
                        <button
                            type="submit"
                            class="px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider btn-gradient text-white active:scale-95 transition-transform"
                        >
                            Show Times
                        </button>
                    </div>
                </form>

                <div class="space-y-4">
                    <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                        Available Times — {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                    </h2>

                    @if ($slots->isEmpty())
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            No available time slots for this date. Please try another day.
                        </p>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="slot-grid">
                            @foreach ($slots as $slot)
                                <button
                                    type="button"
                                    data-slot="{{ $slot->toIso8601String() }}"
                                    data-slot-label="{{ $slot->format('g:i A') }}"
                                    class="slot-button rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm font-semibold text-slate-800 dark:text-slate-100 hover:border-primary hover:bg-primary/5 dark:hover:bg-primary/10 transition-all"
                                >
                                    {{ $slot->format('g:i A') }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <form
                    id="reservation-form"
                    method="POST"
                    action="{{ route('reservations.store') }}"
                    class="space-y-6 @if ($slots->isEmpty()) hidden @endif"
                >
                    @csrf

                    <input type="hidden" name="starts_at" id="starts_at" value="{{ old('starts_at') }}" />

                    <div id="selected-slot-display" class="rounded-xl border border-primary/20 bg-primary/5 dark:bg-primary/10 px-4 py-3 text-sm text-primary font-semibold @unless(old('starts_at')) hidden @endunless">
                        @if (old('starts_at'))
                            Selected: {{ \Carbon\Carbon::parse(old('starts_at'))->timezone($timezone)->format('g:i A') }}
                        @endif
                    </div>

                    <div class="space-y-2">
                        <label for="name" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                            Name
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', auth()->user()?->name) }}"
                            required
                            autocomplete="name"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('name') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="Your name"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                            Email Address
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', auth()->user()?->email) }}"
                            required
                            autocomplete="email"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="you@company.com"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="notes" class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest font-mono">
                            Notes <span class="text-slate-400 font-normal normal-case">(optional)</span>
                        </label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-3 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600 resize-none @error('notes') border-red-400 focus:ring-red-500 @enderror"
                            placeholder="Anything you'd like us to know before the session..."
                        >{{ old('notes') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        id="submit-reservation"
                        disabled
                        class="w-full py-3.5 rounded-xl text-sm font-bold uppercase tracking-wider btn-gradient text-white active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                    >
                        Confirm Reservation
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slotButtons = document.querySelectorAll('.slot-button');
            const startsAtInput = document.getElementById('starts_at');
            const selectedDisplay = document.getElementById('selected-slot-display');
            const submitButton = document.getElementById('submit-reservation');
            const reservationForm = document.getElementById('reservation-form');

            const selectSlot = (button) => {
                slotButtons.forEach((btn) => {
                    btn.classList.remove('border-primary', 'bg-primary/10', 'ring-2', 'ring-primary/30');
                    btn.classList.add('border-slate-200', 'dark:border-slate-600');
                });

                button.classList.remove('border-slate-200', 'dark:border-slate-600');
                button.classList.add('border-primary', 'bg-primary/10', 'ring-2', 'ring-primary/30');

                startsAtInput.value = button.dataset.slot;
                selectedDisplay.textContent = `Selected: ${button.dataset.slotLabel}`;
                selectedDisplay.classList.remove('hidden');
                submitButton.disabled = false;
                reservationForm.classList.remove('hidden');
            };

            slotButtons.forEach((button) => {
                button.addEventListener('click', () => selectSlot(button));
            });

            if (startsAtInput.value) {
                const matchingButton = Array.from(slotButtons).find(
                    (button) => button.dataset.slot === startsAtInput.value
                );

                if (matchingButton) {
                    selectSlot(matchingButton);
                } else {
                    submitButton.disabled = false;
                    reservationForm.classList.remove('hidden');
                }
            }
        });
    </script>
@endpush
