@extends('layouts.app')

@section('title', 'Executive Power BI Dashboard - MetaERP')

@section('content')
<div x-data="{
    activeTab: 'overview',
    showFilters: true,
    mobileFiltersOpen: false,
    filterCompany: '{{ $selectedCompanyId ?? 'all' }}',
    filterStage: '{{ $selectedStageId ?? 'all' }}',
    filterCurrency: '{{ $selectedCurrency ?? 'all' }}',
    startDate: '{{ $startDate ?? '' }}',
    endDate: '{{ $endDate ?? '' }}',
    isApplying: false,
    applyFilters() {
        this.isApplying = true;
        const params = new URLSearchParams();
        if (this.filterCompany && this.filterCompany !== 'all') params.append('company_id', this.filterCompany);
        if (this.filterStage && this.filterStage !== 'all') params.append('stage_id', this.filterStage);
        if (this.filterCurrency && this.filterCurrency !== 'all') params.append('currency', this.filterCurrency);
        if (this.startDate) params.append('start_date', this.startDate);
        if (this.endDate) params.append('end_date', this.endDate);
        window.location.href = '{{ route('dashboards.v2') }}?' + params.toString();
    },
    resetFilters() {
        window.location.href = '{{ route('dashboards.v2') }}';
    }
}" class="max-w-[1700px] mx-auto px-2 sm:px-4 lg:px-6 space-y-4 sm:space-y-6 pb-20">

    <!-- TOP HEADER BAR (Fully Responsive) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs transition-all">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Branding & Title -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-tr from-indigo-600 via-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 shrink-0">
                    <span class="material-icons-outlined text-xl sm:text-2xl">insights</span>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white truncate">
                            MetaERP Power BI Analytics
                        </h1>
                        <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shrink-0">
                            v2.0 Power BI
                        </span>
                    </div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <span class="truncate">Trading Performance, Operational Stages & Financial BI</span>
                        <span class="hidden sm:inline w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                        @if($isSuperAdmin)
                            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium whitespace-nowrap">
                                <span class="material-icons-outlined text-xs">verified_user</span> Full Admin Scope
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium whitespace-nowrap">
                                <span class="material-icons-outlined text-xs">lock</span> Role Restricted View
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- View Switcher Tabs & Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                
                <!-- Tab pills with smooth horizontal scrolling on mobile -->
                <div class="bg-slate-100 dark:bg-slate-800 p-1 rounded-xl flex items-center text-xs font-semibold text-slate-600 dark:text-slate-300 overflow-x-auto custom-scrollbar">
                    <button type="button" @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'hover:text-slate-900 dark:hover:text-white'"
                            class="px-3 sm:px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <span class="material-icons-outlined text-base">dashboard</span>
                        <span>Overview</span>
                    </button>
                    <button type="button" @click="activeTab = 'dynamics'" 
                            :class="activeTab === 'dynamics' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'hover:text-slate-900 dark:hover:text-white'"
                            class="px-3 sm:px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <span class="material-icons-outlined text-base">trending_up</span>
                        <span>Dynamics & Stages</span>
                    </button>
                    @if($canViewFinancials)
                    <button type="button" @click="activeTab = 'financials'" 
                            :class="activeTab === 'financials' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'hover:text-slate-900 dark:hover:text-white'"
                            class="px-3 sm:px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <span class="material-icons-outlined text-base">account_balance</span>
                        <span>Financial BI</span>
                    </button>
                    @endif
                </div>

                <!-- Action buttons -->
                <div class="flex items-center gap-2">
                    <!-- Desktop Filter Toggle -->
                    <button type="button" @click="showFilters = !showFilters" 
                            class="hidden xl:inline-flex px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors items-center gap-1.5 shadow-xs whitespace-nowrap">
                        <span class="material-icons-outlined text-base" :class="showFilters ? 'text-indigo-600 dark:text-indigo-400' : ''">filter_alt</span>
                        <span x-text="showFilters ? 'Hide Slicers' : 'Show Slicers'"></span>
                    </button>

                    <!-- Mobile/Tablet Drawer Trigger -->
                    <button type="button" @click="mobileFiltersOpen = true" 
                            class="xl:hidden flex-1 sm:flex-initial inline-flex justify-center px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition-colors items-center gap-1.5 shadow-xs whitespace-nowrap">
                        <span class="material-icons-outlined text-base text-indigo-600">tune</span>
                        <span>Filters & Slicers</span>
                    </button>

                    <button type="button" onclick="window.print()" 
                            class="flex-1 sm:flex-initial inline-flex justify-center px-3.5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition-all shadow-xs items-center gap-1.5 whitespace-nowrap">
                        <span class="material-icons-outlined text-base">file_download</span>
                        <span>Export</span>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- 12 VIBRANT COUPLER KPI CARDS (Fully Responsive Grid) -->
    <div class="space-y-3">
        <!-- ROW 1 -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-3">
            
            <!-- KPI 1: Total Contracts -->
            <div class="bg-gradient-to-br from-[#4f46e5] to-[#3730a3] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-indigo-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Contracts</span>
                    <span class="material-icons-outlined text-indigo-200 text-sm sm:text-base">description</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1">
                    {{ number_format($kpis['total_contracts']) }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-indigo-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs text-emerald-300">check_circle</span>
                    <span>In active pipeline</span>
                </div>
            </div>

            <!-- KPI 2: Active Deals -->
            <div class="bg-gradient-to-br from-[#1e40af] to-[#1e3a8a] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-blue-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Active Deals</span>
                    <span class="material-icons-outlined text-blue-200 text-sm sm:text-base">handshake</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1">
                    {{ number_format($kpis['active_deals']) }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-blue-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs">sync</span>
                    <span>Purchases & Sales</span>
                </div>
            </div>

            <!-- KPI 3: Gross Sales Volume -->
            <div class="bg-gradient-to-br from-[#0284c7] to-[#0369a1] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-sky-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Gross Sales</span>
                    <span class="material-icons-outlined text-sky-200 text-sm sm:text-base">point_of_sale</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1" title="${{ number_format($kpis['total_sales'], 2) }}">
                    ${{ number_format($kpis['total_sales'] / 1000, 1) }}K
                </div>
                <div class="text-[10px] sm:text-[11px] text-sky-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs text-emerald-300">trending_up</span>
                    <span>Total Invoiced</span>
                </div>
            </div>

            <!-- KPI 4: Purchases Volume -->
            @if($canViewFinancials)
            <div class="bg-gradient-to-br from-[#0f766e] to-[#115e59] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-teal-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Purchases</span>
                    <span class="material-icons-outlined text-teal-200 text-sm sm:text-base">shopping_cart</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1" title="${{ number_format($kpis['total_purchases'], 2) }}">
                    ${{ number_format($kpis['total_purchases'] / 1000, 1) }}K
                </div>
                <div class="text-[10px] sm:text-[11px] text-teal-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs">local_shipping</span>
                    <span>Procured goods</span>
                </div>
            </div>
            @else
            <div class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] border border-dashed border-slate-300 dark:border-slate-700">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider">
                    <span class="truncate">Purchases</span>
                    <span class="material-icons-outlined text-xs sm:text-sm">lock</span>
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Finance role needed</div>
            </div>
            @endif

            <!-- KPI 5: Gross Profit -->
            @if($canViewProfit)
            <div class="bg-gradient-to-br from-[#9d174d] to-[#831843] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-pink-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Gross Profit</span>
                    <span class="material-icons-outlined text-pink-200 text-sm sm:text-base">account_balance_wallet</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1" title="${{ number_format($kpis['gross_profit'], 2) }}">
                    ${{ number_format($kpis['gross_profit'] / 1000, 1) }}K
                </div>
                <div class="text-[10px] sm:text-[11px] text-pink-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs text-emerald-300">payments</span>
                    <span>Trading spread</span>
                </div>
            </div>
            @else
            <div class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] border border-dashed border-slate-300 dark:border-slate-700">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider">
                    <span class="truncate">Gross Profit</span>
                    <span class="material-icons-outlined text-xs sm:text-sm">lock</span>
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Admin role needed</div>
            </div>
            @endif

            <!-- KPI 6: Profit Margin % -->
            @if($canViewProfit)
            <div class="bg-gradient-to-br from-[#6b21a8] to-[#581c87] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-purple-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Profit Margin</span>
                    <span class="material-icons-outlined text-purple-200 text-sm sm:text-base">percent</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight my-1 truncate">
                    {{ number_format($kpis['profit_margin_pct'], 1) }}%
                </div>
                <div class="text-[10px] sm:text-[11px] text-purple-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs text-emerald-300">pie_chart</span>
                    <span>Net trading ROI</span>
                </div>
            </div>
            @else
            <div class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] border border-dashed border-slate-300 dark:border-slate-700">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider">
                    <span class="truncate">Profit Margin</span>
                    <span class="material-icons-outlined text-xs sm:text-sm">lock</span>
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Admin role needed</div>
            </div>
            @endif

        </div>

        <!-- ROW 2 -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-3">

            <!-- KPI 7: Total Tonnage -->
            <div class="bg-gradient-to-br from-[#4338ca] to-[#312e81] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-indigo-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Total Volume</span>
                    <span class="material-icons-outlined text-indigo-200 text-sm sm:text-base">scale</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1">
                    {{ number_format($kpis['total_tonnage']) }} <span class="text-xs font-normal text-indigo-200">MT</span>
                </div>
                <div class="text-[10px] sm:text-[11px] text-indigo-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs">inventory_2</span>
                    <span>Traded weight</span>
                </div>
            </div>

            <!-- KPI 8: In-Transit Count -->
            <div class="bg-gradient-to-br from-[#0284c7] to-[#075985] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-sky-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">In-Transit</span>
                    <span class="material-icons-outlined text-sky-200 text-sm sm:text-base">directions_boat</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1">
                    {{ number_format($kpis['in_transit_count']) }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-sky-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs text-amber-300">hourglass_top</span>
                    <span>Currently shipping</span>
                </div>
            </div>

            <!-- KPI 9: Fulfillment Rate % -->
            <div class="bg-gradient-to-br from-[#047857] to-[#065f46] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-emerald-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Fulfillment</span>
                    <span class="material-icons-outlined text-emerald-200 text-sm sm:text-base">task_alt</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1">
                    {{ number_format($kpis['fulfillment_rate_pct'], 1) }}%
                </div>
                <div class="text-[10px] sm:text-[11px] text-emerald-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs">done_all</span>
                    <span>Closed contracts</span>
                </div>
            </div>

            <!-- KPI 10: Avg Contract Value -->
            <div class="bg-gradient-to-br from-[#15803d] to-[#166534] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-green-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Avg Contract</span>
                    <span class="material-icons-outlined text-green-200 text-sm sm:text-base">equalizer</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1" title="${{ number_format($kpis['avg_contract_value'], 2) }}">
                    ${{ number_format($kpis['avg_contract_value'] / 1000, 1) }}K
                </div>
                <div class="text-[10px] sm:text-[11px] text-green-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs">receipt_long</span>
                    <span>Per transaction</span>
                </div>
            </div>

            <!-- KPI 11: Trading Yield / ROAS -->
            @if($canViewProfit)
            <div class="bg-gradient-to-br from-[#be185d] to-[#9d174d] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-pink-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Trading Yield</span>
                    <span class="material-icons-outlined text-pink-200 text-sm sm:text-base">auto_graph</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1">
                    {{ number_format($kpis['roas_ratio'], 1) }}%
                </div>
                <div class="text-[10px] sm:text-[11px] text-pink-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs text-emerald-300">speed</span>
                    <span>Sales / Purchases</span>
                </div>
            </div>
            @else
            <div class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] border border-dashed border-slate-300 dark:border-slate-700">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider">
                    <span class="truncate">Trading Yield</span>
                    <span class="material-icons-outlined text-xs sm:text-sm">lock</span>
                </div>
                <div class="text-base sm:text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Admin role needed</div>
            </div>
            @endif

            <!-- KPI 12: Active Entities -->
            <div class="bg-gradient-to-br from-[#7e22ce] to-[#6b21a8] text-white p-3 sm:p-4 rounded-xl shadow-xs flex flex-col justify-between min-h-[96px] sm:min-h-[108px] overflow-hidden">
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-purple-100 font-semibold uppercase tracking-wider">
                    <span class="truncate">Entities</span>
                    <span class="material-icons-outlined text-purple-200 text-sm sm:text-base">apartment</span>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight truncate my-1">
                    {{ count($availableCompanies) }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-purple-200 flex items-center gap-1 truncate">
                    <span class="material-icons-outlined text-xs">business_center</span>
                    <span>Operating units</span>
                </div>
            </div>

        </div>
    </div>

    <!-- MAIN GRID WITH CANVAS & DOCKED SLICER PANEL -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 sm:gap-6 items-start">

        <!-- DASHBOARD CANVAS (Flexible 12 or 9 columns) -->
        <div :class="showFilters ? 'xl:col-span-9' : 'xl:col-span-12'" class="space-y-4 sm:space-y-6 min-w-0 transition-all">

            <!-- TAB 1: OVERVIEW & POWER BI CANVAS TILES -->
            <div x-show="activeTab === 'overview'" class="space-y-4 sm:space-y-6">

                <!-- ROW 1: Donut (4) + Stacked Bar (5) + Radial Speedometer (3) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">

                    <!-- DONUT: Company Distribution -->
                    <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 truncate">
                                    <span class="material-icons-outlined text-indigo-600 text-lg">donut_large</span>
                                    <span>Trading by Company</span>
                                </h3>
                                <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                                    Share
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 truncate">Sales distribution across active business entities</p>
                        </div>
                        <div class="my-2 flex items-center justify-center min-w-0 overflow-hidden">
                            <div id="v2CompanyDonutChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between">
                            <span>Dynamic breakdown</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ count($donut['labels']) }} entities reporting</span>
                        </div>
                    </div>

                    <!-- STACKED BAR: Purchases vs Sales Timeline -->
                    <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 truncate">
                                    <span class="material-icons-outlined text-blue-600 text-lg">bar_chart</span>
                                    <span>Purchases vs Sales Timeline</span>
                                </h3>
                                <div class="flex items-center gap-2.5 text-[11px] font-medium">
                                    <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#0284c7]"></span> Sales
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#0f766e]"></span> Purchases
                                    </span>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 truncate">Monthly trading execution balance</p>
                        </div>
                        <div class="my-2 min-w-0 overflow-hidden">
                            <div id="v2StackedTimelineChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between">
                            <span>Monthly volume</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">Active Window</span>
                        </div>
                    </div>

                    <!-- RADIAL GAUGE: Target Fulfillment -->
                    <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 truncate">
                                    <span class="material-icons-outlined text-teal-600 text-lg">speed</span>
                                    <span>Target Goal</span>
                                </h3>
                                <span class="text-[11px] sm:text-xs font-bold text-teal-600 bg-teal-50 dark:bg-teal-950/60 px-2 py-0.5 rounded-full shrink-0">
                                    {{ $gauge['percentage'] }}%
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 truncate">Actual sales vs benchmark</p>
                        </div>
                        <div class="my-1 flex items-center justify-center min-w-0 overflow-hidden">
                            <div id="v2RadialGaugeChart" class="w-full h-52"></div>
                        </div>
                        <div class="border-t border-slate-100 dark:border-slate-800 pt-2 space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Sales:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 truncate">${{ number_format($gauge['current'], 0) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Benchmark:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 truncate">${{ number_format($gauge['target'], 0) }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ROW 2: Power BI Horizontal Bar (Top Partners) + Contract Scatter/Risk Matrix -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">

                    <!-- HORIZONTAL GROUPED BAR: Top Trading Partners -->
                    <div class="lg:col-span-6 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 truncate">
                                    <span class="material-icons-outlined text-indigo-600 text-lg">leaderboard</span>
                                    <span>Top Trading Partners</span>
                                </h3>
                                <span class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 shrink-0">
                                    Volume
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 truncate">Top clients driving sales and procurement demand</p>
                        </div>
                        <div class="my-2 min-w-0 overflow-hidden">
                            <div id="v2HorizontalPartnersChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between">
                            <span>Counterparty exposure</span>
                            <span class="font-medium text-indigo-600 dark:text-indigo-400">Top 5 Analyzed</span>
                        </div>
                    </div>

                    <!-- BUBBLE / SCATTER PLOT: Contract Execution & Risk -->
                    <div class="lg:col-span-6 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 truncate">
                                    <span class="material-icons-outlined text-purple-600 text-lg">bubble_chart</span>
                                    <span>Contract Risk Matrix</span>
                                </h3>
                                <span class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 dark:bg-purple-950/60 font-semibold shrink-0">
                                    Scatter
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 truncate">X: Days Remaining | Y: Contract Value | Size: Margin %</p>
                        </div>
                        <div class="my-2 min-w-0 overflow-hidden">
                            <div id="v2ContractRiskBubbleChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between">
                            <span>Delivery urgency & exposure</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">Hover for details</span>
                        </div>
                    </div>

                </div>

                <!-- ROW 3: HEATMAP MATRICES (In-cell Heatmap Tables with horizontal scroll protection) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">

                    <!-- COMPANY PERFORMANCE HEATMAP MATRIX -->
                    <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs min-w-0">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-icons-outlined text-blue-600 text-lg">grid_view</span>
                                    <span>Company Performance Matrix</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Financial breakdown & trading density per business company</p>
                            </div>
                            <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/60 shrink-0">
                                Heatmap
                            </span>
                        </div>

                        <!-- Responsive Table wrapper with scroll hint -->
                        <div class="overflow-x-auto custom-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
                            <table class="w-full text-left text-xs min-w-[540px] sm:min-w-full">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                                        <th class="py-2.5 px-2.5">Company</th>
                                        <th class="py-2.5 px-2.5 text-center">Orders</th>
                                        <th class="py-2.5 px-2.5 text-right">Sales ($)</th>
                                        @if($canViewFinancials)
                                        <th class="py-2.5 px-2.5 text-right">Purchases ($)</th>
                                        <th class="py-2.5 px-2.5 text-right">Profit ($)</th>
                                        <th class="py-2.5 px-2.5 text-right">Margin %</th>
                                        @endif
                                        <th class="py-2.5 px-2.5 w-28 sm:w-32 text-center">Share</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                                    @forelse($companyMatrix as $cm)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3 px-2.5 text-slate-900 dark:text-white font-semibold whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                                <span class="truncate max-w-[140px] sm:max-w-none">{{ $cm['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-2.5 text-center text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                            {{ $cm['contracts'] }}
                                        </td>
                                        <td class="py-3 px-2.5 text-right text-slate-900 dark:text-white font-bold whitespace-nowrap">
                                            ${{ number_format($cm['sales'], 2) }}
                                        </td>
                                        @if($canViewFinancials)
                                        <td class="py-3 px-2.5 text-right text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                            ${{ number_format($cm['purchases'], 2) }}
                                        </td>
                                        <td class="py-3 px-2.5 text-right {{ $cm['profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }} font-bold whitespace-nowrap">
                                            ${{ number_format($cm['profit'], 2) }}
                                        </td>
                                        <td class="py-3 px-2.5 text-right text-purple-600 dark:text-purple-400 font-semibold whitespace-nowrap">
                                            {{ $cm['margin_pct'] }}%
                                        </td>
                                        @endif
                                        <td class="py-3 px-2.5 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <div class="flex-1 bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all" style="width: {{ $cm['heat_pct'] }}%"></div>
                                                </div>
                                                <span class="text-[10px] text-slate-500 font-bold w-6 text-right">{{ $cm['heat_pct'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-slate-400">No company records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- WORKFLOW STAGE PIPELINE MATRIX -->
                    <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs min-w-0">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-icons-outlined text-teal-600 text-lg">alt_route</span>
                                    <span>Pipeline Stage Funnel</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Real-time status across execution stages</p>
                            </div>
                            <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 dark:bg-teal-950/60 shrink-0">
                                Stages
                            </span>
                        </div>

                        <div class="overflow-x-auto custom-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
                            <table class="w-full text-left text-xs min-w-[340px] sm:min-w-full">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                                        <th class="py-2.5 px-2.5">Stage</th>
                                        <th class="py-2.5 px-2.5 text-center">Contracts</th>
                                        <th class="py-2.5 px-2.5 text-right">Value ($)</th>
                                        <th class="py-2.5 px-2.5 text-center">Share</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                                    @forelse($stageMatrix as $sm)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3 px-2.5 text-slate-900 dark:text-white font-semibold whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-teal-500 shrink-0"></span>
                                                <span class="truncate max-w-[130px] sm:max-w-none">{{ $sm['title'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-2.5 text-center font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                            {{ $sm['count'] }}
                                        </td>
                                        <td class="py-3 px-2.5 text-right text-slate-900 dark:text-white font-bold whitespace-nowrap">
                                            ${{ number_format($sm['value'], 0) }}
                                        </td>
                                        <td class="py-3 px-2.5 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {{ $sm['pct'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-400">No active stages recorded.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

            <!-- TAB 2: DYNAMICS & LOGISTICS STAGES -->
            <div x-show="activeTab === 'dynamics'" class="space-y-4 sm:space-y-6" x-cloak>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-xs min-w-0">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="material-icons-outlined text-indigo-600">sync_alt</span>
                                <span>Unit Rate & Pricing Dynamics over Time</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Average sale rate vs procurement rate per MT across calendar intervals</p>
                        </div>
                    </div>
                    <div id="v2PricingDynamicsChart" class="w-full h-72 sm:h-80"></div>
                </div>

                <!-- Operations & Logistics Metrics Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 flex items-center justify-center shrink-0">
                                <span class="material-icons-outlined">directions_boat</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Active In-Transit</div>
                                <div class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white truncate">{{ $kpis['in_transit_count'] }} Shipments</div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">En-route between loading port and destination port.</p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center shrink-0">
                                <span class="material-icons-outlined">verified</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Completed Fulfillment</div>
                                <div class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white truncate">{{ $kpis['closed_count'] }} Contracts</div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Fully settled and invoiced commercial transactions.</p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs sm:col-span-2 md:col-span-1">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950 text-purple-600 flex items-center justify-center shrink-0">
                                <span class="material-icons-outlined">inventory</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Goods Traded</div>
                                <div class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white truncate">{{ number_format($kpis['total_tonnage']) }} MT</div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Total physical metric tons scheduled in the current period.</p>
                    </div>
                </div>
            </div>

            <!-- TAB 3: FINANCIAL BI (Admin / Finance Role Only) -->
            @if($canViewFinancials)
            <div x-show="activeTab === 'financials'" class="space-y-4 sm:space-y-6" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
                    
                    <!-- Power BI Cash Flow & Profit Waterfall Chart -->
                    <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-xs min-w-0">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-icons-outlined text-pink-600">waterfall_chart</span>
                                    <span>Trading Financial Waterfall</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-1">Capital flow through procurement to net realization</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold bg-pink-50 text-pink-700 dark:bg-pink-950/60 shrink-0">
                                Confidential
                            </span>
                        </div>
                        <div id="v2FinancialWaterfallChart" class="w-full h-72 sm:h-80"></div>
                    </div>

                    <!-- Financial Summary Cards -->
                    <div class="lg:col-span-4 space-y-3 sm:space-y-4">
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                            <span class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Net Trading Margin</span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1 truncate">
                                ${{ number_format($kpis['gross_profit'], 2) }}
                            </div>
                            <div class="text-xs text-emerald-600 font-medium mt-1.5 flex items-center gap-1">
                                <span class="material-icons-outlined text-sm">trending_up</span>
                                <span>{{ $kpis['profit_margin_pct'] }}% net margin</span>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                            <span class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Procurement Cost</span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1 truncate">
                                ${{ number_format($kpis['total_purchases'], 2) }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1.5">
                                Total capital deployed in purchase contracts
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
                            <span class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Trading Multiplier (Yield)</span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 truncate">
                                {{ $kpis['roas_ratio'] }}%
                            </div>
                            <div class="text-xs text-slate-500 mt-1.5">
                                Ratio of gross sales returned per procurement dollar
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @endif

        </div>

        <!-- RIGHT-HAND POWER BI SLICER PANEL (Desktop View) -->
        <div x-show="showFilters" class="hidden xl:block xl:col-span-3 transition-all min-w-0">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs sticky top-6 space-y-4">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-indigo-600 text-lg">tune</span>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Data Slicers & Filters
                        </h3>
                    </div>
                    <button type="button" @click="resetFilters()" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-semibold">
                        Reset All
                    </button>
                </div>

                <!-- Slicer 1: Date Range -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>Report Date Range</span>
                        <span class="material-icons-outlined text-xs text-slate-400">calendar_today</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5">Start Date</span>
                            <input type="date" x-model="startDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2.5 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5">End Date</span>
                            <input type="date" x-model="endDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2.5 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Slicer 2: Company Slicer -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>Company / Entity</span>
                        <span class="material-icons-outlined text-xs text-slate-400">apartment</span>
                    </label>
                    <select x-model="filterCompany" 
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">All Accessible Companies</option>
                        @foreach($availableCompanies as $company)
                        <option value="{{ $company->id }}" {{ (string)$selectedCompanyId === (string)$company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Slicer 3: Workflow Stage Slicer -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>Pipeline Stage</span>
                        <span class="material-icons-outlined text-xs text-slate-400">alt_route</span>
                    </label>
                    <select x-model="filterStage" 
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">All Execution Stages</option>
                        @foreach($allStages as $stage)
                        <option value="{{ $stage->id }}" {{ (string)$selectedStageId === (string)$stage->id ? 'selected' : '' }}>
                            {{ $stage->description ?: ($stage->type ?: 'Stage #' . $stage->id) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Slicer 4: Currency Slicer -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                        <span>Currency</span>
                        <span class="material-icons-outlined text-xs text-slate-400">monetization_on</span>
                    </label>
                    <select x-model="filterCurrency" 
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="all">All Currencies</option>
                        <option value="USD" {{ $selectedCurrency === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ $selectedCurrency === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ $selectedCurrency === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        <option value="CHF" {{ $selectedCurrency === 'CHF' ? 'selected' : '' }}>CHF (Fr)</option>
                    </select>
                </div>

                <!-- Apply Filters Button -->
                <button type="button" @click="applyFilters()" :disabled="isApplying" 
                        class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold text-xs transition-all shadow-md shadow-indigo-500/20 flex items-center justify-center gap-2">
                    <span class="material-icons-outlined text-base">filter_list</span>
                    <span x-text="isApplying ? 'Applying Filters...' : 'Apply Slicers'"></span>
                </button>

                <!-- Role Access Status Card -->
                <div class="bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/60 space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                        <span class="material-icons-outlined text-indigo-600 text-sm">security</span>
                        <span>Active Role Permissions</span>
                    </div>
                    <ul class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                        <li class="flex items-center justify-between">
                            <span>SuperAdmin:</span>
                            <span class="font-bold {{ $isSuperAdmin ? 'text-emerald-600' : 'text-slate-400' }}">{{ $isSuperAdmin ? 'YES' : 'NO' }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Financial View:</span>
                            <span class="font-bold {{ $canViewFinancials ? 'text-emerald-600' : 'text-amber-500' }}">{{ $canViewFinancials ? 'ALLOWED' : 'MASKED' }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Profit Margins:</span>
                            <span class="font-bold {{ $canViewProfit ? 'text-emerald-600' : 'text-amber-500' }}">{{ $canViewProfit ? 'VISIBLE' : 'HIDDEN' }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Entity Scoping:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ count($availableCompanies) }} assigned</span>
                        </li>
                    </ul>
                </div>

                <!-- Support & Switch to Classic -->
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-slate-800 dark:to-indigo-950/40 p-3.5 rounded-xl border border-indigo-100 dark:border-indigo-900/50 space-y-1.5">
                    <div class="text-xs font-bold text-indigo-900 dark:text-indigo-200">
                        Need Custom BI Queries?
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                        Switch between Overview, Dynamics and Financials tabs to review deeper pipeline breakdowns.
                    </p>
                    <a href="{{ route('dashboards.analytics') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                        <span>Classic Dashboard</span>
                        <span class="material-icons-outlined text-xs">arrow_forward</span>
                    </a>
                </div>

            </div>
        </div>

    </div>

    <!-- MOBILE / TABLET OFF-CANVAS SLICER DRAWER -->
    <div x-show="mobileFiltersOpen" 
         class="fixed inset-0 z-50 xl:hidden overflow-hidden" 
         x-cloak>
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             @click="mobileFiltersOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white dark:bg-slate-900 shadow-2xl p-5 space-y-4 overflow-y-auto custom-scrollbar border-l border-slate-200 dark:border-slate-800">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-indigo-600 text-lg">tune</span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Filters & Data Slicers
                        </h3>
                    </div>
                    <button type="button" @click="mobileFiltersOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <span class="material-icons-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Date Range -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Report Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5">Start</span>
                            <input type="date" x-model="startDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2.5 py-2 text-slate-800 dark:text-slate-200">
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5">End</span>
                            <input type="date" x-model="endDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2.5 py-2 text-slate-800 dark:text-slate-200">
                        </div>
                    </div>
                </div>

                <!-- Company -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Company / Entity</label>
                    <select x-model="filterCompany" 
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="all">All Accessible Companies</option>
                        @foreach($availableCompanies as $company)
                        <option value="{{ $company->id }}" {{ (string)$selectedCompanyId === (string)$company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stage -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Pipeline Stage</label>
                    <select x-model="filterStage" 
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="all">All Execution Stages</option>
                        @foreach($allStages as $stage)
                        <option value="{{ $stage->id }}" {{ (string)$selectedStageId === (string)$stage->id ? 'selected' : '' }}>
                            {{ $stage->description ?: ($stage->type ?: 'Stage #' . $stage->id) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Currency -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Currency</label>
                    <select x-model="filterCurrency" 
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="all">All Currencies</option>
                        <option value="USD" {{ $selectedCurrency === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ $selectedCurrency === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ $selectedCurrency === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        <option value="CHF" {{ $selectedCurrency === 'CHF' ? 'selected' : '' }}>CHF (Fr)</option>
                    </select>
                </div>

                <!-- Drawer Action Buttons -->
                <div class="pt-3 space-y-2">
                    <button type="button" @click="applyFilters()" :disabled="isApplying" 
                            class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold text-xs transition-all shadow-md flex items-center justify-center gap-2">
                        <span class="material-icons-outlined text-base">filter_list</span>
                        <span x-text="isApplying ? 'Applying Filters...' : 'Apply Filters'"></span>
                    </button>
                    <button type="button" @click="resetFilters()" 
                            class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-50">
                        Reset Filters
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- APEXCHARTS LIBRARY & VISUAL INITIALIZATION SCRIPT (With Responsive Breakpoints) -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // Shared chart font and styling options
    const sharedFontFamily = 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

    // 1. DONUT CHART (Trading by Company)
    const donutLabels = @json($donut['labels']);
    const donutSeries = @json($donut['series']);
    const donutColors = ['#4f46e5', '#0284c7', '#0f766e', '#9d174d', '#7e22ce', '#eab308', '#f97316', '#64748b'];

    const donutEl = document.querySelector('#v2CompanyDonutChart');
    if (donutEl) {
        const donutOptions = {
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: sharedFontFamily,
                animations: { enabled: true, easing: 'easeinout' }
            },
            series: donutSeries,
            labels: donutLabels,
            colors: donutColors.slice(0, Math.max(donutLabels.length, 1)),
            stroke: { width: 2, colors: ['#ffffff'] },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + "%";
                },
                style: { fontSize: '11px', fontWeight: 600 }
            },
            legend: {
                position: 'bottom',
                fontSize: '11px',
                markers: { radius: 12 },
                itemMargin: { horizontal: 5, vertical: 2 }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '$' + Number(val).toLocaleString();
                    }
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { fontSize: '11px', fontWeight: 600 },
                            value: {
                                fontSize: '15px',
                                fontWeight: 800,
                                formatter: function (val) {
                                    return '$' + Number(val).toLocaleString();
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '10px',
                                color: '#64748b',
                                formatter: function (w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return '$' + Number(total).toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 480,
                    options: {
                        chart: { height: 220 },
                        legend: { fontSize: '10px' }
                    }
                }
            ]
        };
        const donutChart = new ApexCharts(donutEl, donutOptions);
        donutChart.render();
    }

    // 2. STACKED TIMELINE BAR CHART (Purchases vs Sales)
    const stackedCategories = @json($stacked['categories']);
    const stackedSales = @json($stacked['sales']);
    const stackedPurchases = @json($stacked['purchases']);

    const stackedEl = document.querySelector('#v2StackedTimelineChart');
    if (stackedEl) {
        const stackedOptions = {
            chart: {
                type: 'bar',
                height: 250,
                stacked: true,
                fontFamily: sharedFontFamily,
                toolbar: { show: false }
            },
            series: [
                { name: 'Sales ($)', data: stackedSales },
                { name: 'Purchases ($)', data: stackedPurchases }
            ],
            colors: ['#0284c7', '#0f766e'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 4,
                    columnWidth: '50%'
                }
            },
            xaxis: {
                categories: stackedCategories,
                labels: { style: { fontSize: '10px' } },
                axisBorder: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return '$' + (val >= 1000 ? (val / 1000).toFixed(0) + 'K' : val);
                    },
                    style: { fontSize: '10px' }
                }
            },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '$' + Number(val).toLocaleString();
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 230 },
                        plotOptions: { bar: { columnWidth: '65%' } },
                        xaxis: { labels: { rotate: -35, style: { fontSize: '9px' } } }
                    }
                }
            ]
        };
        const stackedChart = new ApexCharts(stackedEl, stackedOptions);
        stackedChart.render();
    }

    // 3. POWER BI RADIAL SPEEDOMETER GAUGE (Target Fulfillment)
    const gaugeEl = document.querySelector('#v2RadialGaugeChart');
    if (gaugeEl) {
        const gaugePct = {{ $gauge['percentage'] }};
        const gaugeOptions = {
            chart: {
                type: 'radialBar',
                height: 210,
                offsetY: -10,
                fontFamily: sharedFontFamily
            },
            series: [gaugePct],
            colors: ['#0f766e'],
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: { size: '65%' },
                    track: {
                        background: '#f1f5f9',
                        strokeWidth: '100%'
                    },
                    dataLabels: {
                        name: {
                            show: true,
                            fontSize: '11px',
                            fontWeight: 600,
                            color: '#64748b',
                            offsetY: -8
                        },
                        value: {
                            show: true,
                            fontSize: '20px',
                            fontWeight: 800,
                            color: '#0f766e',
                            offsetY: 4,
                            formatter: function (val) {
                                return val + "%";
                            }
                        }
                    }
                }
            },
            stroke: { lineCap: 'round' },
            labels: ['Goal Reached'],
            responsive: [
                {
                    breakpoint: 480,
                    options: {
                        chart: { height: 190 }
                    }
                }
            ]
        };
        const gaugeChart = new ApexCharts(gaugeEl, gaugeOptions);
        gaugeChart.render();
    }

    // 4. POWER BI HORIZONTAL GROUPED BAR (Top Trading Partners)
    const partnersEl = document.querySelector('#v2HorizontalPartnersChart');
    if (partnersEl) {
        const topBuyers = @json($horizontalPartners['buyers']);
        const buyerNames = Object.keys(topBuyers);
        const buyerValues = Object.values(topBuyers).map(v => Math.round(v));

        const partnerOptions = {
            chart: {
                type: 'bar',
                height: 250,
                fontFamily: sharedFontFamily,
                toolbar: { show: false }
            },
            series: [{ name: 'Volume ($)', data: buyerValues.length > 0 ? buyerValues : [0] }],
            colors: ['#4f46e5'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '55%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: buyerNames.length > 0 ? buyerNames : ['No buyers recorded'],
                labels: {
                    formatter: function (val) {
                        return '$' + (val >= 1000 ? (val / 1000).toFixed(0) + 'K' : val);
                    },
                    style: { fontSize: '10px' }
                }
            },
            yaxis: { labels: { style: { fontSize: '10px', fontWeight: 600 } } },
            grid: { borderColor: '#f1f5f9' },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '$' + Number(val).toLocaleString();
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 230 },
                        plotOptions: { bar: { barHeight: '65%' } }
                    }
                }
            ]
        };
        const partnerChart = new ApexCharts(partnersEl, partnerOptions);
        partnerChart.render();
    }

    // 5. POWER BI CONTRACT RISK 3D BUBBLE / SCATTER PLOT
    const bubbleEl = document.querySelector('#v2ContractRiskBubbleChart');
    if (bubbleEl) {
        const contractPoints = @json($contractPoints);
        const bubbleData = contractPoints.map(p => ({
            x: p.x,
            y: p.y,
            z: p.z
        }));

        const bubbleOptions = {
            chart: {
                type: 'bubble',
                height: 250,
                fontFamily: sharedFontFamily,
                toolbar: { show: false }
            },
            series: [
                { name: 'Active Orders', data: bubbleData }
            ],
            colors: ['#7e22ce'],
            dataLabels: { enabled: false },
            fill: { opacity: 0.8 },
            xaxis: {
                title: { text: 'Days Remaining', style: { fontSize: '10px' } },
                labels: { style: { fontSize: '10px' } }
            },
            yaxis: {
                title: { text: 'Amount ($)', style: { fontSize: '10px' } },
                labels: {
                    formatter: function (val) {
                        return '$' + (val >= 1000 ? (val / 1000).toFixed(0) + 'K' : val);
                    },
                    style: { fontSize: '10px' }
                }
            },
            grid: { borderColor: '#f1f5f9' },
            tooltip: {
                custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                    const pt = contractPoints[dataPointIndex];
                    if (!pt) return '';
                    return '<div class="p-2 text-xs bg-slate-900 text-white rounded shadow-lg">' +
                           '<div class="font-bold">' + pt.name + '</div>' +
                           '<div>Value: $' + Number(pt.y).toLocaleString() + '</div>' +
                           '<div>Days Left: ' + pt.x + ' days</div>' +
                           '<div>Margin Rate: ' + pt.z + '%</div>' +
                           '</div>';
                }
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 230 }
                    }
                }
            ]
        };
        const bubbleChart = new ApexCharts(bubbleEl, bubbleOptions);
        bubbleChart.render();
    }

    // 6. DYNAMICS TAB: UNIT PRICING LINE
    const dynamicsEl = document.querySelector('#v2PricingDynamicsChart');
    if (dynamicsEl) {
        const unitCategories = @json($unitRates['categories']);
        const unitCosts = @json($unitRates['cost']);
        const unitPrices = @json($unitRates['price']);

        const dynamicsOptions = {
            chart: {
                type: 'line',
                height: 300,
                fontFamily: sharedFontFamily,
                toolbar: { show: false }
            },
            series: [
                { name: 'Sale Rate ($/MT)', type: 'line', data: unitPrices },
                { name: 'Procurement Rate ($/MT)', type: 'line', data: unitCosts }
            ],
            colors: ['#0284c7', '#0f766e'],
            stroke: { width: [3, 3], curve: 'smooth' },
            markers: { size: 4 },
            xaxis: { 
                categories: unitCategories,
                labels: { style: { fontSize: '10px' } }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return '$' + Number(val).toFixed(0);
                    },
                    style: { fontSize: '10px' }
                }
            },
            grid: { borderColor: '#f1f5f9' },
            legend: { position: 'top', fontSize: '11px' },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 250 },
                        xaxis: { labels: { rotate: -40, style: { fontSize: '9px' } } }
                    }
                }
            ]
        };
        const dynamicsChart = new ApexCharts(dynamicsEl, dynamicsOptions);
        dynamicsChart.render();
    }

    // 7. FINANCIAL WATERFALL CHART
    const waterfallEl = document.querySelector('#v2FinancialWaterfallChart');
    if (waterfallEl) {
        const totalSales = {{ $kpis['total_sales'] }};
        const totalPurchases = {{ $kpis['total_purchases'] }};
        const grossProfit = {{ $kpis['gross_profit'] }};

        const waterfallOptions = {
            chart: {
                type: 'bar',
                height: 300,
                fontFamily: sharedFontFamily,
                toolbar: { show: false }
            },
            series: [{
                name: 'Financial Flow',
                data: [
                    { x: 'Gross Sales', y: totalSales, fillColor: '#0284c7' },
                    { x: 'Procurement Cost', y: -totalPurchases, fillColor: '#ef4444' },
                    { x: 'Net Trading Profit', y: grossProfit, fillColor: '#10b981' }
                ]
            }],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 4,
                    columnWidth: '45%'
                }
            },
            xaxis: {
                labels: { style: { fontSize: '10px', fontWeight: 600 } }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return '$' + (Math.abs(val) >= 1000 ? (val / 1000).toFixed(0) + 'K' : val);
                    },
                    style: { fontSize: '10px' }
                }
            },
            grid: { borderColor: '#f1f5f9' },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '$' + Number(val).toLocaleString();
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 250 },
                        plotOptions: { bar: { columnWidth: '60%' } },
                        xaxis: { labels: { rotate: -20, style: { fontSize: '9px' } } }
                    }
                }
            ]
        };
        const waterfallChart = new ApexCharts(waterfallEl, waterfallOptions);
        waterfallChart.render();
    }

});
</script>
@endsection
