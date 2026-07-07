@extends('layouts.admin')

@section('title', "Edit Booking | Admin")
@section('heading', 'Edit Booking')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.bookings.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                &larr; Back to bookings
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 space-y-8">
            <div>
                <h2 class="text-xl font-bold text-slate-900 font-display">Edit Booking</h2>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $reservation->name }} &middot; {{ $reservation->email }}
                </p>
            </div>

            <form method="GET" action="{{ route('admin.bookings.edit', $reservation) }}" class="space-y-3">
                <label for="date" class="block text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                    Select a Date
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        id="date"
                        name="date"
                        type="date"
                        value="{{ $selectedDate }}"
                        max="{{ $maxDate }}"
                        required
                        class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
                    />
                    <button
                        type="submit"
                        class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-wider transition-colors"
                    >
                        Show Times
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.bookings.update', $reservation) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <input type="hidden" name="starts_at" id="starts_at" value="{{ $selectedStartsAt }}" />

                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">
                        Available Times — {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                    </h3>

                    @error('starts_at')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($slots->isEmpty())
                        <p class="text-sm text-slate-600">
                            No time slots for this date. Please try another day.
                        </p>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="slot-grid">
                            @foreach ($slots as $slot)
                                <button
                                    type="button"
                                    data-slot="{{ $slot['starts_at']->toIso8601String() }}"
                                    data-slot-label="{{ $slot['starts_at']->format('g:i A') }}"
                                    @disabled(! $slot['available'])
                                    @if (($slot['unavailable_reason'] ?? null) === 'past')
                                        title="Not Available"
                                    @elseif (! $slot['available'])
                                        title="Reserved"
                                    @endif
                                    class="slot-button rounded-xl border px-4 py-3 text-sm font-semibold transition-all @if ($slot['available']) border-slate-200 bg-white text-slate-800 hover:border-blue-600 hover:bg-blue-50 @elseif (($slot['unavailable_reason'] ?? null) === 'past') border-red-200/60 bg-red-50 text-red-500 cursor-not-allowed opacity-70 @else border-slate-200/50 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60 @endif"
                                >
                                    {{ $slot['starts_at']->format('g:i A') }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div id="selected-slot-display" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 font-semibold hidden"></div>

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        id="submit-booking"
                        @disabled($slots->isEmpty())
                        class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Save Changes
                    </button>

                    <a
                        href="{{ route('admin.bookings.index') }}"
                        class="px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slotButtons = document.querySelectorAll('.slot-button');
            const startsAtInput = document.getElementById('starts_at');
            const selectedDisplay = document.getElementById('selected-slot-display');

            const selectSlot = (button) => {
                slotButtons.forEach((btn) => {
                    btn.classList.remove('border-blue-600', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                    btn.classList.add('border-slate-200');
                });

                button.classList.remove('border-slate-200');
                button.classList.add('border-blue-600', 'bg-blue-50', 'ring-2', 'ring-blue-200');

                startsAtInput.value = button.dataset.slot;
                selectedDisplay.textContent = `Selected: ${button.dataset.slotLabel}`;
                selectedDisplay.classList.remove('hidden');
            };

            slotButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.disabled) {
                        return;
                    }

                    selectSlot(button);
                });
            });

            if (startsAtInput.value) {
                const matchingButton = Array.from(slotButtons).find(
                    (button) => button.dataset.slot === startsAtInput.value
                );

                if (matchingButton && ! matchingButton.disabled) {
                    selectSlot(matchingButton);
                }
            }
        });
    </script>
@endpush
