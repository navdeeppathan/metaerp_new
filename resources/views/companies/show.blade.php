@extends('layouts.app')

@section('title', $company->name . ' - Company Profile')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
                {{ substr($company->name, 0, 2) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $company->name }}</h1>
                <p class="text-sm text-slate-500 font-mono">Code: {{ $company->code }} | VAT: {{ $company->vatNumber ?? 'N/A' }}</p>
            </div>
        </div>
        <a href="{{ route('companies.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg">
            Back to Companies
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Details Card -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Company Information</h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400">Email:</span> <span class="font-medium text-slate-700">{{ $company->email ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Phone:</span> <span class="font-medium text-slate-700">{{ $company->phone ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Website:</span> <span class="font-medium text-slate-700">{{ $company->website ?? 'N/A' }}</span></div>
                <div><span class="text-slate-400">Status:</span> <span class="font-bold text-emerald-600">{{ $company->active ? 'ACTIVE' : 'INACTIVE' }}</span></div>
            </div>
        </div>

        <!-- Contacts Card -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm md:col-span-2 space-y-4">
            <h3 class="font-bold text-sm uppercase text-slate-700 border-b pb-2">Associated Contacts</h3>
            <div class="space-y-2">
                @forelse($company->contacts as $contact)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg text-xs">
                    <div>
                        <p class="font-bold text-slate-800">{{ $contact->name }}</p>
                        <p class="text-slate-500">{{ $contact->email }} | {{ $contact->phone }}</p>
                    </div>
                    <a href="{{ route('contacts.show', ['id' => $contact->id]) }}" class="text-blue-600 font-semibold">View</a>
                </div>
                @empty
                <p class="text-xs text-slate-400">No contacts linked to this company.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
