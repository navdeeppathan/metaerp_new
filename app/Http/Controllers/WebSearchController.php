<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WebSearchController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'OPEN');
        $type = $request->input('type', 'ALL');

        // Default date range: 60 days before today to today (matches screenshot: 19/09/2026 - 18/11/2026)
        $defaultFrom = Carbon::now()->subDays(60)->format('d/m/Y');
        $defaultTo = Carbon::now()->format('d/m/Y');

        $fromDateRaw = $request->input('from_date', $request->input('startDate', $defaultFrom));
        $toDateRaw = $request->input('to_date', $request->input('endDate', $defaultTo));

        $fromDate = null;
        $toDate = null;

        if (!empty($fromDateRaw)) {
            try {
                $fromDate = Carbon::createFromFormat('d/m/Y', trim($fromDateRaw))->startOfDay();
            } catch (\Throwable $e) {
                try {
                    $fromDate = Carbon::parse($fromDateRaw)->startOfDay();
                } catch (\Throwable $e2) {}
            }
        }

        if (!empty($toDateRaw)) {
            try {
                $toDate = Carbon::createFromFormat('d/m/Y', trim($toDateRaw))->endOfDay();
            } catch (\Throwable $e) {
                try {
                    $toDate = Carbon::parse($toDateRaw)->endOfDay();
                } catch (\Throwable $e2) {}
            }
        }

        $hasDateFilter = $request->has('from_date') || $request->has('startDate') || $request->has('to_date') || $request->has('endDate');
        if (!$hasDateFilter && ($request->filled('q') || $request->filled('search'))) {
            $fromDate = null;
            $toDate = null;
        }

        $query = Contract::with([
            'stage',
            'purchase.contact',
            'purchase.metaCompany',
            'sale.contact',
            'sale.metaCompany'
        ])->where(function($q) {
            $q->where('active', true)->orWhereNull('active');
        });

        if ($request->filled('q') || $request->filled('search')) {
            $searchTerm = $request->input('q', $request->input('search'));
            $query->where(function($sub) use ($searchTerm) {
                $sub->where('order_code', 'like', "%{$searchTerm}%")
                    ->orWhere('customer_purchase_order', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('purchase.contact', fn($c) => $c->where('name', 'like', "%{$searchTerm}%"))
                    ->orWhereHas('sale.contact', fn($c) => $c->where('name', 'like', "%{$searchTerm}%"));
            });
        }

        if ($status && strtoupper($status) !== 'ALL') {
            $query->where('status', strtoupper($status));
        }

        if ($type && strtoupper($type) !== 'ALL') {
            $query->where('type', strtoupper($type));
        }

        if ($fromDate) {
            $query->where('due_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('due_date', '<=', $toDate);
        }

        $perPage = (int) ($request->input('per_page') ?? $request->input('perPage') ?? $request->input('rowsPerPage') ?? $request->input('size') ?? 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        // Ordered by due_date desc as shown in standard view
        $contracts = $query->orderBy('due_date', 'desc')->paginate($perPage)->withQueryString();
        $companies = Company::where('active', true)->get();
        $contacts = Contact::where('active', true)->get();

        return view('contracts.search', compact(
            'contracts', 'companies', 'contacts', 'status', 'type',
            'fromDateRaw', 'toDateRaw', 'perPage'
        ));
    }
}
