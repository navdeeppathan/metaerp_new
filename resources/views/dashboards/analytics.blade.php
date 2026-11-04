@extends('layouts.app')

@section('title', 'Metabase Dashboard - MetaERP')

@section('content')
<div class="space-y-4 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="pb-1">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
            Metabase dashboard
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
            Monitor metrics, check reports and review performance
        </p>
    </div>

    <!-- Main Card Panel -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 sm:p-7">
        <!-- Card Top Bar: E-commerce insights & Export button -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-slate-800">
                    E-commerce insights
                </h2>
                <span class="material-icons-outlined text-sm text-slate-400 cursor-pointer" title="Dashboard details">info</span>
            </div>
            <div>
                <a href="javascript:window.print()" class="text-blue-600 hover:text-blue-700 flex items-center gap-1.5 text-xs font-semibold transition-colors">
                    <span class="material-icons-outlined text-sm">picture_as_pdf</span>
                    <span>Export tab as PDF</span>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 flex items-center gap-6 mt-4 text-xs font-medium">
            <button type="button" class="text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px] focus:outline-none">
                Overview
            </button>
            <button type="button" class="text-slate-500 hover:text-slate-800 pb-2.5 transition-colors focus:outline-none">
                Portfolio performance
            </button>
            <button type="button" class="text-slate-500 hover:text-slate-800 pb-2.5 transition-colors focus:outline-none">
                Demographics
            </button>
        </div>

        <!-- Section Title -->
        <h3 class="text-base font-bold text-slate-800 mt-6 mb-4">
            Overall business health
        </h3>

        <!-- 4 KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Revenue per quarter -->
            <div class="border border-slate-200/80 rounded-lg p-4 bg-white flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-700">Revenue per quarter</span>
                <div class="my-4 text-center">
                    <div class="text-3xl font-extrabold text-slate-800 tracking-tight">
                        30,759.47
                    </div>
                    <div class="text-xs text-slate-500 font-medium mt-1">
                        Q2 2026
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 text-center font-normal">
                    N/A &bull; vs. previous quarter: (No data)
                </div>
            </div>

            <!-- Card 2: Discount given per quarter -->
            <div class="border border-slate-200/80 rounded-lg p-4 bg-white flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-700">Discount given per quarter</span>
                <div class="my-4 text-center">
                    <div class="text-3xl font-extrabold text-slate-800 tracking-tight">
                        $171.75
                    </div>
                    <div class="text-xs text-slate-500 font-medium mt-1">
                        Q2 2026
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 text-center font-normal">
                    N/A &bull; vs. previous quarter: (No data)
                </div>
            </div>

            <!-- Card 3: Number of orders per month -->
            <div class="border border-slate-200/80 rounded-lg p-4 bg-white flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-700">Number of orders per month</span>
                <div class="py-3 flex items-center justify-center">
                    <svg class="w-24 h-16 text-slate-300" viewBox="0 0 100 80" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="48" y1="12" x2="48" y2="52" />
                        <path d="M48 16 L20 48 L48 48 Z" />
                        <path d="M52 22 L78 48 L52 48 Z" />
                        <path d="M14 54 L84 54 L74 65 L24 65 Z" />
                        <path d="M8 70 Q 24 73 40 70 T 72 70 T 92 70" />
                        <path d="M18 75 Q 34 78 50 75 T 82 75" />
                    </svg>
                </div>
                <div class="h-4"></div>
            </div>

            <!-- Card 4: Unique customers per month -->
            <div class="border border-slate-200/80 rounded-lg p-4 bg-white flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-700">Unique customers per month</span>
                <div class="py-3 flex items-center justify-center">
                    <svg class="w-24 h-16 text-slate-300" viewBox="0 0 100 80" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="48" y1="12" x2="48" y2="52" />
                        <path d="M48 16 L20 48 L48 48 Z" />
                        <path d="M52 22 L78 48 L52 48 Z" />
                        <path d="M14 54 L84 54 L74 65 L24 65 Z" />
                        <path d="M8 70 Q 24 73 40 70 T 72 70 T 92 70" />
                        <path d="M18 75 Q 34 78 50 75 T 82 75" />
                    </svg>
                </div>
                <div class="h-4"></div>
            </div>
        </div>

        <!-- Bottom Row: Goal & Revenue & Orders Over Time -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
            <!-- Card 5: Revenue goal for this quarter -->
            <div class="border border-slate-200/80 rounded-lg p-4 bg-white flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-700">Revenue goal for this quarter</span>
                <div class="mt-4 mb-2">
                    <span class="text-xs font-bold text-slate-700">$0</span>
                </div>
                <div class="w-full h-16 bg-[#a3e635] rounded-t-sm"></div>
            </div>

            <!-- Card 6: Revenue and orders over time -->
            <div class="border border-slate-200/80 rounded-lg p-4 bg-white lg:col-span-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                    <span class="text-xs font-semibold text-slate-700">Revenue and orders over time</span>
                    <div class="flex items-center gap-4 text-xs font-medium text-slate-600">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#8cb04a]"></span>
                            <span>Revenue</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#4d4484]"></span>
                            <span>Number of orders</span>
                        </span>
                    </div>
                </div>

                <!-- Combo Chart SVG -->
                <div class="w-full overflow-hidden">
                    <svg class="w-full h-40" viewBox="0 0 700 160" fill="none" preserveAspectRatio="none">
                        <!-- Grid Lines -->
                        <line x1="55" y1="20" x2="645" y2="20" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="55" y1="50" x2="645" y2="50" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="55" y1="80" x2="645" y2="80" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="55" y1="110" x2="645" y2="110" stroke="#f1f5f9" stroke-width="1" />
                        <line x1="55" y1="140" x2="645" y2="140" stroke="#e2e8f0" stroke-width="1" />

                        <!-- Left Axis Labels -->
                        <text x="48" y="24" text-anchor="end" font-size="10" fill="#64748b" font-family="Inter, sans-serif">60,000</text>
                        <text x="48" y="54" text-anchor="end" font-size="10" fill="#64748b" font-family="Inter, sans-serif">50,000</text>
                        <text x="48" y="84" text-anchor="end" font-size="10" fill="#64748b" font-family="Inter, sans-serif">40,000</text>
                        <text x="48" y="114" text-anchor="end" font-size="10" fill="#64748b" font-family="Inter, sans-serif">30,000</text>

                        <!-- Right Axis Labels -->
                        <text x="652" y="54" text-anchor="start" font-size="10" fill="#64748b" font-family="Inter, sans-serif">4,000</text>
                        <text x="652" y="84" text-anchor="start" font-size="10" fill="#64748b" font-family="Inter, sans-serif">3,000</text>
                        <text x="652" y="114" text-anchor="start" font-size="10" fill="#64748b" font-family="Inter, sans-serif">2,000</text>

                        <!-- Indigo Bars for Number of Orders -->
                        <rect x="260" y="100" width="24" height="40" fill="#4d4484" rx="1" />
                        <rect x="295" y="70" width="24" height="70" fill="#4d4484" rx="1" />
                        <rect x="330" y="88" width="24" height="52" fill="#4d4484" rx="1" />
                        <rect x="365" y="52" width="24" height="88" fill="#4d4484" rx="1" />
                        <rect x="400" y="88" width="24" height="52" fill="#4d4484" rx="1" />
                        <rect x="435" y="76" width="24" height="64" fill="#4d4484" rx="1" />

                        <!-- Green Smooth Line for Revenue -->
                        <path d="M 55 52 C 100 52, 120 46, 150 46 C 180 46, 200 54, 230 50 C 260 46, 280 52, 310 52 C 340 52, 360 52, 390 55 C 420 58, 450 54, 480 54 C 510 54, 540 44, 570 48 C 600 52, 615 85, 645 135"
                              stroke="#8cb04a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
