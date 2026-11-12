<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\Request;

class WebSearchController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'OPEN');
        $type = $request->input('type', 'ALL');
        $fromDate = $request->input('from_date', '2026-09-05');
        $toDate = $request->input('to_date', '2026-11-04');

        $query = Contract::with(['stage', 'purchase.contact', 'purchase.metaCompany', 'sale.contact', 'sale.metaCompany'])
            ->where('active', true);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->where('order_code', 'like', "%{$q}%")
                    ->orWhere('customer_purchase_order', 'like', "%{$q}%")
                    ->orWhereHas('purchase.contact', fn($c) => $c->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('sale.contact', fn($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($type && $type !== 'ALL') {
            $query->where('type', $type);
        }

        if ($fromDate) {
            $query->whereDate('due_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('due_date', '<=', $toDate);
        }

        $contracts = $query->orderBy('order_code', 'asc')->paginate(10);
        $companies = Company::where('active', true)->get();
        $contacts = Contact::where('active', true)->get();

        return view('contracts.search', compact('contracts', 'companies', 'contacts', 'status', 'type', 'fromDate', 'toDate'));
    }
}
