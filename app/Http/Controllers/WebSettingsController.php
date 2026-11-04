<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Department;
use App\Models\PackType;
use App\Models\PaymentType;
use App\Models\PaymentTermsType;
use App\Models\CreditDebitType;
use Illuminate\Http\Request;

class WebSettingsController extends Controller
{
    public function countries()
    {
        $countries = Country::paginate(20);
        return view('settings.countries', ['items' => $countries, 'countries' => $countries]);
    }

    public function currencies()
    {
        $currencies = Currency::all();
        return view('settings.currencies', ['items' => $currencies, 'currencies' => $currencies]);
    }

    public function departments()
    {
        $departments = Department::all();
        return view('settings.departments', ['items' => $departments, 'departments' => $departments]);
    }

    public function packTypes()
    {
        $packTypes = PackType::all();
        return view('settings.pack-types', ['items' => $packTypes, 'packTypes' => $packTypes]);
    }

    public function paymentTypes()
    {
        $paymentTypes = PaymentType::all();
        return view('settings.payment-types', ['items' => $paymentTypes, 'paymentTypes' => $paymentTypes]);
    }

    public function paymentTerms()
    {
        $paymentTerms = PaymentTermsType::all();
        return view('settings.payment-terms', ['items' => $paymentTerms, 'paymentTerms' => $paymentTerms]);
    }
}
