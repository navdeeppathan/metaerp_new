<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Models\Contact;
use App\Models\Company;
use Illuminate\Http\Request;

class SearchController extends BaseApiController
{
    public function search(Request $request, $text)
    {
        $contracts = Contract::with(['purchase.contact', 'sale.contact'])
            ->where('order_code', 'like', "%{$text}%")
            ->limit(10)
            ->get();

        $contacts = Contact::where('name', 'like', "%{$text}%")
            ->orWhere('code_meta', 'like', "%{$text}%")
            ->limit(10)
            ->get();

        $companies = Company::where('name', 'like', "%{$text}%")
            ->orWhere('code', 'like', "%{$text}%")
            ->limit(10)
            ->get();

        return response()->json([
            'contracts' => $contracts,
            'contacts' => $contacts,
            'companies' => $companies,
        ]);
    }
}
