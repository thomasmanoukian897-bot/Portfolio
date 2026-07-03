@extends('layouts.app')

@section('title', 'Team Squad | Digital Builder')

@section('content')
    <section class="relative pt-24 pb-16 overflow-hidden mesh-gradient">
        <div class="absolute inset-0 animated-grid opacity-60 pointer-events-none"></div>

        <div class="relative z-10 text-center px-6 max-w-4xl mx-auto space-y-6">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-slate-200 bg-slate-50/80 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                <span class="text-xs text-slate-700 font-bold uppercase tracking-widest font-mono">
                    API-Sports
                </span>
            </div>

            <h1 class="text-4xl sm:text-5xl font-bold tracking-tighter leading-tight text-slate-900 font-display">
                Team <span class="gradient-text">Squad</span>
            </h1>

            <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                Live squad data from the Football API for team #{{ config('services.api_sports.team_id') }}.
            </p>
        </div>
    </section>

    <section class="px-6 pb-20 max-w-7xl mx-auto">
        @if ($error)
            <div class="mb-8 rounded-2xl border border-red-200 bg-red-50 p-6">
                <h2 class="text-lg font-bold text-red-800 mb-2">API request failed</h2>
                <p class="text-sm text-red-700 mb-2">HTTP status: {{ $status }}</p>
                <pre class="text-xs text-red-900 overflow-x-auto whitespace-pre-wrap font-mono">{{ $error }}</pre>
            </div>
        @endif

        @if ($data)
            @php
                $squad = $data['response'][0] ?? null;
                $team = $squad['team'] ?? null;
                $players = $squad['players'] ?? [];
            @endphp

            @if ($team)
                <div class="mb-10 flex flex-col sm:flex-row items-center gap-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                    @if (! empty($team['logo']))
                        <img
                            src="{{ $team['logo'] }}"
                            alt="{{ $team['name'] ?? 'Team logo' }}"
                            class="h-24 w-24 object-contain"
                        >
                    @endif
                    <div class="text-center sm:text-left">
                        <h2 class="text-3xl font-bold text-slate-900 font-display">{{ $team['name'] ?? 'Unknown team' }}</h2>
                        <p class="text-slate-500 mt-1 font-mono text-sm">Team ID: {{ $team['id'] ?? '—' }}</p>
                        <p class="text-slate-500 font-mono text-sm">{{ count($players) }} players in squad</p>
                    </div>
                </div>
            @endif

            @if (count($players) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-12">
                    @foreach ($players as $player)
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-4">
                                @if (! empty($player['photo']))
                                    <img
                                        src="{{ $player['photo'] }}"
                                        alt="{{ $player['name'] ?? 'Player' }}"
                                        class="h-14 w-14 rounded-full object-cover bg-slate-100"
                                    >
                                @endif
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ $player['name'] ?? '—' }}</p>
                                    <p class="text-sm text-slate-500">{{ $player['position'] ?? '—' }}</p>
                                </div>
                                @if (! empty($player['number']))
                                    <span class="ml-auto flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white font-mono">
                                        {{ $player['number'] }}
                                    </span>
                                @endif
                            </div>
                            @if (! empty($player['age']))
                                <p class="mt-3 text-xs text-slate-400 font-mono">Age: {{ $player['age'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-600 mb-12">No players found in the API response.</p>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-slate-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h3 class="text-sm font-bold text-white font-mono tracking-wider uppercase">Raw API Response</h3>
                </div>
                <pre class="p-6 text-xs text-emerald-400 overflow-x-auto font-mono leading-relaxed max-h-[32rem] overflow-y-auto">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @elseif (! $error)
            <p class="text-slate-600">No data returned from the API.</p>
        @endif
    </section>
@endsection
