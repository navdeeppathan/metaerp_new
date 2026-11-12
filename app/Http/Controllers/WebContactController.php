<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Models\Country;
use App\Models\Type;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Address;
use App\Models\Representative;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class WebContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with(['country', 'metaCompany', 'types'])->where('active', true);

        $search = $request->input('q') ?? $request->input('search');
        if (!empty($search)) {
            $query->where(function($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('code_meta', 'like', "%{$search}%")
                    ->orWhere('vat', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $contacts = $query->orderBy('name')->paginate($perPage)->withQueryString();
        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        $contact = new Contact();
        $companies = Company::where('active', true)->orderBy('name')->get();
        $countries = Country::where('active', true)->orderBy('name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        return view('contacts.create', compact('contact', 'companies', 'countries', 'products'));
    }

    public function show($id)
    {
        $contact = Contact::with(['country', 'metaCompany', 'addresses.representatives', 'products'])->findOrFail($id);
        $companies = Company::where('active', true)->orderBy('name')->get();
        $countries = Country::where('active', true)->orderBy('name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        return view('contacts.create', compact('contact', 'companies', 'countries', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'companyId' => 'nullable|exists:companies,id',
            'types' => 'nullable|string',
            'template_language' => 'nullable|string',
            'countryId' => 'nullable|exists:countries,id',
            'codeMeta' => 'nullable|string|max:64',
            'registration' => 'nullable|string',
            'vat' => 'nullable|string',
            'eoriNumber' => 'nullable|string',
            'website' => 'nullable|string',
            'additionalInformation' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $active = $request->has('active') ? 1 : 0;
        $contact = Contact::create(array_merge($validated, ['active' => $active]));

        if ($request->filled('product_id')) {
            $contact->products()->sync([$request->input('product_id')]);
        }

        // Address & embedded contacts handling
        if ($request->has('addresses') && is_array($request->input('addresses'))) {
            foreach ($request->input('addresses') as $addrData) {
                if (!empty($addrData['addressName']) || !empty($addrData['addressLine1'])) {
                    $addr = Address::create([
                        'contact_id' => $contact->id,
                        'addressName' => $addrData['addressName'] ?? 'Main Facility',
                        'addressLine1' => $addrData['addressLine1'] ?? null,
                        'addressLine2' => $addrData['addressLine2'] ?? null,
                        'country_id' => $addrData['country_id'] ?? $validated['countryId'] ?? null,
                        'state' => $addrData['state'] ?? null,
                        'city' => $addrData['city'] ?? null,
                        'postalCode' => $addrData['postalCode'] ?? null,
                        'postalBox' => $addrData['postalBox'] ?? null,
                        'forGoogleMapsSearch' => $addrData['forGoogleMapsSearch'] ?? null,
                    ]);

                    if (!empty($addrData['contact_person'])) {
                        $p = $addrData['contact_person'];
                        if (!empty($p['name'])) {
                            Representative::create([
                                'contact_id' => $contact->id,
                                'address_id' => $addr->id,
                                'name' => $p['name'],
                                'job_title' => $p['job_title'] ?? null,
                                'email' => $p['email'] ?? null,
                                'phone' => $p['phone'] ?? null,
                                'phone2' => $p['phone2'] ?? null,
                                'mobile' => $p['mobile'] ?? null,
                                'department' => $p['department'] ?? null,
                            ]);
                        }
                    }
                }
            }
        }

        // Bank Accounts handling
        if ($request->has('bank_accounts') && is_array($request->input('bank_accounts'))) {
            foreach ($request->input('bank_accounts') as $accountData) {
                if (!empty($accountData['name']) || !empty($accountData['accountNumber']) || !empty($accountData['iban']) || !empty($accountData['description'])) {
                    BankAccount::create([
                        'contact_id' => $contact->id,
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

        return redirect()->route('contacts.show', $contact->id)->with('success', "Contact {$contact->name} created successfully!");
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $active = $request->has('active') ? 1 : 0;
        $data = $request->only([
            'name', 'companyId', 'types', 'template_language', 'countryId',
            'codeMeta', 'registration', 'vat', 'eoriNumber', 'website', 'additionalInformation'
        ]);
        $data['active'] = $active;

        $contact->update($data);

        if ($request->has('product_id')) {
            $contact->products()->sync(array_filter([$request->input('product_id')]));
        }

        // Address & embedded contacts handling
        if ($request->has('addresses') && is_array($request->input('addresses'))) {
            foreach ($request->input('addresses') as $addrData) {
                if (!empty($addrData['addressName']) || !empty($addrData['addressLine1'])) {
                    if (!empty($addrData['id'])) {
                        $addr = Address::where('id', $addrData['id'])->where('contact_id', $contact->id)->first();
                        if ($addr) {
                            $addr->update([
                                'addressName' => $addrData['addressName'],
                                'addressLine1' => $addrData['addressLine1'] ?? null,
                                'addressLine2' => $addrData['addressLine2'] ?? null,
                                'country_id' => $addrData['country_id'] ?? null,
                                'state' => $addrData['state'] ?? null,
                                'city' => $addrData['city'] ?? null,
                                'postalCode' => $addrData['postalCode'] ?? null,
                                'postalBox' => $addrData['postalBox'] ?? null,
                                'forGoogleMapsSearch' => $addrData['forGoogleMapsSearch'] ?? null,
                            ]);

                            if (!empty($addrData['contact_person']['name'])) {
                                $p = $addrData['contact_person'];
                                $rep = Representative::where('address_id', $addr->id)->first();
                                if ($rep) {
                                    $rep->update([
                                        'name' => $p['name'],
                                        'job_title' => $p['job_title'] ?? null,
                                        'email' => $p['email'] ?? null,
                                        'phone' => $p['phone'] ?? null,
                                        'phone2' => $p['phone2'] ?? null,
                                        'mobile' => $p['mobile'] ?? null,
                                        'department' => $p['department'] ?? null,
                                    ]);
                                } else {
                                    Representative::create([
                                        'contact_id' => $contact->id,
                                        'address_id' => $addr->id,
                                        'name' => $p['name'],
                                        'job_title' => $p['job_title'] ?? null,
                                        'email' => $p['email'] ?? null,
                                        'phone' => $p['phone'] ?? null,
                                        'phone2' => $p['phone2'] ?? null,
                                        'mobile' => $p['mobile'] ?? null,
                                        'department' => $p['department'] ?? null,
                                    ]);
                                }
                            }
                            continue;
                        }
                    }

                    $addr = Address::create([
                        'contact_id' => $contact->id,
                        'addressName' => $addrData['addressName'],
                        'addressLine1' => $addrData['addressLine1'] ?? null,
                        'addressLine2' => $addrData['addressLine2'] ?? null,
                        'country_id' => $addrData['country_id'] ?? null,
                        'state' => $addrData['state'] ?? null,
                        'city' => $addrData['city'] ?? null,
                        'postalCode' => $addrData['postalCode'] ?? null,
                        'postalBox' => $addrData['postalBox'] ?? null,
                        'forGoogleMapsSearch' => $addrData['forGoogleMapsSearch'] ?? null,
                    ]);

                    if (!empty($addrData['contact_person']['name'])) {
                        $p = $addrData['contact_person'];
                        Representative::create([
                            'contact_id' => $contact->id,
                            'address_id' => $addr->id,
                            'name' => $p['name'],
                            'job_title' => $p['job_title'] ?? null,
                            'email' => $p['email'] ?? null,
                            'phone' => $p['phone'] ?? null,
                            'phone2' => $p['phone2'] ?? null,
                            'mobile' => $p['mobile'] ?? null,
                            'department' => $p['department'] ?? null,
                        ]);
                    }
                }
            }
        }

        // Bank Accounts handling
        if ($request->has('bank_accounts') && is_array($request->input('bank_accounts'))) {
            foreach ($request->input('bank_accounts') as $accountData) {
                if (!empty($accountData['name']) || !empty($accountData['accountNumber']) || !empty($accountData['iban']) || !empty($accountData['description'])) {
                    if (!empty($accountData['id'])) {
                        $ba = BankAccount::where('id', $accountData['id'])->where('contact_id', $contact->id)->first();
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
                        'contact_id' => $contact->id,
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

        return redirect()->route('contacts.show', $contact->id)->with('success', 'Contact details updated successfully!');
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['active' => 0]);
        return redirect()->route('contacts.index')->with('success', 'Contact removed successfully!');
    }
}
