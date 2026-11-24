@extends('layouts.app')

@section('title', 'Executive Power BI Dashboard - MetaERP')

@section('content')
<!-- OUTER WRAPPER WITH EXACT CANVAS BACKGROUND (#eaf1fa matching Image 1) -->
<div x-data="{
    activeTab: 'overview',
    waterfallView: 'flow',
    showFilters: true,
    mobileFiltersOpen: false,
    filterCompany: '{{ $selectedCompanyId ?? 'all' }}',
    filterStage: '{{ $selectedStageId ?? 'all' }}',
    filterCurrency: '{{ $selectedCurrency ?? 'all' }}',
    startDate: '{{ $startDate ?? '' }}',
    endDate: '{{ $endDate ?? '' }}',
    isApplying: false,
    setTab(tab) {
        this.activeTab = tab;
        this.$nextTick(() => {
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
                if (tab === 'financials' && window.renderWaterfallChart) {
                    window.renderWaterfallChart(this.waterfallView || 'flow');
                }
            }, 60);
        });
    },
    switchWaterfall(mode) {
        this.waterfallView = mode;
        if (window.renderWaterfallChart) {
            window.renderWaterfallChart(mode);
        }
    },
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
}" class="-m-6 p-4 sm:p-6 bg-[#eaf1fa] min-h-screen text-slate-800 space-y-4 sm:space-y-5">

    <!-- TOP HEADER BAR (Exact Coupler.io & Power BI Header Style) -->
    <div class="bg-white/90 backdrop-blur-xs border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-[0_2px_10px_rgba(0,0,0,0.03)] transition-all">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Left Branding & Sub-brand -->
            <div class="flex items-center gap-3.5">
                <!-- Cyan Round Logo matching Coupler.io icon -->
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-[#0284c7] flex items-center justify-center text-white shadow-md shadow-sky-500/20 shrink-0">
                    <span class="material-icons-outlined text-2xl font-bold">query_stats</span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-[#0f1e36] truncate">
                            MetaERP Executive BI Dashboard
                        </h1>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[11px] font-extrabold tracking-wider text-[#0284c7] uppercase">METAERP.IO</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-xs text-slate-500 font-medium truncate">Multi-Company Trading & Logistics Performance</span>
                        @if($isSuperAdmin)
                            <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                <span class="material-icons-outlined text-xs">verified_user</span> Admin Scope
                            </span>
                        @else
                            <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">
                                <span class="material-icons-outlined text-xs">lock</span> Restricted
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Nav Capsule & Actions (Matching Coupler.io Overview / Dynamics / Setup) -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                
                <!-- Dark Navy Nav Capsule -->
                <div class="bg-[#1c4b82] p-1 rounded-xl flex items-center text-xs font-semibold text-white shadow-xs overflow-x-auto custom-scrollbar">
                    <button type="button" @click="setTab('overview')" 
                            :class="activeTab === 'overview' ? 'bg-[#2563eb] text-white shadow-xs' : 'text-white/80 hover:text-white'"
                            class="px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <span class="material-icons-outlined text-base">dashboard</span>
                        <span>Overview</span>
                    </button>
                    <button type="button" @click="setTab('dynamics')" 
                            :class="activeTab === 'dynamics' ? 'bg-[#2563eb] text-white shadow-xs' : 'text-white/80 hover:text-white'"
                            class="px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <span class="material-icons-outlined text-base">trending_up</span>
                        <span>Dynamics</span>
                    </button>
                    @if($canViewFinancials)
                    <button type="button" @click="setTab('financials')" 
                            :class="activeTab === 'financials' ? 'bg-[#2563eb] text-white shadow-xs' : 'text-white/80 hover:text-white'"
                            class="px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <span class="material-icons-outlined text-base">account_balance</span>
                        <span>Financials</span>
                    </button>
                    @endif
                </div>

                <!-- Setup / Action Buttons -->
                <div class="flex items-center gap-2">
                    <!-- Desktop Filter Toggle -->
                    <button type="button" @click="showFilters = !showFilters" 
                            class="hidden xl:inline-flex px-3.5 py-2 text-xs font-semibold rounded-xl bg-white text-[#1c4b82] border border-blue-200/70 hover:bg-blue-50 transition-colors items-center gap-1.5 shadow-xs whitespace-nowrap">
                        <span class="material-icons-outlined text-base" :class="showFilters ? 'text-indigo-600' : ''">filter_alt</span>
                        <span x-text="showFilters ? 'Hide Slicers' : 'Show Slicers'"></span>
                    </button>

                    <!-- Mobile Drawer Trigger -->
                    <button type="button" @click="mobileFiltersOpen = true" 
                            class="xl:hidden flex-1 sm:flex-initial inline-flex justify-center px-3.5 py-2 text-xs font-semibold rounded-xl bg-white text-[#1c4b82] border border-blue-200/70 hover:bg-blue-50 transition-colors items-center gap-1.5 shadow-xs whitespace-nowrap">
                        <span class="material-icons-outlined text-base text-[#0284c7]">tune</span>
                        <span>Slicers</span>
                    </button>

                    <!-- Setup Dashboard / Export Button (Crisp White Pill with blue text matching Image 1) -->
                    <button type="button" onclick="window.print()" 
                            class="flex-1 sm:flex-initial inline-flex justify-center px-4 py-2 text-xs font-bold rounded-xl bg-white text-[#1c4b82] hover:bg-blue-50 transition-all shadow-xs border border-blue-200/80 items-center gap-1.5 whitespace-nowrap">
                        <span class="material-icons-outlined text-base text-[#0284c7]">file_download</span>
                        <span>Setup dashboard</span>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- 12 VIBRANT COUPLER KPI CARDS (Exact Hex Colors & Flat Solid Layout from Image 1) -->
    <div class="space-y-3">
        <!-- ROW 1 (6 Cards: Purple, Blue, Teal Cyan, Mint Green, Magenta Rose, Deep Violet) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            
            <!-- CARD 1: Purple (#5a30b5) -> Total Contracts -->
            <div class="bg-[#5a30b5] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Total Contracts
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ number_format($kpis['total_contracts']) }}
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Active execution</span>
                </div>
            </div>

            <!-- CARD 2: Deep Ocean Blue (#00509d) -> Active Deals -->
            <div class="bg-[#00509d] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Active Deals
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ number_format($kpis['active_deals']) }}
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Purchase & sales</span>
                </div>
            </div>

            <!-- CARD 3: Cerulean Cyan (#0082aa) -> Gross Sales Revenue -->
            <div class="bg-[#0082aa] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Gross Sales Revenue
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate" title="${{ number_format($kpis['total_sales'], 2) }}">
                    ${{ number_format($kpis['total_sales'] / 1000, 1) }}K
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Invoiced sales</span>
                </div>
            </div>

            <!-- CARD 4: Mint Jade Green (#0da87a) -> Procurement Cost -->
            @if($canViewFinancials)
            <div class="bg-[#0da87a] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Procurement Cost
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate" title="${{ number_format($kpis['total_purchases'], 2) }}">
                    ${{ number_format($kpis['total_purchases'] / 1000, 1) }}K
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Purchase contracts</span>
                </div>
            </div>
            @else
            <div class="bg-white/80 text-slate-500 p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] border border-dashed border-slate-300">
                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Procurement Cost</div>
                <div class="text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Finance role needed</div>
            </div>
            @endif

            <!-- CARD 5: Berry Rose Magenta (#bf2c78) -> Net Trading Margin -->
            @if($canViewProfit)
            <div class="bg-[#bf2c78] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Trading Profit
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate" title="${{ number_format($kpis['gross_profit'], 2) }}">
                    ${{ number_format($kpis['gross_profit'] / 1000, 1) }}K
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Net trading margin</span>
                </div>
            </div>
            @else
            <div class="bg-white/80 text-slate-500 p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] border border-dashed border-slate-300">
                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Trading Profit</div>
                <div class="text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Admin role needed</div>
            </div>
            @endif

            <!-- CARD 6: Violet Purple (#732eb0) -> Gross Margin Rate -->
            @if($canViewProfit)
            <div class="bg-[#732eb0] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Gross Margin Rate
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ number_format($kpis['profit_margin_pct'], 1) }}%
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Realized margin %</span>
                </div>
            </div>
            @else
            <div class="bg-white/80 text-slate-500 p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] border border-dashed border-slate-300">
                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Gross Margin Rate</div>
                <div class="text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Admin role needed</div>
            </div>
            @endif

        </div>

        <!-- ROW 2 (6 Cards: Symmetrical color pairs from Image 1) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">

            <!-- CARD 7: Purple (#5a30b5) -> Physical Tonnage Traded -->
            <div class="bg-[#5a30b5] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Physical Tonnage
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ number_format($kpis['total_tonnage']) }} <span class="text-sm font-normal text-white/80">MT</span>
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Traded volume</span>
                </div>
            </div>

            <!-- CARD 8: Deep Ocean Blue (#00509d) -> In-Transit Shipments -->
            <div class="bg-[#00509d] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    In-Transit Shipments
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ number_format($kpis['in_transit_count']) }}
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Active logistics</span>
                </div>
            </div>

            <!-- CARD 9: Cerulean Cyan (#0082aa) -> Order Fulfillment Rate -->
            <div class="bg-[#0082aa] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Order Fulfillment
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ number_format($kpis['fulfillment_rate_pct'], 1) }}%
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Completed orders</span>
                </div>
            </div>

            <!-- CARD 10: Mint Jade Green (#0da87a) -> Average Order Value -->
            <div class="bg-[#0da87a] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Average Order Value
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate" title="${{ number_format($kpis['avg_contract_value'], 2) }}">
                    ${{ number_format($kpis['avg_contract_value'] / 1000, 1) }}K
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Revenue per contract</span>
                </div>
            </div>

            <!-- CARD 11: Berry Rose Magenta (#bf2c78) -> Capital Return (ROAS) -->
            @if($canViewProfit)
            <div class="bg-[#bf2c78] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Capital Return (ROAS)
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ number_format($kpis['roas_ratio'], 1) }}%
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Sales / Procurement</span>
                </div>
            </div>
            @else
            <div class="bg-white/80 text-slate-500 p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] border border-dashed border-slate-300">
                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Capital Return (ROAS)</div>
                <div class="text-lg font-bold text-slate-400">Restricted</div>
                <div class="text-[10px] text-slate-400">Admin role needed</div>
            </div>
            @endif

            <!-- CARD 12: Violet Purple (#732eb0) -> Operating Trading Entities -->
            <div class="bg-[#732eb0] text-white p-4 rounded-2xl shadow-xs flex flex-col justify-between min-h-[105px] overflow-hidden">
                <div class="text-xs text-white/80 font-medium">
                    Trading Entities
                </div>
                <div class="text-3xl font-extrabold tracking-tight text-white my-0.5 truncate">
                    {{ count($availableCompanies) }}
                </div>
                <div class="text-[11px] text-white/70 flex items-center gap-1 truncate">
                    <span>Reporting companies</span>
                </div>
            </div>

        </div>
    </div>

    <!-- MAIN GRID WITH CANVAS & DOCKED SLICER PANEL -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 sm:gap-5 items-start">

        <!-- DASHBOARD CANVAS TILES (Pure White Cards with subtle borders) -->
        <div :class="showFilters ? 'xl:col-span-9' : 'xl:col-span-12'" class="space-y-4 sm:space-y-5 min-w-0 transition-all">

            <!-- TAB 1: OVERVIEW TILES -->
            <div x-show="activeTab === 'overview'" class="space-y-4 sm:space-y-5">

                <!-- ROW 1: Donut (4) + Stacked Bar (5) + Clustered/Radial (3) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5">

                    <!-- DONUT: Sales Revenue by Company (Matching Image 1 Donut style) -->
                    <div class="lg:col-span-4 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-[#0f1e36] tracking-tight">
                                    Sales Revenue by Company
                                </h3>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                    Market Share
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">Revenue distribution across operating trading entities</p>
                        </div>
                        <div class="my-2 flex items-center justify-center min-w-0 overflow-hidden">
                            <div id="v2CompanyDonutChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 pt-2 flex items-center justify-between">
                            <span>Reporting Entities</span>
                            <span class="font-semibold text-slate-700">{{ count($donut['labels']) }} companies reporting</span>
                        </div>
                    </div>

                    <!-- STACKED BAR: Sales vs Procurement Timeline (Matching Image 1 Stacked Columns) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h3 class="text-base font-bold text-[#0f1e36] tracking-tight">
                                    Sales vs Procurement Timeline
                                </h3>
                                <div class="flex items-center gap-3 text-xs font-medium">
                                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#0082aa]"></span> Sales Revenue
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#5a30b5]"></span> Procurement Cost
                                    </span>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">Monthly commercial sales revenue vs procurement cost over time</p>
                        </div>
                        <div class="my-2 min-w-0 overflow-hidden">
                            <div id="v2StackedTimelineChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 pt-2 flex items-center justify-between">
                            <span>Periodic Financial Flow</span>
                            <span class="text-emerald-600 font-semibold">Active Timeline</span>
                        </div>
                    </div>

                    <!-- POWER BI SEMI-CIRCLE RADIAL GAUGE (Annual Sales Target Fulfillment) -->
                    <div class="lg:col-span-3 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-[#0f1e36] tracking-tight">
                                    Sales Target Fulfillment
                                </h3>
                                <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-full">
                                    {{ $gauge['percentage'] }}%
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">Cumulative sales revenue vs target benchmark</p>
                        </div>
                        <div class="my-1 flex items-center justify-center min-w-0 overflow-hidden">
                            <div id="v2RadialGaugeChart" class="w-full h-52"></div>
                        </div>
                        <div class="border-t border-slate-100 pt-2 space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Realized Sales:</span>
                                <span class="font-bold text-slate-800">${{ number_format($gauge['current'], 0) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Annual Target:</span>
                                <span class="font-bold text-slate-800">${{ number_format($gauge['target'], 0) }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ROW 2: Power BI Horizontal Bar (Top Partners) + Contract Scatter/Risk Matrix -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5">

                    <!-- HORIZONTAL GROUPED BAR: Top Commercial Counterparties (Key Buyers) -->
                    <div class="lg:col-span-6 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-[#0f1e36] tracking-tight">
                                    Top Commercial Counterparties (Key Buyers)
                                </h3>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                    By Sales Volume
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">Highest-volume purchasing clients & corporate trading counterparties</p>
                        </div>
                        <div class="my-2 min-w-0 overflow-hidden">
                            <div id="v2HorizontalPartnersChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 pt-2 flex items-center justify-between">
                            <span>Counterparty Exposure</span>
                            <span class="font-semibold text-indigo-600">Top 5 Buyers</span>
                        </div>
                    </div>

                    <!-- BUBBLE / SCATTER PLOT: Contract Maturity & Value Exposure Matrix -->
                    <div class="lg:col-span-6 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-[#0f1e36] tracking-tight">
                                    Contract Maturity & Value Exposure Matrix
                                </h3>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-semibold">
                                    Maturity Risk
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">X: Days to Due Date | Y: Contract Value ($) | Size: Margin Rate (%)</p>
                        </div>
                        <div class="my-2 min-w-0 overflow-hidden">
                            <div id="v2ContractRiskBubbleChart" class="w-full h-64"></div>
                        </div>
                        <div class="text-[11px] text-slate-400 border-t border-slate-100 pt-2 flex items-center justify-between">
                            <span>Delivery urgency & exposure</span>
                            <span class="font-medium text-slate-700">Hover for contract details</span>
                        </div>
                    </div>

                </div>

                <!-- ROW 3: HEATMAP MATRICES (Exact In-Cell Pastel Tints matching Image 1) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5">

                    <!-- TABLE 1: Operating Entity Trading Matrix -->
                    <div class="lg:col-span-7 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] min-w-0">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-[#0f1e36] tracking-tight">
                                    Operating Entity Trading Matrix
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Multi-company contract execution, procurement, sales and margin breakdown</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">
                                Entity Matrix
                            </span>
                        </div>

                        <!-- Table with Image 1's pastel cell backgrounds -->
                        <div class="overflow-x-auto custom-scrollbar -mx-5 px-5 sm:mx-0 sm:px-0">
                            <table class="w-full text-left text-xs min-w-[580px] border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                                        <th class="py-2.5 px-3">Operating Company</th>
                                        <th class="py-2.5 px-3 text-center">Contracts</th>
                                        <th class="py-2.5 px-3 text-center">Sales ($)</th>
                                        <th class="py-2.5 px-3 text-center">Procurement ($)</th>
                                        @if($canViewFinancials)
                                        <th class="py-2.5 px-3 text-center">Gross Margin ($)</th>
                                        <th class="py-2.5 px-3 text-center">Margin %</th>
                                        @endif
                                        <th class="py-2.5 px-3 w-28 text-center">Volume Share</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($companyMatrix as $cm)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <!-- Column 1: Expand icon [+] and Name -->
                                        <td class="py-3 px-3 text-[#0f1e36] font-semibold whitespace-nowrap">
                                             <div class="flex items-center gap-2">
                                                <span class="w-4 h-4 rounded border border-slate-300 text-slate-500 inline-flex items-center justify-center text-[10px] font-bold shrink-0">⊞</span>
                                                <span class="truncate">{{ $cm['name'] }}</span>
                                            </div>
                                        </td>
                                        <!-- Column 2: Pastel Blue Cell (#bde0f7) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#bde0f7] whitespace-nowrap">
                                            {{ $cm['contracts'] }}
                                        </td>
                                        <!-- Column 3: Pastel Soft Blue Cell (#cbe5f8) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#cbe5f8] whitespace-nowrap">
                                            ${{ number_format($cm['sales'] / 1000, 1) }}K
                                        </td>
                                        <!-- Column 4: Pastel Mint Green Cell (#c1ecd7) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#c1ecd7] whitespace-nowrap">
                                            ${{ number_format($cm['purchases'] / 1000, 1) }}K
                                        </td>
                                        @if($canViewFinancials)
                                        <!-- Column 5: Pastel Warm Peach Cell (#fae1c3) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#fae1c3] whitespace-nowrap">
                                            ${{ number_format($cm['profit'] / 1000, 1) }}K
                                        </td>
                                        <!-- Column 6: Pastel Lavender Cell (#e3daf5) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#e3daf5] whitespace-nowrap">
                                            {{ $cm['margin_pct'] }}%
                                        </td>
                                        @endif
                                        <!-- Column 7: Progress Bar -->
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <div class="flex-1 bg-slate-100 h-2 rounded-full overflow-hidden">
                                                    <div class="bg-gradient-to-r from-[#0082aa] to-[#5a30b5] h-full rounded-full" style="width: {{ $cm['heat_pct'] }}%"></div>
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

                    <!-- TABLE 2: Contract Workflow Pipeline by Stage -->
                    <div class="lg:col-span-5 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] min-w-0">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-[#0f1e36] tracking-tight">
                                    Contract Workflow Pipeline by Stage
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Order progression across execution stages: Draft, Purchase, Sales, Logistics & Completion</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-50 text-purple-700">
                                Workflow Stages
                            </span>
                        </div>

                        <div class="overflow-x-auto custom-scrollbar -mx-5 px-5 sm:mx-0 sm:px-0">
                            <table class="w-full text-left text-xs min-w-[360px] border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                                        <th class="py-2.5 px-3">Workflow Stage</th>
                                        <th class="py-2.5 px-3 text-center">Contracts</th>
                                        <th class="py-2.5 px-3 text-center">Stage Value ($)</th>
                                        <th class="py-2.5 px-3 text-center">Pipeline %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    @forelse($stageMatrix as $sm)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <!-- Column 1: Expand icon [+] and Stage Description -->
                                        <td class="py-3 px-3 text-[#0f1e36] font-semibold whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="w-4 h-4 rounded border border-slate-300 text-slate-500 inline-flex items-center justify-center text-[10px] font-bold shrink-0">⊞</span>
                                                <span class="truncate">{{ $sm['title'] }}</span>
                                            </div>
                                        </td>
                                        <!-- Column 2: Pastel Purple (#d9c9f2) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#d9c9f2] whitespace-nowrap">
                                            {{ $sm['count'] }}
                                        </td>
                                        <!-- Column 3: Pastel Blue (#bde0f7) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#bde0f7] whitespace-nowrap">
                                            ${{ number_format($sm['value'] / 1000, 1) }}K
                                        </td>
                                        <!-- Column 4: Pastel Mint (#bde8d4) -->
                                        <td class="py-3 px-3 text-center font-bold text-slate-900 bg-[#bde8d4] whitespace-nowrap">
                                            {{ $sm['pct'] }}%
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

            <!-- TAB 2: DYNAMICS TILES -->
            <div x-show="activeTab === 'dynamics'" class="space-y-4 sm:space-y-5" x-cloak>
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] min-w-0">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-[#0f1e36] tracking-tight">
                                Commodity Unit Pricing Dynamics ($/MT)
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">Average Sale Price vs Procurement Cost per Metric Ton (MT) across calendar periods</p>
                        </div>
                    </div>
                    <div id="v2PricingDynamicsChart" class="w-full h-72 sm:h-80"></div>
                </div>

                <!-- Operations & Logistics Metrics Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#00509d] flex items-center justify-center shrink-0">
                                <span class="material-icons-outlined">directions_boat</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">In-Transit Shipments</div>
                                <div class="text-2xl font-bold text-[#0f1e36] truncate">{{ $kpis['in_transit_count'] }}</div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">En-route between loading port and destination port.</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0da87a] flex items-center justify-center shrink-0">
                                <span class="material-icons-outlined">verified</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Completed Fulfillment</div>
                                <div class="text-2xl font-bold text-[#0f1e36] truncate">{{ $kpis['closed_count'] }}</div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Fully settled and invoiced commercial transactions.</p>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs sm:col-span-2 md:col-span-1">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#5a30b5] flex items-center justify-center shrink-0">
                                <span class="material-icons-outlined">inventory</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Goods Traded</div>
                                <div class="text-2xl font-bold text-[#0f1e36] truncate">{{ number_format($kpis['total_tonnage']) }} MT</div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Total physical metric tons scheduled in the period.</p>
                    </div>
                </div>
            </div>

            <!-- TAB 3: FINANCIAL BI (Admin / Finance Role Only) -->
            @if($canViewFinancials)
            <div x-show="activeTab === 'financials'" class="space-y-4 sm:space-y-5" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-stretch">
                    
                    <!-- Power BI Cash Flow & Profit Waterfall Chart Card -->
                    <div class="lg:col-span-8 bg-white rounded-2xl p-4 sm:p-5 lg:p-6 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] min-w-0 flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-3 sm:mb-4 flex-wrap gap-2">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-[#0f1e36] tracking-tight">
                                    Trading Margin & Realization Waterfall
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Stepwise capital realization: Gross Sales Revenue &rarr; Procurement Cost &rarr; Net Trading Margin</p>
                            </div>

                            <!-- Interactive Mode Switcher & Visual Badges -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200/80 shadow-xs">
                                    <button type="button" 
                                            @click="switchWaterfall('flow')" 
                                            :class="waterfallView === 'flow' ? 'bg-white text-[#0f1e36] shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900 font-medium'"
                                            class="px-2.5 py-1 text-[11px] rounded-md transition-all">
                                        Margin Realization
                                    </button>
                                    <button type="button" 
                                            @click="switchWaterfall('monthly')" 
                                            :class="waterfallView === 'monthly' ? 'bg-white text-[#0f1e36] shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900 font-medium'"
                                            class="px-2.5 py-1 text-[11px] rounded-md transition-all">
                                        Monthly Variance
                                    </button>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 hidden sm:inline-flex">
                                    Power BI Visual
                                </span>
                            </div>
                        </div>

                        <!-- Waterfall ApexChart Container -->
                        <div class="my-2 min-w-0 w-full overflow-hidden">
                            <div id="v2FinancialWaterfallChart" class="w-full min-h-[290px] sm:min-h-[320px]"></div>
                        </div>

                        <div class="text-[11px] text-slate-400 border-t border-slate-100 pt-2.5 flex flex-wrap items-center justify-between gap-1.5">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span x-text="waterfallView === 'flow' ? 'Stepwise margin realization' : 'Monthly budget variance from procurement'"></span>
                            </span>
                            <span class="font-semibold text-slate-700" x-text="waterfallView === 'flow' ? 'Gross Sales &rarr; Procurement Cost &rarr; Net Realized Margin' : 'Timeline Variance to Budget ($)'"></span>
                        </div>
                    </div>

                    <!-- 3 Financial Summary Cards (Fully responsive: 1 col on mobile, 3 cols on tablet, 1 col on desktop) -->
                    <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-3 sm:gap-4">
                        
                        <!-- Card 1: Net Trading Margin / Variance to Budget (Matching Power BI Image 2 style) -->
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider truncate">Realized Trading Margin</span>
                                <span class="material-icons-outlined text-sm {{ $kpis['gross_profit'] >= 0 ? 'text-emerald-500' : 'text-red-500' }} shrink-0">
                                    {{ $kpis['gross_profit'] >= 0 ? 'trending_up' : 'trending_down' }}
                                </span>
                            </div>
                            
                            <div class="my-2">
                                @php
                                    $gp = $kpis['gross_profit'];
                                    $absGp = abs($gp);
                                @endphp
                                <div class="text-2xl sm:text-xl md:text-2xl lg:text-3xl font-black {{ $gp >= 0 ? 'text-[#0f1e36]' : 'text-red-600' }} tracking-tight truncate" title="${{ number_format($gp, 2) }}">
                                    @if($gp < 0)
                                        @if($absGp >= 1000000)
                                            (${{ number_format($absGp / 1000000, 2) }}M)
                                        @elseif($absGp >= 1000)
                                            (${{ number_format($absGp / 1000, 1) }}K)
                                        @else
                                            (${{ number_format($absGp, 2) }})
                                        @endif
                                    @else
                                        @if($gp >= 1000000)
                                            ${{ number_format($gp / 1000000, 2) }}M
                                        @elseif($gp >= 1000)
                                            ${{ number_format($gp / 1000, 1) }}K
                                        @else
                                            ${{ number_format($gp, 2) }}
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2 gap-2">
                                <span class="px-2 py-0.5 rounded-md font-bold text-[11px] {{ $gp >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} shrink-0">
                                    {{ $kpis['profit_margin_pct'] }}% margin
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium truncate ml-auto" title="${{ number_format($gp, 2) }}">
                                    @if($gp < 0)
                                        -${{ number_format($absGp, 0) }}
                                    @else
                                        ${{ number_format($gp, 0) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Card 2: Procurement Cost -->
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider truncate">Procurement Cost</span>
                                <span class="material-icons-outlined text-sm text-[#bf2c78] shrink-0">shopping_bag</span>
                            </div>
                            
                            <div class="my-2">
                                @php
                                    $purch = $kpis['total_purchases'];
                                @endphp
                                <div class="text-2xl sm:text-xl md:text-2xl lg:text-3xl font-black text-[#0f1e36] tracking-tight truncate" title="${{ number_format($purch, 2) }}">
                                    @if($purch >= 1000000)
                                        ${{ number_format($purch / 1000000, 2) }}M
                                    @elseif($purch >= 1000)
                                        ${{ number_format($purch / 1000, 1) }}K
                                    @else
                                        ${{ number_format($purch, 2) }}
                                    @endif
                                </div>
                            </div>

                            <div class="text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2 gap-2">
                                <span class="text-slate-500 text-[11px] font-medium shrink-0">Capital deployed</span>
                                <span class="text-[11px] text-slate-400 font-medium truncate ml-auto" title="${{ number_format($purch, 2) }}">
                                    ${{ number_format($purch, 0) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card 3: Trading Multiplier (Yield / ROAS) -->
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col justify-between min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider truncate">Trading Multiplier</span>
                                <span class="material-icons-outlined text-sm text-[#5a30b5] shrink-0">auto_graph</span>
                            </div>
                            
                            <div class="my-2">
                                <div class="text-2xl sm:text-xl md:text-2xl lg:text-3xl font-black text-[#5a30b5] tracking-tight truncate">
                                    {{ number_format($kpis['roas_ratio'], 1) }}%
                                </div>
                            </div>

                            <div class="text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2 gap-2">
                                <span class="text-slate-500 text-[11px] font-medium shrink-0">Sales / Procurement</span>
                                <span class="text-indigo-600 font-bold text-[11px] shrink-0">Yield Ratio</span>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
            @endif

        </div>

        <!-- RIGHT-HAND POWER BI SLICER PANEL (Exact Image 1 Slicer Layout) -->
        <div x-show="showFilters" class="hidden xl:block xl:col-span-3 transition-all min-w-0">
            <div class="bg-white rounded-2xl p-5 shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200/80 sticky top-6 space-y-4 text-slate-700">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[#0284c7] text-lg">tune</span>
                        <h3 class="text-xs font-bold text-[#0f1e36] uppercase tracking-wider">
                            Slicers & Filters
                        </h3>
                    </div>
                    <button type="button" @click="resetFilters()" class="text-xs text-[#0284c7] hover:underline font-semibold">
                        Reset All
                    </button>
                </div>

                <!-- SLICER 1: Contract Date Range with Range Track (Matching Image 1) -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Contract Date Range</span>
                        <span class="material-icons-outlined text-xs text-slate-400">calendar_today</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="relative">
                            <input type="date" x-model="startDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-slate-800 focus:ring-2 focus:ring-[#0284c7] focus:outline-none">
                        </div>
                        <div class="relative">
                            <input type="date" x-model="endDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-slate-800 focus:ring-2 focus:ring-[#0284c7] focus:outline-none">
                        </div>
                    </div>
                    <!-- Range Slider Graphic Line (Matching Image 1 dual-handle line) -->
                    <div class="pt-1.5 pb-1">
                        <div class="relative w-full h-1 bg-slate-200 rounded-full flex items-center justify-between">
                            <div class="w-3.5 h-3.5 rounded-full border-2 border-[#0082aa] bg-white shadow-xs"></div>
                            <div class="flex-1 h-1 bg-[#0082aa]"></div>
                            <div class="w-3.5 h-3.5 rounded-full border-2 border-[#0082aa] bg-white shadow-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- SLICER 2: Operating Company -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Operating Company</span>
                        <span class="material-icons-outlined text-xs text-slate-400">apartment</span>
                    </label>
                    <select x-model="filterCompany" 
                            class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-slate-800 focus:ring-2 focus:ring-[#0284c7] focus:outline-none shadow-xs">
                        <option value="all">All</option>
                        @foreach($availableCompanies as $company)
                        <option value="{{ $company->id }}" {{ (string)$selectedCompanyId === (string)$company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- SLICER 3: Workflow Stage -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Workflow Stage</span>
                        <span class="material-icons-outlined text-xs text-slate-400">alt_route</span>
                    </label>
                    <select x-model="filterStage" 
                            class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-slate-800 focus:ring-2 focus:ring-[#0284c7] focus:outline-none shadow-xs">
                        <option value="all">All</option>
                        @foreach($allStages as $stage)
                        <option value="{{ $stage->id }}" {{ (string)$selectedStageId === (string)$stage->id ? 'selected' : '' }}>
                            {{ $stage->description ?: ($stage->type ?: 'Stage #' . $stage->id) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- SLICER 4: Trading Currency -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Trading Currency</span>
                        <span class="material-icons-outlined text-xs text-slate-400">monetization_on</span>
                    </label>
                    <select x-model="filterCurrency" 
                            class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-slate-800 focus:ring-2 focus:ring-[#0284c7] focus:outline-none shadow-xs">
                        <option value="all">All</option>
                        <option value="USD" {{ $selectedCurrency === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ $selectedCurrency === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ $selectedCurrency === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        <option value="CHF" {{ $selectedCurrency === 'CHF' ? 'selected' : '' }}>CHF (Fr)</option>
                    </select>
                </div>

                <!-- Apply Filters Button -->
                <button type="button" @click="applyFilters()" :disabled="isApplying" 
                        class="w-full py-2.5 px-4 rounded-xl bg-[#00509d] hover:bg-[#08549e] disabled:opacity-50 text-white font-semibold text-xs transition-all shadow-md shadow-blue-500/20 flex items-center justify-center gap-2">
                    <span class="material-icons-outlined text-base">filter_list</span>
                    <span x-text="isApplying ? 'Applying Filters...' : 'Apply Slicers'"></span>
                </button>

                <!-- "Have questions?" CARD (Exact layout from bottom-right of Image 1) -->
                <div class="border border-slate-200 rounded-2xl p-4 bg-white space-y-2.5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#0f1e36]">Have questions?</span>
                        <!-- Support Avatar -->
                        <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <span class="material-icons-outlined text-sm">support_agent</span>
                        </div>
                    </div>
                    <ul class="text-[11px] space-y-1.5 font-medium">
                        <li>
                            <a href="#" class="text-[#0284c7] hover:underline flex items-center gap-1.5">
                                <span>Dashboard setup guide</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-[#0284c7] hover:underline flex items-center gap-1.5">
                                <span>Book a demo</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-[#0284c7] hover:underline flex items-center gap-1.5">
                                <span>Contact support</span>
                            </a>
                        </li>
                    </ul>
                    <!-- Social Icons -->
                    <div class="flex items-center gap-2 text-slate-400 pt-1 border-t border-slate-100">
                        <span class="material-icons-outlined text-sm text-red-500">smart_display</span>
                        <span class="material-icons-outlined text-sm text-cyan-600">language</span>
                        <span class="text-[10px] text-slate-400 ml-auto">MetaERP v2</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- BOTTOM DEEP PURPLE ACCENT BANNER -->
    <div class="bg-[#241352] text-white py-3 px-6 rounded-2xl flex items-center justify-center text-xs font-semibold tracking-wide shadow-md">
        <span>MetaERP Enterprise Trading & Logistics Intelligence Platform</span>
    </div>

    <!-- MOBILE / TABLET OFF-CANVAS SLICER DRAWER -->
    <div x-show="mobileFiltersOpen" 
         class="fixed inset-0 z-50 xl:hidden overflow-hidden" 
         x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             @click="mobileFiltersOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white shadow-2xl p-5 space-y-4 overflow-y-auto custom-scrollbar border-l border-slate-200">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[#0284c7] text-lg">tune</span>
                        <h3 class="text-sm font-bold text-[#0f1e36] uppercase tracking-wider">
                            Filters & Data Slicers
                        </h3>
                    </div>
                    <button type="button" @click="mobileFiltersOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                        <span class="material-icons-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Date Range -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Contract Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5">Start</span>
                            <input type="date" x-model="startDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 px-2.5 py-2 text-slate-800">
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5">End</span>
                            <input type="date" x-model="endDate" 
                                   class="w-full text-xs rounded-xl border border-slate-200 px-2.5 py-2 text-slate-800">
                        </div>
                    </div>
                </div>

                <!-- Company -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Operating Company</label>
                    <select x-model="filterCompany" 
                            class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800">
                        <option value="all">All</option>
                        @foreach($availableCompanies as $company)
                        <option value="{{ $company->id }}" {{ (string)$selectedCompanyId === (string)$company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stage -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Workflow Stage</label>
                    <select x-model="filterStage" 
                            class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800">
                        <option value="all">All</option>
                        @foreach($allStages as $stage)
                        <option value="{{ $stage->id }}" {{ (string)$selectedStageId === (string)$stage->id ? 'selected' : '' }}>
                            {{ $stage->description ?: ($stage->type ?: 'Stage #' . $stage->id) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Currency -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Trading Currency</label>
                    <select x-model="filterCurrency" 
                            class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800">
                        <option value="all">All</option>
                        <option value="USD" {{ $selectedCurrency === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ $selectedCurrency === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ $selectedCurrency === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        <option value="CHF" {{ $selectedCurrency === 'CHF' ? 'selected' : '' }}>CHF (Fr)</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="pt-3 space-y-2">
                    <button type="button" @click="applyFilters()" :disabled="isApplying" 
                            class="w-full py-3 px-4 rounded-xl bg-[#00509d] text-white font-semibold text-xs shadow-md flex items-center justify-center gap-2">
                        <span class="material-icons-outlined text-base">filter_list</span>
                        <span x-text="isApplying ? 'Applying Filters...' : 'Apply Slicers'"></span>
                    </button>
                    <button type="button" @click="resetFilters()" 
                            class="w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs">
                        Reset Slicers
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- APEXCHARTS LIBRARY & VISUAL INITIALIZATION (With Palette matching Image 1 & 2) -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    const sharedFontFamily = 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

    // 1. DONUT CHART (Sales Revenue by Company - Colors matching Image 1)
    const donutLabels = @json($donut['labels']);
    const donutSeries = @json($donut['series']);
    // Image 1 Donut Palette: Mint Jade Green, Cerulean Cyan, Ocean Blue, Violet Purple, Magenta Rose, Amber
    const donutColors = ['#0da87a', '#0082aa', '#00509d', '#5a30b5', '#bf2c78', '#eab308', '#f97316', '#64748b'];

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
            stroke: { width: 3, colors: ['#ffffff'] },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + "%";
                },
                style: { fontSize: '11px', fontWeight: 700 }
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
                        size: '60%',
                        labels: {
                            show: true,
                            name: { fontSize: '11px', fontWeight: 600, color: '#64748b' },
                            value: {
                                fontSize: '15px',
                                fontWeight: 800,
                                color: '#0f1e36',
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

    // 2. STACKED TIMELINE BAR CHART (Sales vs Procurement Timeline)
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
                { name: 'Sales Revenue ($)', data: stackedSales },
                { name: 'Procurement Cost ($)', data: stackedPurchases }
            ],
            // Cyan & Purple matching stacked columns in Image 1
            colors: ['#0082aa', '#5a30b5'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 3,
                    columnWidth: '50%'
                }
            },
            xaxis: {
                categories: stackedCategories,
                labels: { style: { fontSize: '10px', colors: '#64748b' } },
                axisBorder: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return '$' + (val >= 1000 ? (val / 1000).toFixed(0) + 'K' : val);
                    },
                    style: { fontSize: '10px', colors: '#64748b' }
                }
            },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 3 },
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

    // 3. POWER BI SEMI-CIRCLE RADIAL GAUGE (Annual Sales Target Fulfillment)
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
            colors: ['#0082aa'],
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
                            fontSize: '22px',
                            fontWeight: 900,
                            color: '#0082aa',
                            offsetY: 4,
                            formatter: function (val) {
                                return val + "%";
                            }
                        }
                    }
                }
            },
            stroke: { lineCap: 'round' },
            labels: ['Target Achieved'],
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

    // 4. POWER BI HORIZONTAL GROUPED BAR (Top Commercial Counterparties)
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
            series: [{ name: 'Sales Value ($)', data: buyerValues.length > 0 ? buyerValues : [0] }],
            colors: ['#0082aa'],
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
            yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#0f1e36' } } },
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

    // 5. POWER BI CONTRACT RISK 3D BUBBLE / SCATTER PLOT (Contract Maturity & Exposure Matrix)
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
                { name: 'Contract Exposure', data: bubbleData }
            ],
            // Violet color theme for contract exposure
            colors: ['#732eb0'],
            dataLabels: { enabled: false },
            fill: { opacity: 0.8 },
            xaxis: {
                title: { text: 'Days to Due Date', style: { fontSize: '10px', color: '#64748b' } },
                labels: { style: { fontSize: '10px' } }
            },
            yaxis: {
                title: { text: 'Contract Value ($)', style: { fontSize: '10px', color: '#64748b' } },
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
                    return '<div class="p-2.5 text-xs bg-slate-900 text-white rounded-lg shadow-lg">' +
                           '<div class="font-bold text-sky-400">' + pt.name + '</div>' +
                           '<div>Contract Value: $' + Number(pt.y).toLocaleString() + '</div>' +
                           '<div>Days to Maturity: ' + pt.x + ' days</div>' +
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

    // 6. DYNAMICS TAB: Commodity Unit Pricing Dynamics ($/MT)
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
                { name: 'Average Sale Price ($/MT)', type: 'line', data: unitPrices },
                { name: 'Procurement Cost ($/MT)', type: 'line', data: unitCosts }
            ],
            colors: ['#0082aa', '#0da87a'],
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

    // Formatters for Financial BI & Waterfall Visuals
    function formatMoneyCompact(val, withSign = true) {
        if (val === null || val === undefined || isNaN(val)) return '$0';
        const isNeg = Number(val) < 0;
        const absVal = Math.abs(Number(val));
        let numStr = '';
        if (absVal >= 1000000) {
            numStr = (absVal / 1000000).toFixed(2).replace(/\.00$/, '') + 'M';
        } else if (absVal >= 1000) {
            numStr = (absVal / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
        } else {
            numStr = absVal.toFixed(0);
        }
        if (!withSign) return '$' + numStr;
        return isNeg ? '(-$' + numStr + ')' : '+$' + numStr;
    }

    function formatAxisMoney(val) {
        if (val === null || val === undefined || isNaN(val)) return '$0';
        const isNeg = Number(val) < 0;
        const absVal = Math.abs(Number(val));
        let numStr = '';
        if (absVal >= 1000000) {
            numStr = (absVal / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
        } else if (absVal >= 1000) {
            numStr = (absVal / 1000).toFixed(0) + 'K';
        } else {
            numStr = absVal.toFixed(0);
        }
        return isNeg ? '-$' + numStr : '$' + numStr;
    }

    // 7. FINANCIAL WATERFALL CHART (Power BI Budget Remaining style)
    window.waterfallChartInstance = null;
    window.renderWaterfallChart = function(mode = 'flow') {
        const waterfallEl = document.querySelector('#v2FinancialWaterfallChart');
        if (!waterfallEl) return;

        if (window.waterfallChartInstance) {
            try {
                window.waterfallChartInstance.destroy();
            } catch(e) {}
            window.waterfallChartInstance = null;
        }

        let seriesData = [];
        if (mode === 'monthly') {
            const categories = @json($stacked['categories'] ?? []);
            const sales = @json($stacked['sales'] ?? []);
            const purchases = @json($stacked['purchases'] ?? []);

            seriesData = categories.map((cat, idx) => {
                const s = Number(sales[idx] || 0);
                const p = Number(purchases[idx] || 0);
                const diff = s - p;
                return {
                    x: cat,
                    y: diff,
                    fillColor: diff >= 0 ? '#0082aa' : '#bf2c78'
                };
            });
            if (seriesData.length === 0) {
                seriesData = [{ x: 'Current Period', y: {{ $kpis['gross_profit'] }}, fillColor: '{{ $kpis['gross_profit'] >= 0 ? '#0da87a' : '#bf2c78' }}' }];
            }
        } else {
            const totalSales = {{ $kpis['total_sales'] }};
            const totalPurchases = {{ $kpis['total_purchases'] }};
            const grossProfit = {{ $kpis['gross_profit'] }};

            seriesData = [
                { x: 'Gross Sales', y: totalSales, fillColor: '#0082aa' },
                { x: 'Procurement Cost', y: -totalPurchases, fillColor: '#bf2c78' },
                { x: 'Net Trading Profit', y: grossProfit, fillColor: grossProfit >= 0 ? '#0da87a' : '#dc2626' }
            ];
        }

        const waterfallOptions = {
            chart: {
                type: 'bar',
                height: 310,
                fontFamily: sharedFontFamily,
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 400
                }
            },
            series: [{
                name: mode === 'monthly' ? 'Monthly Budget Variance' : 'Financial Flow',
                data: seriesData
            }],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 4,
                    columnWidth: mode === 'monthly' ? '50%' : '42%',
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return formatMoneyCompact(val, true);
                },
                offsetY: -8,
                style: {
                    fontSize: '11px',
                    fontFamily: sharedFontFamily,
                    fontWeight: '700',
                    colors: ['#0f1e36']
                },
                background: {
                    enabled: true,
                    foreColor: '#0f1e36',
                    padding: 5,
                    borderRadius: 5,
                    borderWidth: 1,
                    borderColor: '#cbd5e1',
                    opacity: 0.96,
                    dropShadow: { enabled: false }
                }
            },
            xaxis: {
                labels: {
                    style: {
                        fontSize: '11px',
                        fontWeight: 600,
                        colors: '#475569',
                        fontFamily: sharedFontFamily
                    }
                },
                axisBorder: { show: true, color: '#e2e8f0' },
                axisTicks: { show: true, color: '#e2e8f0' }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return formatAxisMoney(val);
                    },
                    style: {
                        fontSize: '10px',
                        colors: '#64748b',
                        fontFamily: sharedFontFamily
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        const isNeg = Number(val) < 0;
                        const absVal = Math.abs(Number(val));
                        const formatted = Number(absVal).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        return (isNeg ? '(-$' : '$') + formatted + (isNeg ? ')' : '');
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        chart: { height: 270 },
                        plotOptions: { bar: { columnWidth: '55%' } },
                        dataLabels: {
                            offsetY: -6,
                            style: { fontSize: '10px' }
                        },
                        xaxis: {
                            labels: {
                                rotate: -15,
                                style: { fontSize: '10px' }
                            }
                        }
                    }
                },
                {
                    breakpoint: 480,
                    options: {
                        chart: { height: 250 },
                        plotOptions: { bar: { columnWidth: '65%' } },
                        dataLabels: {
                            offsetY: -4,
                            style: { fontSize: '9px' }
                        },
                        xaxis: {
                            labels: {
                                rotate: -25,
                                style: { fontSize: '9px' }
                            }
                        }
                    }
                }
            ]
        };

        window.waterfallChartInstance = new ApexCharts(waterfallEl, waterfallOptions);
        window.waterfallChartInstance.render();
    };

    // Render immediately on load
    if (document.querySelector('#v2FinancialWaterfallChart')) {
        window.renderWaterfallChart('flow');
    }

});
</script>
@endsection
