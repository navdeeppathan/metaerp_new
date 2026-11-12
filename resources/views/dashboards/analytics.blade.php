@extends('layouts.app')

@section('title', 'Analytics Dashboard - MetaERP')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto pb-12">

    <!-- Header matching AnalyticsDashboardAppHeader.js -->
    <div class="flex flex-col sm:flex-row flex-auto sm:items-center justify-between min-w-0 pb-2">
        <div class="flex flex-col flex-auto">
            <h1 class="text-3xl font-semibold tracking-tight leading-8 text-slate-900 dark:text-white">
                Analytics dashboard
            </h1>
            <p class="font-medium tracking-tight text-slate-500 mt-1 text-sm">
                Monitor metrics, check reports and review performance
            </p>
        </div>
        <div class="flex items-center mt-4 sm:mt-0 space-x-3">
            <button type="button" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors shadow-xs">
                <span class="material-icons-outlined text-lg">settings</span>
                <span>Settings</span>
            </button>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg bg-[#4f46e5] text-white hover:bg-[#4338ca] transition-colors shadow-xs">
                <span class="material-icons-outlined text-lg">save</span>
                <span>Export</span>
            </button>
        </div>
    </div>

    <!-- Main Grid matching AnalyticsDashboardApp.js -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">

        <!-- Widget 1: VisitorsOverviewWidget / Contracts Overview -->
        <div class="sm:col-span-2 lg:col-span-3 bg-[#1e293b] text-white shadow-xl rounded-2xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-6 sm:px-8 pb-2">
                <div class="flex flex-col">
                    <h2 class="text-2xl md:text-3xl font-semibold tracking-tight leading-7 text-white">
                        Contracts Overview
                    </h2>
                    <span class="font-medium text-slate-400 text-sm mt-1">
                        Sales amount
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <span class="inline-flex items-center gap-1.5 text-slate-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#38bdf8]"></span>
                        <span>Purchases</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#ffff55]"></span>
                        <span>Sales</span>
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-300 text-xs font-semibold">
                        60 days
                    </span>
                </div>
            </div>

            <div class="p-4 sm:p-6 pt-0">
                <div id="contractsOverviewChart" class="w-full h-80"></div>
            </div>
        </div>

        <!-- Widget 2: ConversionsWidget for each company -->
        @foreach ($companyWidgets as $idx => $cw)
            @php
                $themeColor = $cw['themeColor'];
                $sparklineColor = $themeColor === 'success' ? '#10b981' : ($themeColor === 'secondary' ? '#6366f1' : '#ef4444');
                $sparklineLight = $themeColor === 'success' ? '#86efac' : ($themeColor === 'secondary' ? '#a5b4fc' : '#fca5a5');
            @endphp
            <div class="sm:col-span-2 lg:col-span-1 bg-white dark:bg-slate-800 shadow rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700/60 flex flex-col justify-between">
                <div class="p-6 pb-2">
                    <div class="flex items-start justify-between">
                        <h3 class="text-lg font-medium tracking-tight leading-6 truncate text-slate-800 dark:text-slate-100" title="{{ $cw['name'] }}">
                            {{ $cw['name'] }}
                        </h3>
                        <div class="ml-4 shrink-0">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                30 days
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-center mt-3 gap-2">
                        <div class="text-4xl lg:text-5xl font-bold tracking-tighter leading-tight text-slate-900 dark:text-white">
                            {{ number_format($cw['amount'], 2) }}
                        </div>
                        <div class="flex items-center lg:flex-col lg:items-start lg:ml-3 text-red-500">
                            <div class="flex items-center gap-1">
                                <span class="material-icons-outlined text-base">trending_down</span>
                                <span class="text-xs font-bold leading-none">2%</span>
                            </div>
                            <span class="text-xs text-slate-400 dark:text-slate-500 lg:mt-0.5 ml-1 lg:ml-0 whitespace-nowrap">
                                below target
                            </span>
                        </div>
                    </div>
                </div>

                <div class="h-20 w-full overflow-hidden mt-2">
                    <div id="companySparkline_{{ $cw['id'] }}" class="w-full h-full -mb-3"></div>
                </div>
            </div>
        @endforeach

        <!-- Section: Contracts per stage -->
        <div class="w-full mt-4 sm:col-span-2 lg:col-span-3">
            <h2 class="text-2xl font-semibold tracking-tight leading-6 text-slate-900 dark:text-white">
                Contracts per stage
            </h2>
            <p class="font-medium tracking-tight text-slate-500 text-sm mt-1">
                Number of contracts
            </p>
        </div>

        <div class="sm:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
            @forelse ($stageWidgets as $sw)
                <div class="bg-white dark:bg-slate-800 shadow rounded-2xl overflow-hidden p-6 border border-slate-100 dark:border-slate-700/60 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between">
                            <h3 class="text-lg font-medium tracking-tight leading-6 truncate text-slate-800 dark:text-slate-100" title="{{ $sw['title'] }}">
                                {{ $sw['title'] }}
                            </h3>
                            <div class="ml-2 shrink-0">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    30 days
                                </span>
                            </div>
                        </div>

                        <!-- Donut Chart -->
                        <div class="mt-4 h-48 flex items-center justify-center">
                            <div id="stageDonut_{{ $sw['id'] }}" class="w-full flex items-center justify-center"></div>
                        </div>
                    </div>

                    <!-- Breakdown Table -->
                    <div class="mt-6 pt-2 border-t border-slate-100 dark:border-slate-700/50">
                        <div class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs">
                            @foreach ($sw['breakdown'] as $b)
                                <div class="grid grid-cols-3 py-2 items-center">
                                    <div class="flex items-center min-w-0 pr-2">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0 mr-2" style="background-color: {{ $b['color'] }};"></span>
                                        <span class="truncate font-medium text-slate-700 dark:text-slate-300">{{ $b['label'] }}</span>
                                    </div>
                                    <div class="font-semibold text-right text-slate-900 dark:text-white">
                                        {{ number_format($b['count']) }}
                                    </div>
                                    <div class="text-right text-slate-400 font-medium">
                                        {{ $b['percentage'] }}%
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-sm">
                    No stage data available for workflow model.
                </div>
            @endforelse
        </div>

    </div>

</div>

<!-- ApexCharts Script -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. VisitorsOverviewWidget (Contracts Overview)
    const overviewSeriesData = @json($overviewSeries);

    const overviewOptions = {
        chart: {
            animations: {
                speed: 400,
                animateGradually: { enabled: false },
            },
            fontFamily: 'Inter, sans-serif',
            foreColor: '#94a3b8',
            width: '100%',
            height: 320,
            type: 'line',
            toolbar: { show: false },
            zoom: { enabled: false },
        },
        colors: ['#38bdf8', '#ffff55'],
        dataLabels: { enabled: false },
        grid: {
            show: true,
            borderColor: '#334155',
            padding: { top: 10, bottom: -20, left: 10, right: 10 },
            position: 'back',
            xaxis: { lines: { show: true } },
        },
        stroke: { width: 2, curve: 'smooth' },
        tooltip: {
            followCursor: true,
            theme: 'dark',
            x: { format: 'dd/MM/yyyy' },
            y: { formatter: (value) => Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
        },
        xaxis: {
            type: 'datetime',
            axisBorder: { show: false },
            axisTicks: { show: false },
            crosshairs: {
                stroke: { color: '#475569', dashArray: 0, width: 2 },
            },
            labels: {
                offsetY: -5,
                style: { colors: '#94a3b8' },
            },
            tickAmount: 10,
        },
        yaxis: {
            show: false,
            axisTicks: { show: false },
            axisBorder: { show: false },
            min: 0,
        },
        series: overviewSeriesData,
    };

    const overviewChartEl = document.querySelector('#contractsOverviewChart');
    if (overviewChartEl) {
        const overviewChart = new ApexCharts(overviewChartEl, overviewOptions);
        overviewChart.render();
    }

    // 2. ConversionsWidget (Company Sparkline Charts)
    const companyWidgets = @json($companyWidgets);
    const themeColors = {
        success: { main: '#10b981', light: '#a7f3d0' },
        secondary: { main: '#6366f1', light: '#c7d2fe' },
        error: { main: '#ef4444', light: '#fecaca' }
    };

    companyWidgets.forEach((cw) => {
        const el = document.querySelector(`#companySparkline_${cw.id}`);
        if (!el) return;

        const palette = themeColors[cw.themeColor] || themeColors.success;
        const sparklineOptions = {
            chart: {
                animations: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: 'inherit',
                height: 80,
                type: 'area',
                sparkline: { enabled: true },
            },
            colors: [palette.main],
            fill: {
                colors: [palette.light],
                opacity: 0.5,
            },
            stroke: { curve: 'smooth', width: 2 },
            tooltip: {
                followCursor: true,
                theme: 'dark',
                x: { format: 'dd/MM/yyyy' },
                y: { formatter: (val) => Number(val).toLocaleString('en-US') },
            },
            xaxis: { type: 'datetime' },
            series: cw.sparklineSeries,
        };

        const sparkChart = new ApexCharts(el, sparklineOptions);
        sparkChart.render();
    });

    // 3. NewVsReturningWidget (Donut Charts per Stage)
    const stageWidgets = @json($stageWidgets);

    stageWidgets.forEach((sw) => {
        const el = document.querySelector(`#stageDonut_${sw.id}`);
        if (!el) return;

        const donutOptions = {
            chart: {
                animations: {
                    speed: 400,
                    animateGradually: { enabled: false },
                },
                fontFamily: 'Inter, sans-serif',
                foreColor: 'inherit',
                height: 192,
                type: 'donut',
                sparkline: { enabled: true },
            },
            colors: sw.colors || ['#3182CE', '#63B3ED', '#483FF2', '#3F6FF2', '#833FF2', '#F2D53F', '#736E57', '#F2CA3F'],
            labels: sw.labels,
            plotOptions: {
                pie: {
                    customScale: 0.9,
                    expandOnClick: false,
                    donut: { size: '70%' },
                },
            },
            stroke: {
                colors: ['#ffffff'],
                width: 2,
            },
            series: sw.series.length > 0 ? sw.series : [0],
            tooltip: {
                enabled: true,
                fillSeriesColor: false,
                theme: 'dark',
                custom: function ({ seriesIndex, w }) {
                    const color = w.config.colors[seriesIndex] || '#3182CE';
                    const label = w.config.labels[seriesIndex] || 'N/A';
                    const pct = w.config.series[seriesIndex] || 0;
                    return `<div class="flex items-center h-8 px-3 text-xs">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${color};"></div>
                        <div class="text-slate-300 mr-2">${label}:</div>
                        <div class="font-bold text-white">${pct}%</div>
                    </div>`;
                },
            },
        };

        const donutChart = new ApexCharts(el, donutOptions);
        donutChart.render();
    });
});
</script>
@endsection
