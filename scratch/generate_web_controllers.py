import os

def write_file(filename, code):
    path = os.path.join(r'c:\Users\navde\Downloads\metaerp\app\Http\Controllers', filename)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(code)
    print("Created:", filename)

# 1. WebAuthController.php
write_file('WebAuthController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    protected TotpService $totp;

    public function __construct(TotpService $totp)
    {
        $this->totp = $totp;
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['username' => 'Invalid username or password'])->withInput();
        }

        if (!$user->active) {
            return back()->withErrors(['username' => 'Your account is deactivated. Please contact an administrator.'])->withInput();
        }

        // If 2FA enabled, redirect to 2FA challenge
        if ($user->enable2fa) {
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $request->boolean('remember'));
            return redirect()->route('auth.2fa');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showTwoFactor(Request $request)
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor-challenge');
    }

    public function verifyTwoFactor(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $code = $request->input('code');

        if (!$this->totp->validate($user, (string) $code)) {
            return back()->withErrors(['code' => 'Invalid 6-digit authentication code']);
        }

        $remember = $request->session()->get('2fa:remember', false);
        $request->session()->forget(['2fa:user:id', '2fa:remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:191|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'displayName' => $request->input('displayName', $validated['name']),
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'occupation' => $request->input('occupation', 'Staff'),
            'role' => 'ROLE_USER',
            'active' => true,
            'secret' => $this->totp->generateSecret(),
        ]);

        Auth::login($user);
        return redirect()->route('home')->with('success', 'Account registered successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Logged out successfully');
    }
}
""")

# 2. HomeController.php
write_file('HomeController.php', r"""<?php

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
""")

# 3. WebContractController.php
write_file('WebContractController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ModelWorkflow;
use App\Models\Stage;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Currency;
use App\Models\PackType;
use App\Models\PaymentType;
use App\Models\PaymentTermsType;
use App\Models\Comment;
use App\Models\Checkitem;
use App\Models\Template;
use App\Services\OrderCodeService;
use App\Services\ContractActionService;
use App\Services\ContractAssociationService;
use App\Services\DocumentTemplateService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebContractController extends Controller
{
    protected OrderCodeService $orderCodeService;
    protected ContractActionService $actionService;
    protected ContractAssociationService $associationService;
    protected DocumentTemplateService $docService;
    protected AuditLogService $auditService;

    public function __construct(
        OrderCodeService $orderCodeService,
        ContractActionService $actionService,
        ContractAssociationService $associationService,
        DocumentTemplateService $docService,
        AuditLogService $auditService
    ) {
        $this->orderCodeService = $orderCodeService;
        $this->actionService = $actionService;
        $this->associationService = $associationService;
        $this->docService = $docService;
        $this->auditService = $auditService;
    }

    public function board(Request $request, $modelId = null)
    {
        $models = ModelWorkflow::where('active', true)->get();
        $currentModel = $modelId ? ModelWorkflow::find($modelId) : $models->first();

        if (!$currentModel && $models->isNotEmpty()) {
            $currentModel = $models->first();
        }

        $stages = $currentModel ? $currentModel->stages()->orderBy('priority')->get() : collect();

        $contracts = Contract::with([
            'stage',
            'purchase.metaCompany', 'purchase.contact', 'purchase.products.product',
            'sale.metaCompany', 'sale.contact', 'sale.products.product',
            'checklists.checkitems', 'comments'
        ])
        ->where('active', true)
        ->where('contractStatus', '!=', 'CANCELLED')
        ->orderBy('id', 'desc')
        ->get();

        $companies = Company::where('active', true)->get();
        $contacts = Contact::where('active', true)->get();
        $products = Product::where('active', true)->get();
        $currencies = Currency::all();
        $packTypes = PackType::all();
        $paymentTypes = PaymentType::all();
        $paymentTerms = PaymentTermsType::all();

        return view('contracts.board', compact(
            'models', 'currentModel', 'stages', 'contracts',
            'companies', 'contacts', 'products', 'currencies',
            'packTypes', 'paymentTypes', 'paymentTerms'
        ));
    }

    public function cardModal($id)
    {
        $contract = Contract::with([
            'stage',
            'purchase.metaCompany', 'purchase.contact.types', 'purchase.address.country',
            'purchase.currency', 'purchase.paymentType', 'purchase.paymentTermsType', 'purchase.paymentAccount',
            'purchase.products.product', 'purchase.products.packType', 'purchase.products.currency',
            'sale.metaCompany', 'sale.contact.types', 'sale.address.country',
            'sale.currency', 'sale.paymentType', 'sale.paymentTermsType', 'sale.paymentAccount',
            'sale.products.product', 'sale.products.packType', 'sale.products.currency',
            'packingLists.packs.packType',
            'generatedFiles.template',
            'uploadedFiles',
            'checklists.checkitems',
            'comments.user'
        ])->findOrFail($id);

        $stages = Stage::where('model_id', $contract->stage?->model_id ?? 1)->orderBy('priority')->get();
        $templates = Template::all();

        return view('contracts.partials.card-modal', compact('contract', 'stages', 'templates'));
    }

    public function moveStage(Request $request, $contractId)
    {
        $stageId = (int) $request->input('stage_id');
        try {
            $this->actionService->move((int) $contractId, $stageId, Auth::id());
            return back()->with('success', 'Contract moved successfully');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:BUY,SELL',
            'meta_company_id' => 'required|exists:companies,id',
            'contact_id' => 'required|exists:contacts,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currency,id',
        ]);

        $company = Company::findOrFail($validated['meta_company_id']);
        $contact = Contact::with('addresses')->findOrFail($validated['contact_id']);
        $product = Product::findOrFail($validated['product_id']);

        $total = $validated['quantity'] * $validated['unit_price'];

        $deal = Deal::create([
            'meta_company_id' => $company->id,
            'contact_id' => $contact->id,
            'address_id' => $contact->addresses->first()?->id,
            'currency_id' => $validated['currency_id'],
            'total' => $total,
            'netTotal' => $total,
            'dealPaymentStatus' => 'PENDING',
        ]);

        DealProduct::create([
            'deal_id' => $deal->id,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'unitPrice' => $validated['unit_price'],
            'currency_id' => $validated['currency_id'],
            'total' => $total,
            'status' => 'PENDING',
        ]);

        $firstStage = Stage::where('model_id', $request->input('model_id', 1))->orderBy('priority')->first();

        $contract = Contract::create([
            'date' => now(),
            'type' => $validated['type'],
            'stage_id' => $firstStage?->id ?? 1,
            'purchase_id' => $validated['type'] === 'BUY' ? $deal->id : null,
            'sale_id' => $validated['type'] === 'SELL' ? $deal->id : null,
            'contractStatus' => 'ACTIVE',
            'active' => true,
            'loadingDate' => now()->addDays(14),
            'deliveryTerms' => $request->input('delivery_terms', 'CIF'),
            'modeOfPayment' => $request->input('mode_of_payment', 'TT in Advance'),
        ]);

        try {
            $contract->orderCode = $this->orderCodeService->orderCodeGenerator($contract);
            $contract->save();
        } catch (\Throwable $e) {
            $contract->orderCode = sprintf('ME-COM%s-%d', date('y'), $contract->id);
            $contract->save();
        }

        $this->auditService->register('CONTRACT', $contract->id, 'CREATE', json_encode($contract));

        return redirect()->route('contracts.board', ['modelId' => $firstStage?->model_id])
            ->with('success', "Contract {$contract->orderCode} created successfully!");
    }

    public function cancel($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->contractStatus = 'CANCELLED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CANCEL', null);
        return back()->with('info', "Contract {$contract->orderCode} was cancelled");
    }

    public function close($id)
    {
        $contract = Contract::findOrFail($id);
        if ($contract->type === 'BUY') {
            try {
                $this->associationService->verifyPurchaseContractSaleAssociations($contract->id);
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        $contract->contractStatus = 'CLOSED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CLOSE', null);
        return back()->with('success', "Contract {$contract->orderCode} was closed");
    }

    public function addComment(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        Comment::create([
            'contract_id' => $id,
            'user_id' => Auth::id(),
            'message' => $request->input('message'),
        ]);

        return back()->with('success', 'Comment added');
    }

    public function toggleCheckitem($itemId)
    {
        $item = Checkitem::findOrFail($itemId);
        $item->checked = !$item->checked;
        $item->save();

        return response()->json(['checked' => $item->checked]);
    }

    public function generateDoc(Request $request, $contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $template = Template::findOrFail($request->input('template_id'));

        try {
            $doc = $this->docService->render($template, $contract, Auth::id());
            return back()->with('success', "Document {$doc->fileName} generated successfully!");
        } catch (\Throwable $e) {
            return back()->with('error', "Document generation failed: " . $e->getMessage());
        }
    }
}
""")

