@extends('layouts.admin')

@section('title', 'Users | Admin')
@section('heading', 'Users')

@section('content')
    <div class="lg:hidden mb-6">
        <h1 class="text-2xl font-bold text-slate-900 font-display">Users</h1>
        <p class="text-sm text-slate-600 mt-1">Manage registered accounts.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
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
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Role</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono">Joined</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-widest font-mono text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
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
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                                    >
                                        Edit
                                    </a>

                                    @can('delete', $user)
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                aria-label="Delete"
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
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
