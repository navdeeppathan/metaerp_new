<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Type;
use App\Models\CreditDebitType;
use App\Models\Currency;
use App\Models\Department;
use App\Models\PackType;
use App\Models\PaymentType;
use App\Models\PaymentTermsType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class WebSettingsController extends Controller
{
    // ==========================================
    // Countries
    // ==========================================

    public function countries(Request $request)
    {
        $query = Country::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('alpha2', 'like', "%{$search}%")
                  ->orWhere('alpha3', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $countries = $query->orderBy('name', 'asc')->paginate($perPage)->withQueryString();

        return view('settings.countries.index', compact('countries'));
    }

    public function createCountry()
    {
        return view('settings.countries.create', ['country' => null]);
    }

    public function showCountry($id)
    {
        $country = Country::findOrFail($id);

        return view('settings.countries.create', compact('country'));
    }

    public function storeCountry(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'alpha2' => 'nullable|string|max:10',
            'alpha3' => 'nullable|string|max:10',
            'region' => 'required|string|max:191',
            'vat' => 'required|numeric',
        ]);

        $data['active'] = 1;
        $data['version'] = 0;

        Country::create($data);

        return redirect()->route('settings.countries')->with('success', 'Country created successfully.');
    }

    public function updateCountry(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'alpha2' => 'nullable|string|max:10',
            'alpha3' => 'nullable|string|max:10',
            'region' => 'required|string|max:191',
            'vat' => 'required|numeric',
        ]);

        $country->update($data);

        return redirect()->route('settings.countries')->with('success', 'Country updated successfully.');
    }

    public function destroyCountry($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return redirect()->route('settings.countries')->with('success', 'Country deleted successfully.');
    }

    // ==========================================
    // Company Types
    // ==========================================

    private function ensureTypesTable(): void
    {
        try {
            if (!Schema::hasTable('types')) {
                Schema::create('types', function ($table) {
                    $table->id();
                    $table->string('name')->nullable();
                    $table->string('description')->nullable();
                    $table->boolean('active')->default(true);
                    $table->unsignedBigInteger('version')->default(0);
                });

                DB::table('types')->insert([
                    ['id' => 1, 'name' => 'Buyer', 'description' => 'Buyer', 'active' => 1, 'version' => 0],
                    ['id' => 2, 'name' => 'Seller', 'description' => 'Seller', 'active' => 1, 'version' => 0],
                    ['id' => 3, 'name' => 'Trader', 'description' => 'Trader', 'active' => 1, 'version' => 0],
                    ['id' => 4, 'name' => 'Metal Agent', 'description' => 'Metal Agent', 'active' => 1, 'version' => 0],
                    ['id' => 5, 'name' => 'Galvanizer', 'description' => 'Galvanizer', 'active' => 1, 'version' => 0],
                    ['id' => 6, 'name' => 'Recycler', 'description' => 'Recycler', 'active' => 1, 'version' => 0],
                    ['id' => 7, 'name' => 'Construction', 'description' => 'Construction', 'active' => 1, 'version' => 0],
                    ['id' => 8, 'name' => 'Shipping Agent', 'description' => 'Shipping Agent', 'active' => 1, 'version' => 0],
                    ['id' => 9, 'name' => 'Miscellaneous', 'description' => 'Miscellaneous', 'active' => 1, 'version' => 0],
                    ['id' => 10, 'name' => 'Chemical Lab Analysis', 'description' => 'Chemical Lab Analysis', 'active' => 1, 'version' => 0],
                    ['id' => 11, 'name' => 'Custom Broker', 'description' => 'Custom Broker', 'active' => 1, 'version' => 0],
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore failure if user has no DDL permissions or schema query fails
        }
    }

    public function companyTypes(Request $request)
    {
        $this->ensureTypesTable();

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $companyTypes = null;
        try {
            if (Schema::hasTable('types')) {
                $query = Type::query();

                if ($request->filled('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        if (Schema::hasColumn('types', 'description')) {
                            $q->where('description', 'like', "%{$search}%");
                        }
                        if (Schema::hasColumn('types', 'name')) {
                            $q->orWhere('name', 'like', "%{$search}%");
                        }
                    });
                }

                $companyTypes = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();
            }
        } catch (\Throwable $e) {
            $companyTypes = null;
        }

        if (!$companyTypes) {
            // Graceful fallback when types table is missing or query throws
            $defaultTypes = collect([
                new Type(['id' => 1, 'name' => 'Buyer', 'description' => 'Buyer', 'active' => true, 'version' => 0]),
                new Type(['id' => 2, 'name' => 'Seller', 'description' => 'Seller', 'active' => true, 'version' => 0]),
                new Type(['id' => 3, 'name' => 'Trader', 'description' => 'Trader', 'active' => true, 'version' => 0]),
                new Type(['id' => 4, 'name' => 'Metal Agent', 'description' => 'Metal Agent', 'active' => true, 'version' => 0]),
                new Type(['id' => 5, 'name' => 'Galvanizer', 'description' => 'Galvanizer', 'active' => true, 'version' => 0]),
                new Type(['id' => 6, 'name' => 'Recycler', 'description' => 'Recycler', 'active' => true, 'version' => 0]),
                new Type(['id' => 7, 'name' => 'Construction', 'description' => 'Construction', 'active' => true, 'version' => 0]),
                new Type(['id' => 8, 'name' => 'Shipping Agent', 'description' => 'Shipping Agent', 'active' => true, 'version' => 0]),
                new Type(['id' => 9, 'name' => 'Miscellaneous', 'description' => 'Miscellaneous', 'active' => true, 'version' => 0]),
                new Type(['id' => 10, 'name' => 'Chemical Lab Analysis', 'description' => 'Chemical Lab Analysis', 'active' => true, 'version' => 0]),
                new Type(['id' => 11, 'name' => 'Custom Broker', 'description' => 'Custom Broker', 'active' => true, 'version' => 0]),
            ]);

            if ($request->filled('search')) {
                $search = strtolower($request->input('search'));
                $defaultTypes = $defaultTypes->filter(function ($t) use ($search) {
                    return str_contains(strtolower($t->description ?? $t->name ?? ''), $search);
                });
            }

            $page = (int) $request->input('page', 1);
            $companyTypes = new LengthAwarePaginator(
                $defaultTypes->forPage($page, $perPage)->values(),
                $defaultTypes->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('settings.company-types.index', compact('companyTypes'));
    }

    public function createCompanyType()
    {
        return view('settings.company-types.create', ['type' => null]);
    }

    public function showCompanyType($id)
    {
        $this->ensureTypesTable();

        $type = null;
        if (Schema::hasTable('types')) {
            $type = Type::find($id);
        }

        if (!$type) {
            $type = new Type([
                'id' => (int) $id,
                'name' => 'Type #' . $id,
                'description' => 'Type #' . $id,
                'active' => true,
            ]);
        }

        return view('settings.company-types.create', compact('type'));
    }

    public function storeCompanyType(Request $request)
    {
        $name = $request->input('name') ?? $request->input('description');
        if (empty($name)) {
            return back()->withErrors(['name' => 'The Name field is required.']);
        }

        $this->ensureTypesTable();

        if (Schema::hasTable('types')) {
            $data = ['version' => 0];
            if (Schema::hasColumn('types', 'description')) {
                $data['description'] = $name;
            }
            if (Schema::hasColumn('types', 'name')) {
                $data['name'] = $name;
            }
            if (Schema::hasColumn('types', 'active')) {
                $data['active'] = 1;
            }

            Type::create($data);
        }

        return redirect()->route('settings.companyTypes')->with('success', 'Company type created successfully.');
    }

    public function updateCompanyType(Request $request, $id)
    {
        $name = $request->input('name') ?? $request->input('description');
        if (empty($name)) {
            return back()->withErrors(['name' => 'The Name field is required.']);
        }

        $this->ensureTypesTable();

        if (Schema::hasTable('types')) {
            $type = Type::findOrFail($id);
            $data = [];
            if (Schema::hasColumn('types', 'description')) {
                $data['description'] = $name;
            }
            if (Schema::hasColumn('types', 'name')) {
                $data['name'] = $name;
            }
            $type->update($data);
        }

        return redirect()->route('settings.companyTypes')->with('success', 'Company type updated successfully.');
    }

    public function destroyCompanyType($id)
    {
        $this->ensureTypesTable();

        if (Schema::hasTable('types')) {
            $type = Type::find($id);
            if ($type) {
                $type->delete();
            }
        }

        return redirect()->route('settings.companyTypes')->with('success', 'Company type deleted successfully.');
    }

    // ==========================================
    // CreditDebitTypes
    // ==========================================

    public function creditDebitTypes(Request $request)
    {
        $query = CreditDebitType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $items = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        return view('settings.credit-debit-types.index', compact('items'));
    }

    public function createCreditDebitType()
    {
        return view('settings.credit-debit-types.create', ['type' => null]);
    }

    public function showCreditDebitType($id)
    {
        $type = CreditDebitType::findOrFail($id);

        return view('settings.credit-debit-types.create', compact('type'));
    }

    public function storeCreditDebitType(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);
        $data['version'] = 0;

        CreditDebitType::create($data);

        return redirect()->route('settings.creditDebitTypes')->with('success', 'Credit/Debit type created successfully.');
    }

    public function updateCreditDebitType(Request $request, $id)
    {
        $type = CreditDebitType::findOrFail($id);

        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);

        $type->update($data);

        return redirect()->route('settings.creditDebitTypes')->with('success', 'Credit/Debit type updated successfully.');
    }

    public function destroyCreditDebitType($id)
    {
        $type = CreditDebitType::findOrFail($id);
        $type->delete();

        return redirect()->route('settings.creditDebitTypes')->with('success', 'Credit/Debit type deleted successfully.');
    }

    // ==========================================
    // Currencies
    // ==========================================

    public function currencies(Request $request)
    {
        $query = Currency::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('abbreviation', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $currencies = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        return view('settings.currencies.index', ['items' => $currencies, 'currencies' => $currencies]);
    }

    public function createCurrency()
    {
        return view('settings.currencies.create', ['currency' => null]);
    }

    public function showCurrency($id)
    {
        $currency = Currency::findOrFail($id);

        return view('settings.currencies.create', compact('currency'));
    }

    public function storeCurrency(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'abbreviation' => 'required|string|max:20',
            'symbol' => 'nullable|string|max:10',
            'htmlEncodeSymbol' => 'nullable|string|max:50',
        ]);

        $data['description'] = $data['abbreviation'];
        $data['rate'] = 1.0;
        $data['version'] = 0;

        Currency::create($data);

        return redirect()->route('settings.currencies')->with('success', 'Currency created successfully.');
    }

    public function updateCurrency(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'abbreviation' => 'required|string|max:20',
            'symbol' => 'nullable|string|max:10',
            'htmlEncodeSymbol' => 'nullable|string|max:50',
        ]);

        $data['description'] = $data['abbreviation'];

        $currency->update($data);

        return redirect()->route('settings.currencies')->with('success', 'Currency updated successfully.');
    }

    public function destroyCurrency($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();

        return redirect()->route('settings.currencies')->with('success', 'Currency deleted successfully.');
    }

    // ==========================================
    // Departments
    // ==========================================

    public function departments(Request $request)
    {
        $query = Department::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $departments = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        return view('settings.departments.index', ['items' => $departments, 'departments' => $departments]);
    }

    public function createDepartment()
    {
        return view('settings.departments.create', ['department' => null]);
    }

    public function showDepartment($id)
    {
        $department = Department::findOrFail($id);

        return view('settings.departments.create', compact('department'));
    }

    public function storeDepartment(Request $request)
    {
        $desc = $request->input('description') ?? $request->input('name');
        if (empty($desc)) {
            return back()->withErrors(['description' => 'The Description field is required.']);
        }

        Department::create([
            'name' => $desc,
            'description' => $desc,
            'version' => 0,
        ]);

        return redirect()->route('settings.departments')->with('success', 'Department created successfully.');
    }

    public function updateDepartment(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        $desc = $request->input('description') ?? $request->input('name');
        if (empty($desc)) {
            return back()->withErrors(['description' => 'The Description field is required.']);
        }

        $department->update([
            'name' => $desc,
            'description' => $desc,
        ]);

        return redirect()->route('settings.departments')->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()->route('settings.departments')->with('success', 'Department deleted successfully.');
    }

    // ==========================================
    // PackTypes
    // ==========================================

    public function packTypes(Request $request)
    {
        $query = PackType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $packTypes = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        return view('settings.pack-types.index', ['items' => $packTypes, 'packTypes' => $packTypes]);
    }

    public function createPackType()
    {
        return view('settings.pack-types.create', ['packType' => null]);
    }

    public function showPackType($id)
    {
        $packType = PackType::findOrFail($id);

        return view('settings.pack-types.create', compact('packType'));
    }

    public function storePackType(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);
        $data['version'] = 0;

        PackType::create($data);

        return redirect()->route('settings.packTypes')->with('success', 'Pack type created successfully.');
    }

    public function updatePackType(Request $request, $id)
    {
        $packType = PackType::findOrFail($id);
        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);

        $packType->update($data);

        return redirect()->route('settings.packTypes')->with('success', 'Pack type updated successfully.');
    }

    public function destroyPackType($id)
    {
        $packType = PackType::findOrFail($id);
        $packType->delete();

        return redirect()->route('settings.packTypes')->with('success', 'Pack type deleted successfully.');
    }

    // ==========================================
    // PaymentTypes (Screenshots 1, 2, 3)
    // ==========================================

    public function paymentTypes(Request $request)
    {
        $query = PaymentType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $paymentTypes = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        return view('settings.payment-types.index', ['items' => $paymentTypes, 'paymentTypes' => $paymentTypes]);
    }

    public function createPaymentType()
    {
        return view('settings.payment-types.create', ['paymentType' => null]);
    }

    public function showPaymentType($id)
    {
        $paymentType = PaymentType::findOrFail($id);

        return view('settings.payment-types.create', compact('paymentType'));
    }

    public function storePaymentType(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);
        $data['version'] = 0;

        PaymentType::create($data);

        return redirect()->route('settings.paymentTypes')->with('success', 'Payment type created successfully.');
    }

    public function updatePaymentType(Request $request, $id)
    {
        $paymentType = PaymentType::findOrFail($id);
        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);

        $paymentType->update($data);

        return redirect()->route('settings.paymentTypes')->with('success', 'Payment type updated successfully.');
    }

    public function destroyPaymentType($id)
    {
        $paymentType = PaymentType::findOrFail($id);
        $paymentType->delete();

        return redirect()->route('settings.paymentTypes')->with('success', 'Payment type deleted successfully.');
    }

    // ==========================================
    // PaymentTermsTypes (Screenshots 4 & 5)
    // ==========================================

    public function paymentTerms(Request $request)
    {
        $query = PaymentTermsType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $paymentTerms = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        return view('settings.payment-terms.index', ['items' => $paymentTerms, 'paymentTerms' => $paymentTerms]);
    }

    public function createPaymentTerm()
    {
        return view('settings.payment-terms.create', ['paymentTerm' => null]);
    }

    public function showPaymentTerm($id)
    {
        $paymentTerm = PaymentTermsType::findOrFail($id);

        return view('settings.payment-terms.create', compact('paymentTerm'));
    }

    public function storePaymentTerm(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);
        $data['version'] = 0;

        PaymentTermsType::create($data);

        return redirect()->route('settings.paymentTerms')->with('success', 'Payment terms type created successfully.');
    }

    public function updatePaymentTerm(Request $request, $id)
    {
        $paymentTerm = PaymentTermsType::findOrFail($id);
        $data = $request->validate([
            'description' => 'required|string|max:191',
        ]);

        $paymentTerm->update($data);

        return redirect()->route('settings.paymentTerms')->with('success', 'Payment terms type updated successfully.');
    }

    public function destroyPaymentTerm($id)
    {
        $paymentTerm = PaymentTermsType::findOrFail($id);
        $paymentTerm->delete();

        return redirect()->route('settings.paymentTerms')->with('success', 'Payment terms type deleted successfully.');
    }
}
