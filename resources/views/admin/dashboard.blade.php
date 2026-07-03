@extends('layouts.admin')

@section('title', 'Dashboard | Admin')
@section('heading', 'Dashboard')

@section('content')
    <div class="lg:hidden mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Dashboard</h1>
        <p class="text-sm text-slate-600 mt-1">Overview of your application.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Total Users</p>
            <p class="mt-2 text-4xl font-bold text-slate-900 font-display">{{ number_format($totalUsers) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Administrators</p>
            <p class="mt-2 text-4xl font-bold text-slate-900 font-display">{{ number_format($adminCount) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest font-mono">Recent Users</h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                View all &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Name</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Email</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Role</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentUsers as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest font-mono',
                                    'bg-blue-100 text-blue-700' => $user->isAdmin(),
                                    'bg-slate-100 text-slate-600' => ! $user->isAdmin(),
                                ])>
                                    {{ $user->role->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $user->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No users yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
