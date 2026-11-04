<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Contact;
use App\Models\Company;
use App\Services\MetalPriceService;

class HomeController extends Controller
{
    public function index(MetalPriceService $metalPriceService)
    {
        $activeContractsCount = Contract::where('active', true)->where('contractStatus', 'ACTIVE')->count();
        $openOffersCount = Offer::where('status', 'OPEN')->count();
        $productsCount = Product::where('active', true)->count();
        $contactsCount = Contact::where('active', true)->count();
        $companiesCount = Company::where('active', true)->count();

        $recentContracts = Contract::with(['stage', 'purchase.contact', 'sale.contact'])
            ->where('active', true)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $metalPrices = $metalPriceService->getPrices();

        return view('home', compact(
            'activeContractsCount',
            'openOffersCount',
            'productsCount',
            'contactsCount',
            'companiesCount',
            'recentContracts',
            'metalPrices'
        ));
    }
}
