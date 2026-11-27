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

        // Allow dates to be optional so contracts across all periods (e.g. historical closed/cancelled) can be found
        $fromDateRaw = $request->input('from_date', $request->input('startDate', ''));
        $toDateRaw = $request->input('to_date', $request->input('endDate', ''));

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

        $query = Contract::with([
            'stage',
            'purchase.contact',
            'purchase.metaCompany',
            'sale.contact',
            'sale.metaCompany'
        ]);

        if ($request->filled('q') || $request->filled('search')) {
            $searchTerm = $request->input('q', $request->input('search'));
            $query->where(function($sub) use ($searchTerm) {
                $sub->where('order_code', 'like', "%{$searchTerm}%")
                    ->orWhere('orderCode', 'like', "%{$searchTerm}%")
                    ->orWhere('customer_purchase_order', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('orderDescription', 'like', "%{$searchTerm}%")
                    ->orWhereHas('purchase.contact', fn($c) => $c->where('name', 'like', "%{$searchTerm}%"))
                    ->orWhereHas('sale.contact', fn($c) => $c->where('name', 'like', "%{$searchTerm}%"));
            });
        }

        if ($status && strtoupper($status) !== 'ALL') {
            $st = strtoupper($status);
            if ($st === 'CLOSED') {
                $query->whereIn('status', ['CLOSED', 'STORED']);
            } elseif ($st === 'CANCELLED') {
                $query->where('status', 'CANCELLED');
            } elseif ($st === 'OPEN') {
                $query->where(function($sq) {
                    $sq->whereIn('status', ['OPEN', 'ACTIVE'])->orWhereNull('status');
                });
            } else {
                $query->where('status', $st);
            }
        }

        if ($type && strtoupper($type) !== 'ALL') {
            $query->where('type', strtoupper($type));
        }

        if ($fromDate) {
            $query->where(function($dq) use ($fromDate) {
                $dq->where('due_date', '>=', $fromDate)
                   ->orWhere('date', '>=', $fromDate);
            });
        }

        if ($toDate) {
            $query->where(function($dq) use ($toDate) {
                $dq->where('due_date', '<=', $toDate)
                   ->orWhere('date', '<=', $toDate);
            });
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
