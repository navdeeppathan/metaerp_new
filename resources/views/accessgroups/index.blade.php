@extends('layouts.app')

@section('title', 'Access Groups & Permissions')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">security</span>
                Access Groups
            </h1>
            <p class="text-sm text-slate-500">Configure role-based access control, stage permissions, and features</p>
        </div>
        <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            <span class="material-icons text-base">add_moderator</span>
            New Access Group
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($groups as $group)
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg text-slate-800">{{ $group->name }}</h3>
                <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $group->users?->count() ?? 0 }} Members
                </span>
            </div>
            <p class="text-xs text-slate-500">{{ $group->description ?? 'No description provided' }}</p>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Active: {{ $group->active ? 'Yes' : 'No' }}</span>
                <span class="font-mono">ID: #{{ $group->id }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-2 p-8 text-center text-slate-400 bg-white rounded-xl border">No access groups defined.</div>
        @endforelse
    </div>

    <!-- Create Group Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60" @click="showModal = false"></div>
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full z-10 overflow-hidden">
                <form action="{{ route('accessgroups.store') }}" method="POST">
                    @csrf
                    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">New Access Group</h3>
                        <button type="button" @click="showModal = false"><span class="material-icons">close</span></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Group Name *</label>
                            <input type="text" name="name" required class="w-full text-sm border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full text-sm border-slate-200 rounded-lg"></textarea>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
