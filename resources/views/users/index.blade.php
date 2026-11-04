@extends('layouts.app')

@section('title', 'Users - MetaERP')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{ search: '', showModal: false, selected: [], selectAll: false }">
    <!-- Header with Search & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Users
        </h1>

        <div class="flex items-center gap-3">
            <!-- Search User Pill Input -->
            <div class="flex items-center bg-[#f1f5f9] rounded-full px-3.5 py-1.5 w-60 sm:w-72 border border-slate-200/60 focus-within:border-indigo-400 focus-within:bg-white transition-all">
                <span class="material-icons text-slate-400 text-lg mr-2 leading-none">search</span>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search user"
                    class="bg-transparent border-0 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none w-full"
                >
            </div>

            <!-- Add User Pill Button -->
            <a
                href="{{ route('users.create') }}"
                class="rounded-full bg-[#4f46e5] hover:bg-[#4338ca] text-white px-5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 shadow-sm transition-colors duration-150 cursor-pointer"
            >
                <span class="material-icons text-base leading-none">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl border border-slate-200/70 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-white">
                        <th class="py-4 px-4 sm:px-6 w-12 text-center">
                            <input
                                type="checkbox"
                                x-model="selectAll"
                                @change="selected = selectAll ? [{{ $users->pluck('id')->join(',') }}] : []"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </th>
                        <th class="py-4 px-4 text-xs font-bold text-slate-900 tracking-normal">Name</th>
                        <th class="py-4 px-4 text-xs font-bold text-slate-900 tracking-normal">Username</th>
                        <th class="py-4 px-4 text-xs font-bold text-slate-900 tracking-normal">Display Name</th>
                        <th class="py-4 px-4 sm:px-6 text-xs font-bold text-slate-900 tracking-normal text-left">Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr
                        x-show="!search || '{{ strtolower($user->name . ' ' . $user->username . ' ' . ($user->displayName ?? '')) }}'.includes(search.toLowerCase())"
                        class="hover:bg-slate-50/70 transition-colors duration-100 cursor-pointer"
                        @click="if (!$event.target.closest('input')) window.location.href = '{{ route('users.show', $user->id) }}'"
                    >
                        <!-- Checkbox -->
                        <td class="py-3.5 px-4 sm:px-6 text-center">
                            <input
                                type="checkbox"
                                :value="{{ $user->id }}"
                                x-model="selected"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </td>

                        <!-- Name with Avatar -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-300/80 text-white flex items-center justify-center shrink-0">
                                    <span class="material-icons text-slate-500 text-base leading-none">person</span>
                                </div>
                                <span class="font-normal text-xs sm:text-sm text-slate-800">
                                    {{ $user->name }}
                                </span>
                            </div>
                        </td>

                        <!-- Username -->
                        <td class="py-3.5 px-4 text-xs sm:text-sm text-slate-700 font-normal">
                            {{ $user->username }}
                        </td>

                        <!-- Display Name -->
                        <td class="py-3.5 px-4 text-xs sm:text-sm text-slate-700 font-normal">
                            {{ $user->displayName ?? $user->name }}
                        </td>

                        <!-- Active Status Icon -->
                        <td class="py-3.5 px-4 sm:px-6 text-left">
                            @if($user->active)
                                <span class="material-icons text-emerald-500 text-lg sm:text-xl leading-none inline-block align-middle" title="Active">
                                    check_circle_outline
                                </span>
                            @else
                                <span class="material-icons text-red-500 text-lg sm:text-xl leading-none inline-block align-middle" title="Inactive">
                                    remove_circle_outline
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-200/80 bg-white">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Create User Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showModal = false"></div>
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full z-10 overflow-hidden transform transition-all">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-bold text-base text-slate-800">Add New User</h3>
                        <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                            <span class="material-icons text-xl">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="e.g. John Doe">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username / Email *</label>
                            <input type="text" name="username" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="john@metaenterprices.co.uk">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Display Name</label>
                            <input type="text" name="displayName" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="John">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password *</label>
                            <input type="password" name="password" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border focus:ring-1 focus:ring-indigo-500 focus:outline-none" placeholder="Min 6 characters">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Role *</label>
                            <select name="role" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                <option value="ROLE_USER">Operator / User</option>
                                <option value="ROLE_ADMIN">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-full bg-[#4f46e5] hover:bg-[#4338ca] text-white transition-colors">
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
