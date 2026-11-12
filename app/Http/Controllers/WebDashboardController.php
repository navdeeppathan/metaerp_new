<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ModelWorkflow;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WebDashboardController extends Controller
{
    public function analytics(Request $request)
    {
        $dashboardData = $this->buildDashboardData();

        return view('dashboards.analytics', $dashboardData);
    }

    public function apiWidgets()
    {
        return response()->json($this->buildDashboardData());
    }

    private function buildDashboardData(): array
    {
        $companies = Company::where('active', true)->orderBy('id')->get();

        $contracts = Contract::with([
            'stage',
            'purchase.contact',
            'purchase.products',
            'sale.contact',
            'sale.products',
        ])
        ->where('active', true)
        ->get();

        $models = ModelWorkflow::with([
            'stages' => function ($q) {
                $q->orderBy('position', 'asc');
            },
            'stages.contracts.purchase.products',
            'stages.contracts.sale.products',
        ])
        ->where('active', true)
        ->get();

        // 1. VisitorsOverviewWidget (Contracts Overview): 60-day Timeline of Purchases & Sales
        $rangeDays = 60;
        $now = Carbon::now();
        $startDate = (clone $now)->startOfDay()->subDays($rangeDays);

        $timestampBuckets = [];
        $purchasesByDay = [];
        $salesByDay = [];

        for ($i = 0; $i <= $rangeDays; $i++) {
            $dayStart = (clone $startDate)->addDays($i)->startOfDay();
            $ts = $dayStart->timestamp * 1000;
            $timestampBuckets[] = $ts;
            $purchasesByDay[$ts] = 0.0;
            $salesByDay[$ts] = 0.0;
        }

        foreach ($contracts as $c) {
            $dueDate = $c->due_date ?: $c->loading_date;
            if (!$dueDate) {
                continue;
            }

            $dueDayStart = (clone $dueDate)->startOfDay()->timestamp * 1000;
            if (!isset($purchasesByDay[$dueDayStart])) {
                // If not exactly in the 60-day daily grid, find nearest or skip
                continue;
            }

            // Buyer Contract -> Purchases
            if ($c->purchase && $c->purchase->products) {
                $totalPurchase = 0.0;
                foreach ($c->purchase->products as $p) {
                    $itemTotal = (float) ($p->total_price ?: ($p->total ?: ($p->quantity * ($p->unit_price ?: ($p->rate ?: 0)))));
                    $totalPurchase += $itemTotal;
                }
                if ($totalPurchase > 0) {
                    $purchasesByDay[$dueDayStart] += $totalPurchase;
                }
            }

            // Seller Contract -> Sales
            if ($c->sale && $c->sale->products) {
                $totalSale = 0.0;
                foreach ($c->sale->products as $p) {
                    $itemTotal = (float) ($p->total_price ?: ($p->total ?: ($p->quantity * ($p->unit_price ?: ($p->rate ?: 0)))));
                    $totalSale += $itemTotal;
                }
                if ($totalSale > 0) {
                    $salesByDay[$dueDayStart] += $totalSale;
                }
            }
        }

        $purchasesSeriesData = [];
        $salesSeriesData = [];
        foreach ($timestampBuckets as $ts) {
            $purchasesSeriesData[] = ['x' => $ts, 'y' => round($purchasesByDay[$ts], 2)];
            $salesSeriesData[] = ['x' => $ts, 'y' => round($salesByDay[$ts], 2)];
        }

        $overviewSeries = [
            ['name' => 'Purchases', 'data' => $purchasesSeriesData],
            ['name' => 'Sales', 'data' => $salesSeriesData],
        ];

        // 2. ConversionsWidget (Company Sales Cards)
        $colorThemes = ['success', 'secondary', 'error'];
        $companyWidgets = [];

        foreach ($companies as $idx => $company) {
            $companySalesContracts = $contracts->filter(function ($c) use ($company) {
                return (int) $c->salesCompany === (int) $company->id;
            });

            $totalAmount = 0.0;
            $sparklineData = [];

            foreach ($companySalesContracts as $c) {
                $cTotal = 0.0;
                if ($c->sale && $c->sale->products) {
                    foreach ($c->sale->products as $p) {
                        $itemTotal = (float) ($p->total_price ?: ($p->total ?: ($p->quantity * ($p->unit_price ?: ($p->rate ?: 0)))));
                        $cTotal += $itemTotal;
                    }
                }
                $totalAmount += $cTotal;

                $d = $c->due_date ?: $c->loading_date;
                $ts = $d ? $d->timestamp * 1000 : $now->timestamp * 1000;
                $sparklineData[] = [
                    'x' => $ts,
                    'y' => round($cTotal, 2),
                ];
            }

            // Sort sparkline by date
            usort($sparklineData, fn($a, $b) => $a['x'] <=> $b['x']);

            $companyWidgets[] = [
                'id' => $company->id,
                'name' => $company->name,
                'amount' => $totalAmount,
                'themeColor' => $colorThemes[$idx % 3],
                'sparklineSeries' => [
                    ['data' => $sparklineData],
                ],
            ];
        }

        // 3. Contracts per stage (NewVsReturningWidget)
        $firstModel = $models->first();
        $stages = $firstModel ? $firstModel->stages : collect();
        $companyLabels = $companies->pluck('name')->values()->all();
        $stageWidgets = [];
        $donutColors = ['#3182CE', '#63B3ED', '#483FF2', '#3F6FF2', '#833FF2', '#F2D53F', '#736E57', '#F2CA3F'];

        foreach ($stages as $stage) {
            $stageContracts = $stage->contracts ?: collect();
            $counts = [];
            $totalInStage = 0;

            foreach ($companies as $c) {
                $num = $stageContracts->filter(function ($con) use ($c) {
                    return (int) $con->salesCompany === (int) $c->id || (int) $con->purchaseCompany === (int) $c->id;
                })->count();

                $counts[] = $num;
                $totalInStage += $num;
            }

            $percentages = [];
            if ($totalInStage === 0) {
                $percentages = array_fill(0, count($counts), 0);
            } else {
                foreach ($counts as $count) {
                    $percentages[] = (int) round(($count / $totalInStage) * 100);
                }
            }

            // Build breakdown items for the widget table
            $breakdown = [];
            foreach ($companies as $cIdx => $comp) {
                $color = $donutColors[$cIdx % count($donutColors)];
                $pct = $percentages[$cIdx] ?? 0;
                $calcCount = (int) round(($totalInStage * $pct) / 100);

                $breakdown[] = [
                    'label' => $comp->name,
                    'color' => $color,
                    'percentage' => $pct,
                    'count' => $calcCount,
                ];
            }

            $stageWidgets[] = [
                'id' => $stage->id,
                'title' => $stage->description ?: $stage->name,
                'total' => $totalInStage,
                'series' => $percentages,
                'labels' => $companyLabels,
                'colors' => array_slice($donutColors, 0, max(count($companyLabels), 1)),
                'breakdown' => $breakdown,
            ];
        }

        return [
            'overviewSeries' => $overviewSeries,
            'companyWidgets' => $companyWidgets,
            'stageWidgets' => $stageWidgets,
            'contracts' => $contracts,
            'companies' => $companies,
            'models' => $models,
        ];
    }
}
