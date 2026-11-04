@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs min-h-[calc(100vh-170px)] p-6 sm:p-8 flex flex-col justify-between"
     x-data="{
         selectedItems: [],
         selectAll: false,
         toggleSelectAll() {
             this.selectAll = !this.selectAll;
             this.selectedItems = this.selectAll ? [{{ $products->pluck('id')->join(',') }}] : [];
         }
     }">

    <div>
        <!-- Top Action Bar: Title, Search Pill, and Add Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 sm:mb-8">
            <div>
                <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">Products</h1>
            </div>

            <div class="flex items-center gap-3">
                <!-- Search Product Pill Matching Exact Screenshot -->
                <form method="GET" action="{{ route('products.index') }}" class="relative w-full sm:w-72">
                    <span class="material-icons-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">search</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q', request('search')) }}"
                        placeholder="Search product"
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs transition"
                    >
                </form>

                <!-- + Add Button Matching Screenshot -->
                <a
                    href="{{ route('products.create') }}"
                    class="px-5 py-2 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-full text-xs sm:text-sm font-semibold flex items-center gap-1.5 shadow-xs transition-colors shrink-0 cursor-pointer"
                >
                    <span class="material-icons text-base font-bold leading-none">add</span>
                    <span>Add</span>
                </a>
            </div>
        </div>

        <!-- Feedback Alert Messages -->
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Products Table Matching Exact Screenshot -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-transparent text-slate-800 text-xs sm:text-sm font-semibold select-none">
                        <th scope="col" class="py-4 px-6 w-14 text-left">
                            <input
                                type="checkbox"
                                x-model="selectAll"
                                @change="toggleSelectAll()"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer"
                            >
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm w-3/5">
                            Name
                        </th>
                        <th scope="col" class="py-4 px-6 font-semibold text-slate-800 text-xs sm:text-sm">
                            Symbol
                        </th>
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm">
                    @forelse($products as $product)
                        <tr
                            onclick="if(!event.target.closest('input')) window.location='{{ route('products.show', $product->id) }}'"
                            :class="{ 'bg-slate-50/80': selectedItems.includes({{ $product->id }}) }"
                            class="hover:bg-slate-50/70 transition-colors cursor-pointer group"
                        >
                            <td class="py-5 px-6 w-14 text-left" onclick="event.stopPropagation()">
                                <input
                                    type="checkbox"
                                    :value="{{ $product->id }}"
                                    x-model="selectedItems"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer"
                                >
                            </td>
                            <td class="py-5 px-6 text-slate-800 font-normal">
                                <a href="{{ route('products.show', $product->id) }}" class="hover:text-indigo-600 transition">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="py-5 px-6 text-slate-800 font-normal">
                                {{ $product->chemicalFormula ?? $product->code ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-16 text-center text-slate-400 text-sm">
                                There are no products!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Pagination Matching Material UI DataGrid -->
    @if($products->total() > 0)
        @include('partials.pagination', ['paginator' => $products])
    @endif

</div>
@endsection
