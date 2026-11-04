@extends('layouts.app')

@section('title', 'Users - MetaERP')

@section('content')
<div class="space-y-4" x-data="{ search: '', showModal: false, selected: [], selectAll: false }">
    <!-- Header with Title, Search & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
        <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">
            Users
        </h1>

        <div class="flex items-center gap-3">
            <!-- Search User Pill Input -->
            <form method="GET" action="{{ route('users.index') }}" class="relative w-full sm:w-72">
                <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">search</span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q', request('search')) }}"
                    placeholder="Search user"
                    class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200/90 rounded-full text-xs text-slate-800 placeholder-slate-400 shadow-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition"
                >
            </form>

            <!-- Add User Pill Button -->
            <a
                href="{{ route('users.create') }}"
                class="rounded-full bg-[#4f46e5] hover:bg-[#4338ca] text-white px-5 py-2 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors shrink-0 cursor-pointer"
            >
                <span class="material-icons text-sm font-bold leading-none">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/70 shadow-xs overflow-hidden min-h-[calc(100vh-210px)] flex flex-col justify-between">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-100 text-slate-800 text-xs sm:text-sm font-semibold select-none">
                        <th class="py-4 px-6 w-14 text-left">
                            <input
                                type="checkbox"
                                x-model="selectAll"
                                @change="selected = selectAll ? [{{ $users->pluck('id')->join(',') }}] : []"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer"
                            >
                        </th>
                        <th class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">Name</th>
                        <th class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">Username</th>
                        <th class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">Display Name</th>
                        <th class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm text-center w-28">Active</th>
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm">
                    @forelse($users as $user)
                    <tr
                        class="hover:bg-slate-50/70 transition-colors cursor-pointer group"
                        :class="{ 'bg-slate-50/90': selected.includes({{ $user->id }}) }"
                        onclick="if (!event.target.closest('input')) window.location.href = '{{ route('users.show', $user->id) }}'"
                    >
                        <!-- Checkbox -->
                        <td class="py-5 px-6 w-14 text-left" onclick="event.stopPropagation()">
                            <input
                                type="checkbox"
                                :value="{{ $user->id }}"
                                x-model="selected"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer"
                            >
                        </td>

                        <!-- Name with Avatar -->
                        <td class="py-5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 font-semibold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-normal text-slate-800">
                                    {{ $user->name }}
                                </span>
                            </div>
                        </td>

                        <!-- Username -->
                        <td class="py-5 px-6 text-slate-800 font-normal">
                            {{ $user->username }}
                        </td>

                        <!-- Display Name -->
                        <td class="py-5 px-6 text-slate-800 font-normal">
                            {{ $user->displayName ?? $user->name }}
                        </td>

                        <!-- Active Status Icon -->
                        <td class="py-5 px-6 text-center">
                            @if($user->active)
                                <span class="material-icons text-emerald-500 text-lg leading-none inline-block align-middle" title="Active">
                                    check_circle_outline
                                </span>
                            @else
                                <span class="material-icons text-red-500 text-lg leading-none inline-block align-middle" title="Inactive">
                                    remove_circle_outline
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center text-slate-400 text-sm">
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->total() > 0)
            <div class="px-6 py-3.5 border-t border-slate-100">
                @include('partials.pagination', ['paginator' => $users])
            </div>
        @endif
    </div>
</div>
@endsection
