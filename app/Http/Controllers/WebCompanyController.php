<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Country;
use App\Models\Address;
use App\Models\BankAccount;
use App\Models\Representative;
use Illuminate\Http\Request;

class WebCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::where('active', true);

        $search = $request->input('q') ?? $request->input('search');
        if (!empty($search)) {
            $query->where(function($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('vatNumber', 'like', "%{$search}%")
                    ->orWhere('companyNumber', 'like', "%{$search}%")
                    ->orWhere('phoneNumber', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $companies = $query->orderBy('name')->paginate($perPage)->withQueryString();
        $countries = Country::where('active', true)->orderBy('name')->get();
        return view('companies.index', compact('companies', 'countries'));
    }

    public function create()
    {
        $company = new Company();
        $countries = Country::where('active', true)->orderBy('name')->get();
        return view('companies.create', compact('company', 'countries'));
    }

    public function show($id)
    {
        $company = Company::with(['address', 'bankAccounts'])->findOrFail($id);
        $countries = Country::where('active', true)->orderBy('name')->get();
        return view('companies.create', compact('company', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:64',
            'companyNumber' => 'nullable|string',
            'vatNumber' => 'nullable|string',
            'eoriNumber' => 'nullable|string',
            'phoneNumber' => 'nullable|string',
            'faxNumber' => 'nullable|string',
            'email' => 'nullable|email',
            'domain' => 'nullable|string',
            'webSite' => 'nullable|string',
            'countryId' => 'nullable|exists:countries,id',
        ]);

        $company = Company::create(array_merge($validated, ['active' => 1]));

        // Address handling
        if ($request->filled('addressName') || $request->filled('addressLine1') || $request->filled('city')) {
            $addr = Address::create([
                'addressName' => $request->input('addressName', 'Main Office'),
                'addressLine1' => $request->input('addressLine1'),
                'addressLine2' => $request->input('addressLine2'),
                'state' => $request->input('state'),
                'city' => $request->input('city'),
                'postalCode' => $request->input('postalCode'),
                'postalBox' => $request->input('postalBox'),
                'forGoogleMapsSearch' => $request->input('forGoogleMapsSearch'),
                'country_id' => $validated['countryId'] ?? null,
                'company_id' => $company->id,
            ]);
            $company->address_id = $addr->id;
            $company->save();
        }

        // Bank Accounts array handling
        if ($request->has('bank_accounts') && is_array($request->input('bank_accounts'))) {
            foreach ($request->input('bank_accounts') as $accountData) {
                if (!empty($accountData['name']) || !empty($accountData['accountNumber']) || !empty($accountData['iban']) || !empty($accountData['description'])) {
                    BankAccount::create([
                        'company_id' => $company->id,
                        'description' => $accountData['description'] ?? null,
                        'name' => $accountData['name'] ?? 'Primary Account',
                        'sortCode' => $accountData['sortCode'] ?? null,
                        'accountNumber' => $accountData['accountNumber'] ?? null,
                        'iban' => $accountData['iban'] ?? null,
                        'swift' => $accountData['swift'] ?? null,
                    ]);
                }
            }
        } elseif ($request->filled('bank_name') || $request->filled('accountNumber') || $request->filled('iban')) {
            BankAccount::create([
                'company_id' => $company->id,
                'description' => $request->input('description'),
                'name' => $request->input('bank_name', 'Primary Account'),
                'sortCode' => $request->input('sortCode'),
                'accountNumber' => $request->input('accountNumber'),
                'iban' => $request->input('iban'),
                'swift' => $request->input('swift'),
            ]);
        }

        return redirect()->route('companies.show', $company->id)->with('success', "Company {$company->name} created successfully!");
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $company->update($request->only([
            'name', 'code', 'companyNumber', 'vatNumber', 'eoriNumber',
            'phoneNumber', 'faxNumber', 'email', 'domain', 'webSite', 'countryId'
        ]));

        // Address handling
        if ($company->address) {
            $company->address->update($request->only([
                'addressName', 'addressLine1', 'addressLine2', 'state', 'city',
                'postalCode', 'postalBox', 'forGoogleMapsSearch'
            ]));
            if ($request->filled('countryId')) {
                $company->address->country_id = $request->input('countryId');
                $company->address->save();
            }
        } elseif ($request->filled('addressName') || $request->filled('addressLine1') || $request->filled('city')) {
            $addr = Address::create([
                'addressName' => $request->input('addressName', 'Main Office'),
                'addressLine1' => $request->input('addressLine1'),
                'addressLine2' => $request->input('addressLine2'),
                'state' => $request->input('state'),
                'city' => $request->input('city'),
                'postalCode' => $request->input('postalCode'),
                'postalBox' => $request->input('postalBox'),
                'forGoogleMapsSearch' => $request->input('forGoogleMapsSearch'),
                'country_id' => $request->input('countryId'),
                'company_id' => $company->id,
            ]);
            $company->address_id = $addr->id;
            $company->save();
        }

        // Bank Accounts array handling
        if ($request->has('bank_accounts') && is_array($request->input('bank_accounts'))) {
            foreach ($request->input('bank_accounts') as $accountData) {
                if (!empty($accountData['name']) || !empty($accountData['accountNumber']) || !empty($accountData['iban']) || !empty($accountData['description'])) {
                    if (!empty($accountData['id'])) {
                        $ba = BankAccount::where('id', $accountData['id'])->where('company_id', $company->id)->first();
                        if ($ba) {
                            $ba->update([
                                'description' => $accountData['description'] ?? null,
                                'name' => $accountData['name'] ?? null,
                                'sortCode' => $accountData['sortCode'] ?? null,
                                'accountNumber' => $accountData['accountNumber'] ?? null,
                                'iban' => $accountData['iban'] ?? null,
                                'swift' => $accountData['swift'] ?? null,
                            ]);
                            continue;
                        }
                    }
                    BankAccount::create([
                        'company_id' => $company->id,
                        'description' => $accountData['description'] ?? null,
                        'name' => $accountData['name'] ?? 'Primary Account',
                        'sortCode' => $accountData['sortCode'] ?? null,
                        'accountNumber' => $accountData['accountNumber'] ?? null,
                        'iban' => $accountData['iban'] ?? null,
                        'swift' => $accountData['swift'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('companies.show', $company->id)->with('success', 'Company details updated successfully!');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->update(['active' => 0]);
        return redirect()->route('companies.index')->with('success', 'Company removed successfully!');
    }

    public function uploadMedia(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $type = $request->input('type'); // logo, footer, stamp

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store("companies/{$type}", 'public');
            $url = '/storage/' . $path;

            if ($type === 'logo') {
                $company->logoUrl = $url;
            } elseif ($type === 'footer') {
                $company->footer = $url;
            }
            $company->save();

            return back()->with('success', ucfirst($type) . ' uploaded successfully!');
        }

        return back()->with('error', 'No file was uploaded');
    }
}
