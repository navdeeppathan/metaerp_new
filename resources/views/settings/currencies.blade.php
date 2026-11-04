@extends('layouts.app')

@section('title', 'Currencies Configuration')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">tune</span>
                Currencies
            </h1>
            <p class="text-sm text-slate-500">Configure global lookup reference data for ERP transactions</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-xs font-bold">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Name / Title</th>
                    <th class="p-4">Created Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-4 font-mono text-xs text-slate-400">#{{ $item->id }}</td>
                    <td class="p-4 font-bold text-slate-800">{{ $item->name ?? $item->currencyCode ?? $item->countryName ?? 'Item' }}</td>
                    <td class="p-4 text-xs text-slate-500">{{ $item->created_at ?? 'System Seed' }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="p-8 text-center text-slate-400">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if(method_exists($items, 'links') && $items->hasPages())
        <div class="p-4 border-t border-slate-200">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
