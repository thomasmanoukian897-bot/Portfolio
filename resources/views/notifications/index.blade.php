@extends('layouts.app')

@section('title', 'Notifications | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-16 px-4 md:px-6 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-100/60 via-blue-50/20 to-transparent dark:from-slate-950/95 dark:via-slate-900/80 dark:to-slate-900/0 pointer-events-none"></div>

        <div class="relative max-w-xl mx-auto">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                <div class="px-4 py-5 border-b border-slate-200 dark:border-slate-700">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Notifications</h1>
                </div>

                @php
                    $sections = [
                        'today' => 'Today',
                        'this_month' => 'This month',
                        'earlier' => 'Earlier',
                    ];

                    $hasNotifications = collect($groupedNotifications)->flatten(1)->isNotEmpty();
                @endphp

                @if ($hasNotifications)
                    @foreach ($sections as $key => $label)
                        @if ($groupedNotifications[$key]->isNotEmpty())
                            <div @class(['border-t border-slate-200 dark:border-slate-700' => ! $loop->first])>
                                <h2 class="px-4 pt-4 pb-2 text-sm font-bold text-slate-900 dark:text-slate-100">
                                    {{ $label }}
                                </h2>

                                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($groupedNotifications[$key] as $notification)
                                        <x-notification-item
                                            :notification="$notification"
                                            :following-ids="$followingIds"
                                        />
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="px-4 py-16 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 mb-4">
                            <i class="fa-solid fa-bell text-2xl text-slate-400 dark:text-slate-500" aria-hidden="true"></i>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            No notifications yet. When someone follows you or likes your posts, you will see it here.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
