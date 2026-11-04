@extends('layouts.app')

@section('title', $contact->name . ' - Contact Profile')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $contact->name }}</h1>
            <p class="text-sm text-slate-500">Company: <a href="{{ route('companies.show', ['id' => $contact->metaCompany?->id ?? 0]) }}" class="text-blue-600 underline">{{ $contact->metaCompany?->name ?? 'N/A' }}</a></p>
        </div>
        <a href="{{ route('contacts.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg">
            Back to Contacts
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Contact Details</h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400">Email:</span> <span class="font-medium text-slate-700">{{ $contact->email ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Phone:</span> <span class="font-medium text-slate-700">{{ $contact->phone ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Mobile:</span> <span class="font-medium text-slate-700">{{ $contact->mobile ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Department:</span> <span class="font-medium text-slate-700">{{ $contact->department?->name ?? 'N/A' }}</span></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm md:col-span-2 space-y-3">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Assigned Products & Roles</h3>
            <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                    @forelse($contact->types as $t)
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-bold text-xs rounded-full border border-blue-200">
                        {{ $t->name }}
                    </span>
                    @empty
                    <span class="text-xs text-slate-400">No types assigned.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