# 4. WebSearchController.php
write_file('WebSearchController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\Request;

class WebSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['stage', 'purchase.contact', 'purchase.metaCompany', 'sale.contact', 'sale.metaCompany'])
            ->where('active', true);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->where('orderCode', 'like', "%{$q}%")
                    ->orWhere('customerPurchaseOrder', 'like', "%{$q}%")
                    ->orWhereHas('purchase.contact', fn($c) => $c->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('sale.contact', fn($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('contractStatus', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->input('to_date'));
        }

        $contracts = $query->orderBy('id', 'desc')->paginate(20);
        $companies = Company::where('active', true)->get();
        $contacts = Contact::where('active', true)->get();

        return view('contracts.search', compact('contracts', 'companies', 'contacts'));
    }
}
""")

# 5. WebOfferController.php
write_file('WebOfferController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;

class WebOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::with(['metaCompany', 'contact', 'product', 'currency']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $offers = $query->orderBy('id', 'desc')->paginate(15);
        $companies = Company::where('active', true)->get();
        $contacts = Contact::where('active', true)->get();
        $products = Product::where('active', true)->get();
        $currencies = Currency::all();

        return view('offers.index', compact('offers', 'companies', 'contacts', 'products', 'currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:BUY,SELL',
            'meta_company_id' => 'required|exists:companies,id',
            'contact_id' => 'required|exists:contacts,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unitPrice' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currency,id',
            'validUntil' => 'nullable|date',
        ]);

        $validated['total'] = $validated['quantity'] * $validated['unitPrice'];
        $validated['orderCode'] = 'OFF-' . date('Y') . '-' . str_pad((string) (Offer::count() + 1), 3, '0', STR_PAD_LEFT);
        $validated['status'] = 'OPEN';

        Offer::create($validated);

        return redirect()->route('offers.index')->with('success', 'Commercial offer created successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);
        $offer->status = $request->input('status', 'OPEN');
        $offer->save();

        return back()->with('success', 'Offer status updated');
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();

        return back()->with('info', 'Offer deleted');
    }
}
""")

