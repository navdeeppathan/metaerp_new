<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Product;
use App\Models\Offer;
use Illuminate\Http\Request;

class SearchController extends BaseApiController
{
    public function search(Request $request, $text)
    {
        $text = trim($text);
        if (empty($text)) {
            return response()->json([
                'contracts' => [],
                'contacts' => [],
                'companies' => [],
                'products' => [],
                'offers' => [],
            ]);
        }

        $contracts = Contract::with(['purchase.contact', 'sale.contact'])
            ->where(function($q) use ($text) {
                $q->where('order_code', 'like', "%{$text}%")
                  ->orWhere('description', 'like', "%{$text}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'orderCode' => $c->order_code,
                    'order_code' => $c->order_code,
                    'description' => $c->description,
                    'type' => $c->type,
                    'status' => $c->status,
                    'url' => url('/findContracts/' . $c->id),
                ];
            });

        $contacts = Contact::where('name', 'like', "%{$text}%")
            ->orWhere('code_meta', 'like', "%{$text}%")
            ->orWhere('code', 'like', "%{$text}%")
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'codeMeta' => $c->code_meta ?? $c->code ?? '',
                    'code_meta' => $c->code_meta ?? $c->code ?? '',
                    'url' => url('/contacts/' . $c->id),
                ];
            });

        $companies = Company::where('name', 'like', "%{$text}%")
            ->orWhere('code', 'like', "%{$text}%")
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'code' => $c->code ?? '',
                    'url' => url('/companies/' . $c->id),
                ];
            });

        $products = Product::where('name', 'like', "%{$text}%")
            ->orWhere('code', 'like', "%{$text}%")
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code ?? '',
                    'url' => url('/products/' . $p->id),
                ];
            });

        $offers = Offer::where('order_code', 'like', "%{$text}%")
            ->orWhere('subject', 'like', "%{$text}%")
            ->limit(10)
            ->get()
            ->map(function ($o) {
                return [
                    'id' => $o->id,
                    'orderCode' => $o->order_code,
                    'order_code' => $o->order_code,
                    'subject' => $o->subject ?? '',
                    'url' => url('/offers/' . $o->id),
                ];
            });

        return response()->json([
            'contracts' => $contracts,
            'contacts' => $contacts,
            'companies' => $companies,
            'products' => $products,
            'offers' => $offers,
        ]);
    }
}
