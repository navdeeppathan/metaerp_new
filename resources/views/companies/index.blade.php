@extends('layouts.app')

@section('title', 'Meta Group')

@section('content')
<div class="space-y-4 max-w-7xl mx-auto">
    <!-- Header with Title & Action Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1 pb-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0e1726] tracking-tight">Meta Group</h1>

        <div class="flex items-center gap-3">
            <!-- Search Pill -->
            <form method="GET" action="{{ route('companies.index') }}" class="relative w-64 sm:w-72">
                <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search product"
                    class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-600 shadow-xs transition"
                >
            </form>

            <!-- + Add Button -->
            <a
                href="{{ route('companies.create') }}"
                class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white text-sm font-semibold rounded-full shadow-xs transition-colors shrink-0"
            >
                <span class="material-icons text-base">add</span>
                <span>Add</span>
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Companies Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-800 text-sm font-bold bg-white">
                        <th class="w-12 px-6 py-4">
                            <input
                                type="checkbox"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Name</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Code</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">VAT</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Company Number</th>
                        <th class="px-6 py-4 font-bold text-slate-800 text-sm">Phone Number</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($companies as $company)
                        <tr
                            onclick="window.location='{{ route('companies.show', $company->id) }}'"
                            class="hover:bg-slate-50/80 cursor-pointer transition-colors"
                        >
                            <td class="w-12 px-6 py-4.5" onclick="event.stopPropagation()">
                                <input
                                    type="checkbox"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                >
                            </td>
                            <td class="px-6 py-4.5 font-medium text-slate-900">
                                {{ $company->name }}
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $company->code }}
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $company->vatNumber }}
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $company->companyNumber }}
                            </td>
                            <td class="px-6 py-4.5 text-slate-700">
                                {{ $company->phoneNumber }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No companies found in Meta Group.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