# 6. WebCompanyController.php
write_file('WebCompanyController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Country;
use App\Models\Address;
use App\Models\BankAccount;
use App\Models\Representative;
use Illuminate\Http\Request;

class WebCompanyController extends Controller
{
    public function index()
    {
        $companies = Company::with(['address.country', 'bankAccounts', 'representatives'])->where('active', true)->get();
        $countries = Country::where('active', true)->orderBy('name')->get();
        return view('companies.index', compact('companies', 'countries'));
    }

    public function show($id)
    {
        $company = Company::with(['address.country', 'bankAccounts', 'representatives'])->findOrFail($id);
        $countries = Country::where('active', true)->orderBy('name')->get();
        return view('companies.show', compact('company', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:64',
            'countryId' => 'nullable|exists:countries,id',
            'vatNumber' => 'nullable|string',
            'companyNumber' => 'nullable|string',
            'phoneNumber' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $company = Company::create($validated);

        if ($request->filled('addressLine1')) {
            $addr = Address::create([
                'addressName' => 'Main Office',
                'addressLine1' => $request->input('addressLine1'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'postalCode' => $request->input('postalCode'),
                'country_id' => $validated['countryId'],
                'company_id' => $company->id,
            ]);
            $company->address_id = $addr->id;
            $company->save();
        }

        return redirect()->route('companies.index')->with('success', "Company {$company->name} created successfully!");
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->update($request->only([
            'name', 'code', 'vatNumber', 'companyNumber', 'phoneNumber', 'faxNumber', 'email', 'webSite', 'eoriNumber'
        ]));

        return back()->with('success', 'Company details updated');
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
""")

# 7. WebContactController.php
write_file('WebContactController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\Contact;
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
        $query = Contact::with(['types', 'products', 'addresses.country', 'representatives', 'currency', 'language'])
            ->where('active', true);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('codeMeta', 'like', "%{$q}%")
                    ->orWhere('vat', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type_id')) {
            $query->whereHas('types', fn($t) => $t->where('types.id', $request->input('type_id')));
        }

        $contacts = $query->orderBy('name')->paginate(20);
        $countries = Country::where('active', true)->orderBy('name')->get();
        $types = Type::all();
        $products = Product::where('active', true)->get();
        $currencies = Currency::all();
        $languages = Language::all();

        return view('contacts.index', compact('contacts', 'countries', 'types', 'products', 'currencies', 'languages'));
    }

    public function show($id)
    {
        $contact = Contact::with(['types', 'products', 'addresses.country', 'representatives', 'bankAccounts', 'currency', 'language'])->findOrFail($id);
        $countries = Country::where('active', true)->orderBy('name')->get();
        $types = Type::all();
        $products = Product::where('active', true)->get();
        return view('contacts.show', compact('contact', 'countries', 'types', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'codeMeta' => 'nullable|string|max:64',
            'vat' => 'nullable|string|max:64',
            'countryId' => 'nullable|exists:countries,id',
            'currency_id' => 'nullable|exists:currency,id',
            'language_id' => 'nullable|exists:languages,id',
        ]);

        $contact = Contact::create($validated);

        if ($request->has('type_ids')) {
            $contact->types()->sync($request->input('type_ids'));
        }

        if ($request->has('product_ids')) {
            $contact->products()->sync($request->input('product_ids'));
        }

        if ($request->filled('addressLine1')) {
            Address::create([
                'addressName' => 'Main Facility',
                'addressLine1' => $request->input('addressLine1'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'postalCode' => $request->input('postalCode'),
                'country_id' => $validated['countryId'] ?? null,
                'contact_id' => $contact->id,
            ]);
        }

        return redirect()->route('contacts.index')->with('success', "Contact {$contact->name} added successfully!");
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update($request->only(['name', 'codeMeta', 'vat', 'registration', 'countryId', 'currency_id', 'language_id']));

        if ($request->has('type_ids')) {
            $contact->types()->sync($request->input('type_ids'));
        }

        if ($request->has('product_ids')) {
            $contact->products()->sync($request->input('product_ids'));
        }

        return back()->with('success', 'Contact updated successfully');
    }
}
""")

# 8. WebProductController.php
write_file('WebProductController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WebProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('active', true);

        if ($request->filled('category')) {
            $query->where('productCategory', $request->input('category'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('chemicalFormula', 'like', "%{$q}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20);
        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:64',
            'productCategory' => 'required|string',
            'chemicalFormula' => 'nullable|string',
            'unitOfMeasurement' => 'required|string',
            'hsCode' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->only([
            'name', 'code', 'productCategory', 'chemicalFormula', 'unitOfMeasurement', 'hsCode', 'description'
        ]));

        return back()->with('success', 'Product updated successfully');
    }
}
""")

# 9. WebUserController.php & WebAccessGroupController.php
write_file('WebUserController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AccessGroup;
use App\Models\Company;
use App\Models\Permission;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WebUserController extends Controller
{
    public function index()
    {
        $users = User::with(['permissions.accessGroup', 'permissions.company'])->orderBy('name')->paginate(20);
        $accessGroups = AccessGroup::where('active', true)->get();
        $companies = Company::where('active', true)->get();

        return view('users.index', compact('users', 'accessGroups', 'companies'));
    }

    public function store(Request $request, TotpService $totp)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:191|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'occupation' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'displayName' => $request->input('displayName', $validated['name']),
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'occupation' => $validated['occupation'],
            'role' => $validated['role'],
            'active' => true,
            'secret' => $totp->generateSecret(),
            'enable2fa' => $request->boolean('enable2fa'),
        ]);

        if ($request->filled('accessgroup_id') && $request->filled('company_id')) {
            Permission::create([
                'user_id' => $user->id,
                'company_id' => $request->input('company_id'),
                'accessgroup_id' => $request->input('accessgroup_id'),
            ]);
        }

        return redirect()->route('users.index')->with('success', "User {$user->name} created successfully");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->only(['name', 'displayName', 'occupation', 'role', 'active']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return back()->with('success', 'User updated successfully');
    }

    public function uploadSignature(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $b64 = base64_encode(file_get_contents($file->getRealPath()));
            $user->signatureFile = $b64;
            $user->signature_file_name = $file->getClientOriginalName();
            $user->save();

            return back()->with('success', 'Digital signature uploaded');
        }
        return back()->with('error', 'No file was provided');
    }
}
""")

write_file('WebAccessGroupController.php', r"""<?php

namespace App\Http\Controllers;

use App\Models\AccessGroup;
use App\Models\Access;
use Illuminate\Http\Request;

class WebAccessGroupController extends Controller
{
    public function index()
    {
        $groups = AccessGroup::with('accessList')->where('active', true)->get();
        $modules = ['DASHBOARD', 'USERS', 'PRODUCT', 'COMPANY', 'CONTACT', 'OFFER', 'CONTRACT', 'ACCESS_CONTROL', 'SYSTEM_SETTINGS'];
        $actions = ['CREATE', 'READ', 'UPDATE', 'DELETE'];

        return view('accessgroups.index', compact('groups', 'modules', 'actions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $group = AccessGroup::create(['name' => $validated['name'], 'active' => true]);

        $matrix = $request->input('matrix', []);
        foreach ($matrix as $mod => $actions) {
            foreach ($actions as $act => $enabled) {
                if ($enabled) {
                    Access::create([
                        'accessgroup_id' => $group->id,
                        'module' => $mod,
                        'accessType' => $act,
                        'active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('accessgroups.index')->with('success', 'Access group created successfully');
    }

    public function update(Request $request, $id)
    {
        $group = AccessGroup::findOrFail($id);
        $group->update(['name' => $request->input('name')]);

        $group->accessList()->delete();
        $matrix = $request->input('matrix', []);
        foreach ($matrix as $mod => $actions) {
            foreach ($actions as $act => $enabled) {
                if ($enabled) {
                    Access::create([
                        'accessgroup_id' => $group->id,
                        'module' => $mod,
                        'accessType' => $act,
                        'active' => true,
                    ]);
                }
            }
        }

        return back()->with('success', 'Permissions updated successfully');
    }
}
""")

# 10. WebProfileController.php
write_file('WebProfileController.php', r"""<?php

namespace App\Http\Controllers;

use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebProfileController extends Controller
{
    public function index(TotpService $totp)
    {
        $user = Auth::user();
        $qrUrl = $totp->generateQRUrl($user);

        return view('profile.index', compact('user', 'qrUrl'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'displayName' => 'nullable|string|max:255',
            'occupation' => 'nullable|string',
        ]);

        $user->update($validated);
        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password changed successfully');
    }

    public function toggle2fa(Request $request, TotpService $totp)
    {
        $user = Auth::user();
        $code = $request->input('code');

        if (!$user->enable2fa) {
            // Verify code to enable
            if (!$totp->validate($user, (string) $code)) {
                return back()->withErrors(['code' => 'Invalid verification code. Please check your authenticator app.']);
            }
            $user->enable2fa = true;
            $user->save();
            return back()->with('success', 'Two-Factor Authentication is now ENABLED!');
        } else {
            $user->enable2fa = false;
            $user->save();
            return back()->with('info', 'Two-Factor Authentication has been disabled.');
        }
    }
}
""")

# 11. WebDashboardController.php & WebSettingsController.php
write_file('WebDashboardController.php', r"""<?php

namespace App\Http\Controllers;

use App\Services\MetabaseEmbedService;
use Illuminate\Http\Request;

class WebDashboardController extends Controller
{
    public function analytics(Request $request, MetabaseEmbedService $metabaseService)
    {
        $dashboardId = (int) $request->input('dashboardId', 1);
        $embedUrl = $metabaseService->generateEmbedUrl($dashboardId);

        return view('dashboards.analytics', compact('embedUrl', 'dashboardId'));
    }
}
""")

write_file('WebSettingsController.php', r"""<?php

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
    public function countries() { return view('settings.countries', ['countries' => Country::paginate(20)]); }
    public function currencies() { return view('settings.currencies', ['currencies' => Currency::all()]); }
    public function departments() { return view('settings.departments', ['departments' => Department::all()]); }
    public function packTypes() { return view('settings.pack-types', ['packTypes' => PackType::all()]); }
    public function paymentTypes() { return view('settings.payment-types', ['paymentTypes' => PaymentType::all()]); }
    public function paymentTerms() { return view('settings.payment-terms', ['paymentTerms' => PaymentTermsType::all()]); }
}
""")
