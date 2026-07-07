@extends('layouts.admin')

@section('title', 'Bookings | Admin')
@section('heading', 'Bookings')

@section('content')
    <div class="lg:hidden mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Bookings</h1>
        <p class="text-sm text-slate-600 mt-1">Manage session reservations.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name or email..."
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-xs transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-600"
                />
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-wider transition-colors"
                >
                    Search
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Name</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Email</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Date &amp; Time</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Account</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Status</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $now = now()->timezone($timezone);
                    @endphp

                    @forelse ($bookings as $booking)
                        @php
                            $isUpcoming = $booking->starts_at->gte($now);
                        @endphp

                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $booking->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $booking->email }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                <p class="font-medium text-slate-800">{{ $booking->starts_at->format('M j, Y') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $booking->starts_at->format('g:i A') }}
                                    &ndash;
                                    {{ $booking->ends_at->format('g:i A') }}
                                    ({{ $timezone }})
                                </p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                @if ($booking->user)
                                    <a href="{{ route('admin.users.edit', $booking->user) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $booking->user->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Guest</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono',
                                    'bg-green-100 text-green-700' => $isUpcoming,
                                    'bg-slate-100 text-slate-600' => ! $isUpcoming,
                                ])>
                                    {{ $isUpcoming ? 'Upcoming' : 'Past' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $booking)
                                        <a
                                            href="{{ route('admin.bookings.edit', $booking) }}"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                                        >
                                            Edit
                                        </a>
                                    @endcan

                                    @can('delete', $booking)
                                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Cancel this booking?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                aria-label="Cancel booking"
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs bg-red-50 hover:bg-red-100 text-red-700 transition-colors"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">No bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
@endsection
