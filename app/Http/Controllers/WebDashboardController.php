<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ModelWorkflow;
use App\Models\Stage;
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

    public function dashboardV2(Request $request)
    {
        @ini_set('memory_limit', '512M');
        $data = $this->buildDashboardV2Data($request);

        return view('dashboards.v2', $data);
    }

    public function apiDashboardV2Data(Request $request)
    {
        @ini_set('memory_limit', '512M');
        return response()->json($this->buildDashboardV2Data($request));
    }

    private function buildDashboardV2Data(Request $request): array
    {
        @ini_set('memory_limit', '512M');

        $user = $request->user();
        $isSuperAdmin = $user ? $user->isSuperAdmin() : false;

        // Role-based visibility flags
        $canViewFinancials = $isSuperAdmin || ($user && ($user->hasPermission(['FINANCE', 'ACCOUNTING'], 'READ') || $user->hasPermission('DASHBOARD', 'FULL')));
        $canViewContracts = $isSuperAdmin || ($user && $user->hasPermission('CONTRACT', 'READ'));
        $canViewLogistics = $isSuperAdmin || ($user && ($user->hasPermission(['LOGISTICS', 'PACKING'], 'READ') || $user->hasPermission('CONTRACT', 'READ')));
        $canViewProfit = $canViewFinancials; // Hide profit/margins from pure operations/sales if not permitted

        // Accessible company scope
        $accessibleCompanyIds = $user ? $user->getAccessibleCompanies('CONTRACT') : [];
        $companiesQuery = Company::where('active', true)->orderBy('name');
        if (!$isSuperAdmin && !empty($accessibleCompanyIds)) {
            $companiesQuery->whereIn('id', $accessibleCompanyIds);
        }
        $availableCompanies = $companiesQuery->get(['id', 'name']);

        // Stages
        $allStages = Stage::where('active', true)->orderBy('position', 'asc')->get(['id', 'name', 'description', 'position']);

        // Selected filters from request
        $selectedCompanyId = $request->filled('company_id') && $request->company_id !== 'all' ? (int) $request->company_id : null;
        $selectedStageId = $request->filled('stage_id') && $request->stage_id !== 'all' ? (int) $request->stage_id : null;
        $selectedCurrency = $request->filled('currency') && $request->currency !== 'all' ? trim((string) $request->currency) : null;
        
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(180)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->addDays(30)->endOfDay();

        // Base contracts query with selected columns to optimize memory
        $contractsQuery = Contract::query()
            ->where('active', true)
            ->select([
                'id',
                'order_code',
                'orderCode',
                'due_date',
                'loading_date',
                'loadingDate',
                'sales_invoice_date',
                'stage_id',
                'purchase_id',
                'sale_id',
                'active',
                'currency',
                'transport_price_agreed',
                'mode_of_transport',
            ])
            ->with([
                'stage:id,description,name,position',
                'purchase' => function ($q) {
                    $q->select(['id', 'contact_id', 'meta_company_id']);
                },
                'purchase.contact:id,name',
                'purchase.products' => function ($q) {
                    $q->select(['id', 'deal_id', 'total_price', 'total', 'quantity', 'unit_price', 'rate', 'net_weight']);
                },
                'sale' => function ($q) {
                    $q->select(['id', 'contact_id', 'meta_company_id']);
                },
                'sale.contact:id,name',
                'sale.products' => function ($q) {
                    $q->select(['id', 'deal_id', 'total_price', 'total', 'quantity', 'unit_price', 'rate', 'net_weight']);
                },
            ]);

        // Company permission scoping via relationships in SQL
        if (!$isSuperAdmin && !empty($accessibleCompanyIds)) {
            $contractsQuery->where(function ($q) use ($accessibleCompanyIds) {
                $q->whereHas('sale', function ($sq) use ($accessibleCompanyIds) {
                    $sq->whereIn('meta_company_id', $accessibleCompanyIds);
                })->orWhereHas('purchase', function ($pq) use ($accessibleCompanyIds) {
                    $pq->whereIn('meta_company_id', $accessibleCompanyIds);
                });
            });
        }

        // Apply request company filter
        if ($selectedCompanyId) {
            $contractsQuery->where(function ($q) use ($selectedCompanyId) {
                $q->whereHas('sale', function ($sq) use ($selectedCompanyId) {
                    $sq->where('meta_company_id', $selectedCompanyId);
                })->orWhereHas('purchase', function ($pq) use ($selectedCompanyId) {
                    $pq->where('meta_company_id', $selectedCompanyId);
                });
            });
        }

        if ($selectedStageId) {
            $contractsQuery->where('stage_id', $selectedStageId);
        }

        if ($selectedCurrency) {
            $contractsQuery->where('currency', $selectedCurrency);
        }

        // Apply date range filter in SQL
        if ($startDate && $endDate) {
            $contractsQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('due_date', [$startDate, $endDate])
                  ->orWhereBetween('loading_date', [$startDate, $endDate])
                  ->orWhere(function ($sub) {
                      $sub->whereNull('due_date')->whereNull('loading_date');
                  });
            });
        }

        // Bound to the most recent 1,000 contracts to guarantee zero OOM
        $allContracts = $contractsQuery->orderBy('id', 'desc')->limit(1000)->get();

        // Apply date filtering in PHP if specified
        if ($startDate || $endDate) {
            $allContracts = $allContracts->filter(function ($c) use ($startDate, $endDate) {
                $d = $c->due_date ?: ($c->loading_date ?: $c->sales_invoice_date);
                if (!$d) {
                    return true;
                }
                if ($startDate && $d->lt($startDate)) {
                    return false;
                }
                if ($endDate && $d->gt($endDate)) {
                    return false;
                }
                return true;
            });
        }

        // Aggregate core numbers
        $totalSales = 0.0;
        $totalPurchases = 0.0;
        $totalTransport = 0.0;
        $totalTonnage = 0.0;
        $inTransitCount = 0;
        $closedCount = 0;
        $contractPoints = [];
        $companyTotals = [];
        $monthlyData = [];
        $topBuyers = [];
        $topSuppliers = [];

        // Map company IDs to names in memory to avoid N+1 queries
        $companiesMap = Company::where('active', true)->pluck('name', 'id')->toArray();

        foreach ($allContracts as $c) {
            $cSaleTotal = 0.0;
            $cPurchaseTotal = 0.0;
            $cTonnage = 0.0;

            if ($c->sale && $c->sale->products) {
                foreach ($c->sale->products as $p) {
                    $itemTotal = (float) ($p->total_price ?: ($p->total ?: ($p->quantity * ($p->unit_price ?: ($p->rate ?: 0)))));
                    $cSaleTotal += $itemTotal;
                    $cTonnage += (float) ($p->quantity ?: ($p->net_weight ?: 0));
                }
            }

            if ($c->purchase && $c->purchase->products) {
                foreach ($c->purchase->products as $p) {
                    $itemTotal = (float) ($p->total_price ?: ($p->total ?: ($p->quantity * ($p->unit_price ?: ($p->rate ?: 0)))));
                    $cPurchaseTotal += $itemTotal;
                }
            }

            $transportCost = (float) ($c->transport_price_agreed ?: 0);
            $totalTransport += $transportCost;
            $totalSales += $cSaleTotal;
            $totalPurchases += $cPurchaseTotal;
            $totalTonnage += $cTonnage;

            $stageName = strtolower(trim((string) ($c->stage?->description ?: $c->stage?->name ?: '')));
            if (str_contains($stageName, 'transit') || str_contains($stageName, 'load') || str_contains($stageName, 'ship')) {
                $inTransitCount++;
            }
            if (str_contains($stageName, 'close') || str_contains($stageName, 'complete') || str_contains($stageName, 'invoic')) {
                $closedCount++;
            }

            // Group by company using in-memory map
            $saleCompId = $c->sale?->meta_company_id;
            $purchCompId = $c->purchase?->meta_company_id;
            $compName = $companiesMap[$saleCompId] ?? ($companiesMap[$purchCompId] ?? 'Global Trading');
            if (!isset($companyTotals[$compName])) {
                $companyTotals[$compName] = [
                    'name' => $compName,
                    'sales' => 0.0,
                    'purchases' => 0.0,
                    'contracts_count' => 0,
                ];
            }
            $companyTotals[$compName]['sales'] += $cSaleTotal;
            $companyTotals[$compName]['purchases'] += $cPurchaseTotal;
            $companyTotals[$compName]['contracts_count']++;

            // Monthly aggregation
            $d = $c->due_date ?: ($c->loading_date ?: Carbon::now());
            $monthKey = $d->format('Y-m');
            $monthLabel = $d->format('M Y');
            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'label' => $monthLabel,
                    'sort' => $d->startOfMonth()->timestamp,
                    'sales' => 0.0,
                    'purchases' => 0.0,
                    'cpc' => 0.0, // average rate/unit
                    'weight' => 0.0,
                ];
            }
            $monthlyData[$monthKey]['sales'] += $cSaleTotal;
            $monthlyData[$monthKey]['purchases'] += $cPurchaseTotal;
            $monthlyData[$monthKey]['weight'] += $cTonnage;

            // Top Buyers
            if ($c->sale?->contact?->name) {
                $buyerName = $c->sale->contact->name;
                $topBuyers[$buyerName] = ($topBuyers[$buyerName] ?? 0.0) + $cSaleTotal;
            }

            // Top Suppliers
            if ($c->purchase?->contact?->name) {
                $suppName = $c->purchase->contact->name;
                $topSuppliers[$suppName] = ($topSuppliers[$suppName] ?? 0.0) + $cPurchaseTotal;
            }

            // Scatter / Bubble Plot item: [Days to Due, Contract Total, Margin Pct]
            $daysToDue = $c->due_date ? max(1, Carbon::now()->diffInDays($c->due_date, false)) : 30;
            $cMargin = $cSaleTotal - $cPurchaseTotal;
            $cMarginPct = $cSaleTotal > 0 ? round(($cMargin / $cSaleTotal) * 100, 1) : 10;
            $contractPoints[] = [
                'x' => $daysToDue,
                'y' => round($cSaleTotal, 2),
                'z' => max(5, min(50, abs($cMarginPct))),
                'name' => $c->order_code ?: ('Contract #' . $c->id),
            ];
        }

        // Sort monthly buckets
        uasort($monthlyData, fn($a, $b) => $a['sort'] <=> $b['sort']);
        // Keep last 6-12 months
        $monthlySlice = array_slice($monthlyData, -8, 8, true);

        $stackedCategories = [];
        $stackedSales = [];
        $stackedPurchases = [];
        $weeklyCpcSeries = [];

        foreach ($monthlySlice as $m) {
            $stackedCategories[] = $m['label'];
            $stackedSales[] = round($m['sales'], 2);
            $stackedPurchases[] = round($m['purchases'], 2);
            $avgUnitCost = $m['weight'] > 0 ? round($m['purchases'] / $m['weight'], 2) : 0;
            $avgUnitSale = $m['weight'] > 0 ? round($m['sales'] / $m['weight'], 2) : 0;
            $weeklyCpcSeries['cost'][] = $avgUnitCost;
            $weeklyCpcSeries['price'][] = $avgUnitSale;
        }

        // Derived summary statistics
        $grossProfit = $totalSales - $totalPurchases;
        $profitMarginPct = $totalSales > 0 ? round(($grossProfit / $totalSales) * 100, 2) : 0.0;
        $totalContractsCount = $allContracts->count();
        $avgContractValue = $totalContractsCount > 0 ? round($totalSales / $totalContractsCount, 2) : 0.0;
        $fulfillmentRatePct = $totalContractsCount > 0 ? round(($closedCount / $totalContractsCount) * 100, 1) : 0.0;
        $roasRatio = $totalPurchases > 0 ? round(($totalSales / $totalPurchases) * 100, 2) : 100.0;

        // Top 5 Buyers and Suppliers
        arsort($topBuyers);
        arsort($topSuppliers);
        $topBuyersList = array_slice($topBuyers, 0, 5, true);
        $topSuppliersList = array_slice($topSuppliers, 0, 5, true);

        // Donut Chart: Company distribution
        $donutLabels = [];
        $donutSeries = [];
        foreach ($companyTotals as $cName => $cData) {
            $donutLabels[] = $cName;
            $donutSeries[] = round($cData['sales'], 2);
        }
        if (empty($donutSeries)) {
            $donutLabels = ['Standard Trading'];
            $donutSeries = [100];
        }

        // Heatmap Matrix Breakdown Table (Companies)
        $companyMatrix = [];
        $maxSales = max(1, max(array_column($companyTotals, 'sales') ?: [1]));
        foreach ($companyTotals as $cData) {
            $cProfit = $cData['sales'] - $cData['purchases'];
            $cMargin = $cData['sales'] > 0 ? round(($cProfit / $cData['sales']) * 100, 1) : 0;
            $heatPct = min(100, round(($cData['sales'] / $maxSales) * 100));
            $companyMatrix[] = [
                'name' => $cData['name'],
                'contracts' => $cData['contracts_count'],
                'sales' => $cData['sales'],
                'purchases' => $cData['purchases'],
                'profit' => $cProfit,
                'margin_pct' => $cMargin,
                'heat_pct' => $heatPct,
            ];
        }

        // Heatmap Matrix Breakdown Table (Stages)
        $stageMatrix = [];
        foreach ($allStages as $stage) {
            $sContracts = $allContracts->filter(fn($c) => (int) $c->stage_id === (int) $stage->id);
            $sCount = $sContracts->count();
            $sVal = 0.0;
            foreach ($sContracts as $sc) {
                if ($sc->sale && $sc->sale->products) {
                    foreach ($sc->sale->products as $p) {
                        $sVal += (float) ($p->total_price ?: ($p->total ?: ($p->quantity * ($p->unit_price ?: ($p->rate ?: 0)))));
                    }
                }
            }
            $stageMatrix[] = [
                'id' => $stage->id,
                'title' => $stage->description ?: $stage->name,
                'count' => $sCount,
                'value' => $sVal,
                'pct' => $totalContractsCount > 0 ? round(($sCount / $totalContractsCount) * 100, 1) : 0,
            ];
        }

        // Target Fulfillment Gauge (Target benchmark $1,500,000 or dynamic)
        $targetRevenue = max(100000.0, round($totalSales * 1.25, -4));
        $targetGaugePct = min(100, round(($totalSales / $targetRevenue) * 100, 1));

        return [
            // Permission Flags
            'canViewFinancials' => $canViewFinancials,
            'canViewContracts' => $canViewContracts,
            'canViewLogistics' => $canViewLogistics,
            'canViewProfit' => $canViewProfit,
            'isSuperAdmin' => $isSuperAdmin,

            // Filter Options & State
            'availableCompanies' => $availableCompanies,
            'allStages' => $allStages,
            'selectedCompanyId' => $selectedCompanyId,
            'selectedStageId' => $selectedStageId,
            'selectedCurrency' => $selectedCurrency,
            'startDate' => $startDate?->format('Y-m-d'),
            'endDate' => $endDate?->format('Y-m-d'),

            // 12 Vibrant Coupler KPI Cards
            'kpis' => [
                'total_contracts' => $totalContractsCount,
                'active_deals' => $allContracts->pluck('purchase_id')->merge($allContracts->pluck('sale_id'))->filter()->unique()->count(),
                'total_sales' => $totalSales,
                'total_purchases' => $totalPurchases,
                'gross_profit' => $grossProfit,
                'profit_margin_pct' => $profitMarginPct,
                'total_tonnage' => $totalTonnage,
                'in_transit_count' => $inTransitCount,
                'closed_count' => $closedCount,
                'fulfillment_rate_pct' => $fulfillmentRatePct,
                'avg_contract_value' => $avgContractValue,
                'roas_ratio' => $roasRatio,
            ],

            // Charts Data
            'donut' => [
                'labels' => $donutLabels,
                'series' => $donutSeries,
            ],
            'stacked' => [
                'categories' => $stackedCategories,
                'sales' => $stackedSales,
                'purchases' => $stackedPurchases,
            ],
            'unitRates' => [
                'categories' => $stackedCategories,
                'cost' => $weeklyCpcSeries['cost'] ?? [],
                'price' => $weeklyCpcSeries['price'] ?? [],
            ],
            'gauge' => [
                'current' => $totalSales,
                'target' => $targetRevenue,
                'percentage' => $targetGaugePct,
            ],
            'horizontalPartners' => [
                'buyers' => $topBuyersList,
                'suppliers' => $topSuppliersList,
            ],
            'contractPoints' => array_slice($contractPoints, 0, 30),
            'companyMatrix' => $companyMatrix,
            'stageMatrix' => $stageMatrix,
        ];
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
                $comp = (int) ($c->sale?->meta_company_id ?? $c->salesCompany);
                return $comp === (int) $company->id;
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
                    $saleComp = (int) ($con->sale?->meta_company_id ?? $con->salesCompany);
                    $purchComp = (int) ($con->purchase?->meta_company_id ?? $con->purchaseCompany);
                    return $saleComp === (int) $c->id || $purchComp === (int) $c->id;
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
