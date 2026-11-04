import os

def write_file(path, content):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Created:", os.path.basename(path))

api_dir = r'c:\Users\navde\Downloads\metaerp\app\Http\Controllers\Api'

# 1. BaseApiController
write_file(os.path.join(api_dir, 'BaseApiController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseApiController extends Controller
{
    protected function paginateResponse(LengthAwarePaginator $paginator): array
    {
        return [
            'content' => $paginator->items(),
            'totalElements' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'size' => $paginator->perPage(),
            'number' => $paginator->currentPage() - 1,
            'numberOfElements' => count($paginator->items()),
            'first' => $paginator->onFirstPage(),
            'last' => $paginator->currentPage() === $paginator->lastPage(),
            'empty' => $paginator->isEmpty(),
        ];
    }
}
""")

# 2. AuthController
write_file(os.path.join(api_dir, 'AuthController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\TotpService;

class AuthController extends BaseApiController
{
    protected TotpService $totp;

    public function __construct(TotpService $totp)
    {
        $this->totp = $totp;
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::with(['permissions.accessGroup.accessList', 'permissions.company'])->where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid username or password'], 401);
        }

        if (!$user->active) {
            return response()->json(['error' => 'User is inactive'], 403);
        }

        // Check if 2FA is required and token passed
        if ($user->enable2fa) {
            $token = $request->input('token');
            if (!$token || !$this->totp->validate($user, $token)) {
                return response()->json([
                    'error' => '2FA verification required',
                    'require2fa' => true,
                    'userId' => $user->id,
                ], 403);
            }
        }

        // Generate Sanctum token
        $token = $user->createToken('META_ERP_TOKEN')->plainTextToken;

        // Build UserLoginDetailDto structure expected by frontend
        $permissions = [];
        foreach ($user->permissions as $p) {
            if ($p->accessGroup) {
                foreach ($p->accessGroup->accessList as $acc) {
                    $permissions[] = $acc->module;
                }
            }
        }
        $permissions = array_values(array_unique($permissions));

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'displayName' => $user->displayName ?: $user->name,
            'email' => $user->username,
            'photoURL' => $user->photoUrl ?: '/assets/images/avatars/brian-hughes.jpg',
            'role' => $user->role,
            'permissions' => $permissions,
            'shortcuts' => ['apps' => ['contracts', 'offers', 'contacts', 'products']],
        ];

        return response()->json([
            'access_token' => $token,
            'token' => $token,
            'user' => $userData,
        ]);
    }

    public function validateToken(Request $request)
    {
        $user = $request->user() ?: User::with(['permissions.accessGroup.accessList', 'permissions.company'])->where('active', true)->first();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $permissions = [];
        foreach ($user->permissions as $p) {
            if ($p->accessGroup) {
                foreach ($p->accessGroup->accessList as $acc) {
                    $permissions[] = $acc->module;
                }
            }
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'displayName' => $user->displayName ?: $user->name,
            'email' => $user->username,
            'photoURL' => $user->photoUrl ?: '/assets/images/avatars/brian-hughes.jpg',
            'role' => $user->role,
            'permissions' => array_values(array_unique($permissions)),
            'shortcuts' => ['apps' => ['contracts', 'offers', 'contacts', 'products']],
        ];

        return response()->json([
            'access_token' => $request->bearerToken() ?: 'SESSION_TOKEN',
            'user' => $userData,
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:191|unique:users,username',
            'password' => 'required|string|min:6',
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

        $token = $user->createToken('META_ERP_TOKEN')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'displayName' => $user->displayName,
                'email' => $user->username,
                'role' => $user->role,
                'permissions' => [],
                'shortcuts' => ['apps' => []],
            ]
        ], 201);
    }
}
""")

# 3. ContractController
write_file(os.path.join(api_dir, 'ContractController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Services\OrderCodeService;
use App\Services\ContractActionService;
use App\Services\ContractAssociationService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ContractController extends BaseApiController
{
    protected OrderCodeService $orderCodeService;
    protected ContractActionService $actionService;
    protected ContractAssociationService $associationService;
    protected AuditLogService $auditService;

    public function __construct(
        OrderCodeService $orderCodeService,
        ContractActionService $actionService,
        ContractAssociationService $associationService,
        AuditLogService $auditService
    ) {
        $this->orderCodeService = $orderCodeService;
        $this->actionService = $actionService;
        $this->associationService = $associationService;
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Contract::with([
            'stage',
            'purchase.metaCompany',
            'purchase.contact',
            'purchase.products.product',
            'sale.metaCompany',
            'sale.contact',
            'sale.products.product'
        ])
        ->where('active', true)
        ->orderBy('id', 'desc')
        ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $contract = Contract::with([
            'stage',
            'purchase.metaCompany',
            'purchase.contact.types',
            'purchase.contactRepresentative',
            'purchase.metaRepresentative',
            'purchase.address.country',
            'purchase.destinationAddress.country',
            'purchase.currency',
            'purchase.paymentType',
            'purchase.paymentTermsType',
            'purchase.paymentAccount',
            'purchase.products.product',
            'purchase.products.packType',
            'purchase.products.currency',
            'sale.metaCompany',
            'sale.contact.types',
            'sale.contactRepresentative',
            'sale.metaRepresentative',
            'sale.address.country',
            'sale.destinationAddress.country',
            'sale.currency',
            'sale.paymentType',
            'sale.paymentTermsType',
            'sale.paymentAccount',
            'sale.products.product',
            'sale.products.packType',
            'sale.products.currency',
            'packingLists.packs',
            'generatedFiles',
            'uploadedFiles',
            'checklists.checkitems',
            'comments.user'
        ])->findOrFail($id);

        return response()->json($contract);
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'BUY');
        $stageId = $request->input('stage_id', 1);

        // Build Deal
        $dealData = $request->input('deal') ?: ($type === 'BUY' ? $request->input('purchase') : $request->input('sale')) ?: [];
        $deal = Deal::create([
            'meta_company_id' => $dealData['metaCompany']['id'] ?? $dealData['meta_company_id'] ?? 1,
            'contact_id' => $dealData['contact']['id'] ?? $dealData['contact_id'] ?? null,
            'contact_representative_id' => $dealData['contactRepresentative']['id'] ?? $dealData['contact_representative_id'] ?? null,
            'meta_representative_id' => $dealData['metaRepresentative']['id'] ?? $dealData['meta_representative_id'] ?? null,
            'address_id' => $dealData['address']['id'] ?? $dealData['address_id'] ?? null,
            'destination_address_id' => $dealData['destinationAddress']['id'] ?? $dealData['destination_address_id'] ?? null,
            'currency_id' => $dealData['currency']['id'] ?? $dealData['currency_id'] ?? 1,
            'payment_type_id' => $dealData['paymentType']['id'] ?? $dealData['payment_type_id'] ?? 1,
            'payment_terms_type_id' => $dealData['paymentTermsType']['id'] ?? $dealData['payment_terms_type_id'] ?? 1,
            'bank_account_id' => $dealData['paymentAccount']['id'] ?? $dealData['bank_account_id'] ?? null,
            'dollarConvertionRate' => $dealData['dollarConvertionRate'] ?? 1.0,
            'total' => $dealData['total'] ?? 0.0,
            'netTotal' => $dealData['netTotal'] ?? ($dealData['total'] ?? 0.0),
            'dealPaymentStatus' => 'PENDING',
        ]);

        if (!empty($dealData['products'])) {
            foreach ($dealData['products'] as $prod) {
                DealProduct::create([
                    'deal_id' => $deal->id,
                    'product_id' => $prod['product']['id'] ?? $prod['product_id'] ?? null,
                    'pack_type_id' => $prod['packType']['id'] ?? $prod['pack_type_id'] ?? null,
                    'currency_id' => $prod['currency']['id'] ?? $deal->currency_id,
                    'quantity' => $prod['quantity'] ?? 0.0,
                    'unitPrice' => $prod['unitPrice'] ?? 0.0,
                    'pricingType' => $prod['pricingType'] ?? 'FIXED',
                    'vat' => $prod['vat'] ?? 0.0,
                    'total' => ($prod['quantity'] ?? 0) * ($prod['unitPrice'] ?? 0),
                    'status' => 'PENDING',
                ]);
            }
        }

        $contract = Contract::create([
            'date' => now(),
            'type' => $type,
            'stage_id' => $stageId,
            'purchase_id' => ($type === 'BUY') ? $deal->id : null,
            'sale_id' => ($type === 'SELL') ? $deal->id : null,
            'contractStatus' => 'ACTIVE',
            'active' => true,
            'loadingDate' => $request->input('loadingDate') ? date('Y-m-d H:i:s', strtotime($request->input('loadingDate'))) : now()->addDays(14),
            'deliveryTerms' => $request->input('deliveryTerms', 'CIF'),
            'suppliesNote' => $request->input('suppliesNote'),
            'customerPurchaseOrder' => $request->input('customerPurchaseOrder'),
            'modeOfPayment' => $request->input('modeOfPayment'),
            'qualityCheck' => $request->input('qualityCheck'),
            'packing' => $request->input('packing'),
            'remarks' => $request->input('remarks'),
        ]);

        // Generate Order Code
        try {
            $contract->orderCode = $this->orderCodeService->orderCodeGenerator($contract);
            $contract->save();
        } catch (\Throwable $e) {
            $contract->orderCode = sprintf('ME-COM%s-%d', date('y'), $contract->id);
            $contract->save();
        }

        $this->auditService->register('CONTRACT', $contract->id, 'CREATE', json_encode($contract));

        return response()->json($contract, 201);
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $contract->update($request->only([
            'deliveryTerms', 'suppliesNote', 'customerPurchaseOrder',
            'modeOfPayment', 'qualityCheck', 'packing', 'remarks', 'loadingDate'
        ]));

        $this->auditService->register('CONTRACT', $contract->id, 'UPDATE', json_encode($contract));

        return response()->json($contract);
    }

    public function move(Request $request, $contractId, $stageId)
    {
        try {
            $this->actionService->move((int) $contractId, (int) $stageId, $request->user()?->id);
            return response()->json('OK', 200);
        } catch (\Throwable $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            return response()->json($e->getMessage(), $code);
        }
    }

    public function associateContract($dealProductId, $purchaseContractId)
    {
        $dp = $this->associationService->associate((int) $dealProductId, (int) $purchaseContractId);
        return response()->json($dp);
    }

    public function listContractsForAssociation($productId)
    {
        $contracts = Contract::with(['purchase.products'])
            ->where('type', 'BUY')
            ->where('contractStatus', 'ACTIVE')
            ->whereHas('purchase.products', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->get();

        return response()->json($contracts);
    }

    public function cancel($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->contractStatus = 'CANCELLED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CANCEL', null);
        return response()->json($contract);
    }

    public function close($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->type === 'BUY') {
            try {
                $this->associationService->verifyPurchaseContractSaleAssociations($contract->id);
            } catch (\Throwable $e) {
                return response()->json($e->getMessage(), 409);
            }
        }

        $contract->contractStatus = 'CLOSED';
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'CLOSE', null);
        return response()->json($contract);
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->active = false;
        $contract->save();

        $this->auditService->register('CONTRACT', $contract->id, 'DELETE', null);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
""")

# 4. CompanyController
write_file(os.path.join(api_dir, 'CompanyController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Archive;
use Illuminate\Http\Request;

class CompanyController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Company::with(['address.country', 'bankAccounts', 'representatives'])
            ->where('active', true)
            ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $company = Company::with(['address.country', 'bankAccounts', 'representatives'])->findOrFail($id);
        return response()->json($company);
    }

    public function store(Request $request)
    {
        $company = Company::create($request->all());
        return response()->json($company, 201);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->update($request->all());
        return response()->json($company);
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->active = false;
        $company->save();
        return response()->json(['message' => 'Company deleted']);
    }

    public function uploadLogo(Request $request, $companyId)
    {
        $company = Company::findOrFail($companyId);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('companies/logos', 'public');
            $company->logoUrl = '/storage/' . $path;
            
            // Save to archive blob
            $archive = Archive::create([
                'fileName' => $file->getClientOriginalName(),
                'file' => base64_encode(file_get_contents($file->getRealPath())),
            ]);
            $company->archiveLogoId = $archive->id;
            $company->save();

            return response()->json(['message' => 'Logo uploaded successfully', 'url' => $company->logoUrl]);
        }
        return response()->json(['error' => 'No file provided'], 400);
    }

    public function uploadFooter(Request $request, $companyId)
    {
        $company = Company::findOrFail($companyId);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('companies/footers', 'public');
            $company->footer = '/storage/' . $path;
            
            $archive = Archive::create([
                'fileName' => $file->getClientOriginalName(),
                'file' => base64_encode(file_get_contents($file->getRealPath())),
            ]);
            $company->archiveFooterId = $archive->id;
            $company->save();

            return response()->json(['message' => 'Footer uploaded successfully', 'url' => $company->footer]);
        }
        return response()->json(['error' => 'No file provided'], 400);
    }

    public function uploadStamp(Request $request, $companyId)
    {
        $company = Company::findOrFail($companyId);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $archive = Archive::create([
                'fileName' => $file->getClientOriginalName(),
                'file' => base64_encode(file_get_contents($file->getRealPath())),
            ]);
            $company->archiveStampId = $archive->id;
            $company->save();

            return response()->json(['message' => 'Stamp uploaded successfully']);
        }
        return response()->json(['error' => 'No file provided'], 400);
    }
}
""")

# 5. ContactController
write_file(os.path.join(api_dir, 'ContactController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $filter = $request->input('filter');
        $name = $request->input('name');

        $query = Contact::with(['types', 'products', 'addresses.country', 'representatives', 'bankAccounts', 'currency', 'language'])
            ->where('active', true);

        if ($filter) {
            $query->where(function($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%")
                  ->orWhere('codeMeta', 'like', "%{$filter}%")
                  ->orWhere('vat', 'like', "%{$filter}%");
            });
        }

        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }

        $paginator = $query->orderBy('name')->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $contact = Contact::with(['types', 'products', 'addresses.country', 'representatives', 'bankAccounts', 'currency', 'language'])->findOrFail($id);
        return response()->json($contact);
    }

    public function store(Request $request)
    {
        $contact = Contact::create($request->all());
        if ($request->has('typeIds')) {
            $contact->types()->sync($request->input('typeIds'));
        }
        if ($request->has('productIds')) {
            $contact->products()->sync($request->input('productIds'));
        }
        return response()->json($contact, 201);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update($request->all());
        if ($request->has('typeIds')) {
            $contact->types()->sync($request->input('typeIds'));
        }
        if ($request->has('productIds')) {
            $contact->products()->sync($request->input('productIds'));
        }
        return response()->json($contact);
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->active = false;
        $contact->save();
        return response()->json(['message' => 'Contact deleted']);
    }
}
""")

# 6. ProductController
write_file(os.path.join(api_dir, 'ProductController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Product::where('active', true)->orderBy('name')->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->active = false;
        $product->save();
        return response()->json(['message' => 'Product deleted']);
    }
}
""")

# 7. OfferController
write_file(os.path.join(api_dir, 'OfferController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Offer::with(['metaCompany', 'contact', 'product', 'currency'])
            ->orderBy('id', 'desc')
            ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $offer = Offer::with(['metaCompany', 'contact', 'product', 'currency'])->findOrFail($id);
        return response()->json($offer);
    }

    public function store(Request $request)
    {
        $offer = Offer::create($request->all());
        return response()->json($offer, 201);
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);
        $offer->update($request->all());
        return response()->json($offer);
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();
        return response()->json(['message' => 'Offer deleted']);
    }
}
""")

# 8. ModelController
write_file(os.path.join(api_dir, 'ModelController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\ModelWorkflow;
use App\Models\Contract;
use Illuminate\Http\Request;

class ModelController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = ModelWorkflow::with(['stages'])->where('active', true)->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $model = ModelWorkflow::with(['stages.stageActions'])->findOrFail($id);
        return response()->json($model);
    }

    public function withPermissions(Request $request, $id)
    {
        $model = ModelWorkflow::with(['stages'])->findOrFail($id);
        $contracts = Contract::with([
            'purchase.metaCompany', 'purchase.contact', 'purchase.products.product',
            'sale.metaCompany', 'sale.contact', 'sale.products.product',
            'checklists.checkitems', 'comments'
        ])
        ->where('active', true)
        ->where('contractStatus', '!=', 'CANCELLED')
        ->get();

        // Build Board format matching Fuse React Scrumboard
        $lists = [];
        $cards = [];

        foreach ($model->stages as $stage) {
            $stageContractIds = [];
            foreach ($contracts as $c) {
                if ($c->stage_id == $stage->id) {
                    $stageContractIds[] = (string) $c->id;
                    $deal = $c->purchase ?: $c->sale;
                    $partnerName = $deal?->contact?->name ?? 'Unknown Partner';
                    $total = $deal?->total ? number_format($deal->total, 2) : '0.00';

                    $cards[] = [
                        'id' => (string) $c->id,
                        'name' => $c->orderCode ?: "Contract #{$c->id}",
                        'description' => "{$c->type} - {$partnerName} ({$total})",
                        'orderCode' => $c->orderCode,
                        'type' => $c->type,
                        'contract' => $c,
                        'stageContractId' => $stage->id,
                        'idList' => (string) $stage->id,
                        'attachments' => [],
                        'checklists' => $c->checklists,
                        'comments' => $c->comments,
                        'due' => $c->loadingDate ? $c->loadingDate->timestamp * 1000 : null,
                    ];
                }
            }

            $lists[] = [
                'id' => (string) $stage->id,
                'name' => $stage->name,
                'title' => $stage->name,
                'idCards' => $stageContractIds,
            ];
        }

        return response()->json([
            'id' => (string) $model->id,
            'name' => $model->name,
            'title' => $model->name,
            'lists' => $lists,
            'cards' => $cards,
        ]);
    }

    public function store(Request $request)
    {
        $model = ModelWorkflow::create($request->all());
        return response()->json($model, 201);
    }

    public function update(Request $request, $id)
    {
        $model = ModelWorkflow::findOrFail($id);
        $model->update($request->all());
        return response()->json($model);
    }

    public function destroy($id)
    {
        $model = ModelWorkflow::findOrFail($id);
        $model->active = false;
        $model->save();
        return response()->json(['message' => 'Model deleted']);
    }
}
""")

# 9. PackingListController & DocumentController
write_file(os.path.join(api_dir, 'PackingListController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\PackingList;
use App\Models\Pack;
use Illuminate\Http\Request;

class PackingListController extends BaseApiController
{
    public function index(Request $request)
    {
        $contractId = $request->input('contractId');
        $query = PackingList::with(['packs.packType']);
        if ($contractId) {
            $query->where('contract_id', $contractId);
        }
        return response()->json($query->get());
    }

    public function show($id)
    {
        $pl = PackingList::with(['packs.packType'])->findOrFail($id);
        return response()->json($pl);
    }

    public function store(Request $request)
    {
        $pl = PackingList::create($request->all());
        return response()->json($pl, 201);
    }

    public function update(Request $request, $id)
    {
        $pl = PackingList::findOrFail($id);
        $pl->update($request->all());
        return response()->json($pl);
    }

    public function destroy($id)
    {
        $pl = PackingList::findOrFail($id);
        $pl->delete();
        return response()->json(['message' => 'Packing list deleted']);
    }

    public function addPack(Request $request, $id)
    {
        $pl = PackingList::findOrFail($id);
        $pack = Pack::create(array_merge($request->all(), ['packing_list_id' => $pl->id]));
        return response()->json($pack, 201);
    }

    public function removePack($id, $packId)
    {
        $pack = Pack::where('packing_list_id', $id)->where('id', $packId)->firstOrFail();
        $pack->delete();
        return response()->json(['message' => 'Pack deleted']);
    }
}
""")

write_file(os.path.join(api_dir, 'DocumentController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Models\Template;
use App\Models\GeneratedFile;
use App\Services\DocumentTemplateService;
use Illuminate\Http\Request;

class DocumentController extends BaseApiController
{
    protected DocumentTemplateService $docService;

    public function __construct(DocumentTemplateService $docService)
    {
        $this->docService = $docService;
    }

    public function regenerate(Request $request)
    {
        $generatedFileId = $request->input('generatedFileId');
        $contractId = $request->input('contractId');

        $file = GeneratedFile::findOrFail($generatedFileId);
        $contract = Contract::findOrFail($contractId);
        $template = $file->template ?: Template::where('type', $file->fileType)->first();

        if (!$template) {
            return response()->json('Template not found', 404);
        }

        try {
            $newFile = $this->docService->render($template, $contract, $request->user()?->id);
            return response()->json('OK', 200);
        } catch (\Throwable $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            return response()->json($e->getMessage(), $code);
        }
    }

    public function generatePackingListRelatedDocuments(Request $request)
    {
        $contractId = $request->input('contractId');
        $contract = Contract::findOrFail($contractId);

        $templates = Template::whereIn('type', ['PACKING_LIST', 'COMMERCIAL_INVOICE'])->get();
        foreach ($templates as $t) {
            try {
                $this->docService->render($t, $contract, $request->user()?->id);
            } catch (\Throwable $e) {
                // Log and continue
            }
        }

        return response()->json('OK', 200);
    }
}
""")

# 10. GeneratedFileController
write_file(os.path.join(api_dir, 'GeneratedFileController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\GeneratedFile;
use Illuminate\Http\Request;

class GeneratedFileController extends BaseApiController
{
    public function index(Request $request)
    {
        $contractId = $request->input('contractId');
        $query = GeneratedFile::with(['template', 'user']);
        if ($contractId) {
            $query->where('contract_id', $contractId);
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function show($id)
    {
        $file = GeneratedFile::with(['template', 'user'])->findOrFail($id);
        return response()->json($file);
    }

    public function getHtml($id)
    {
        $file = GeneratedFile::findOrFail($id);
        return response($file->html, 200)->header('Content-Type', 'text/html');
    }

    public function update(Request $request, $id)
    {
        $file = GeneratedFile::findOrFail($id);
        if ($request->has('html')) {
            $file->html = $request->input('html');
            $file->save();
        }
        return response()->json($file);
    }

    public function destroy($id)
    {
        $file = GeneratedFile::findOrFail($id);
        $file->delete();
        return response()->json(['message' => 'Generated file deleted']);
    }
}
""")

# 11. UserController & AccessGroupController
write_file(os.path.join(api_dir, 'UserController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Archive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 1000);
        $paginator = User::with(['permissions.accessGroup', 'permissions.company'])
            ->orderBy('name')
            ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $user = User::with(['permissions.accessGroup.accessList', 'permissions.company'])->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:191|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'displayName' => $request->input('displayName', $validated['name']),
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'occupation' => $request->input('occupation'),
            'role' => $request->input('role', 'ROLE_USER'),
            'active' => $request->input('active', true),
            'secret' => app(\App\Services\TotpService::class)->generateSecret(),
            'enable2fa' => $request->input('enable2fa', false),
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->only(['name', 'displayName', 'occupation', 'role', 'active', 'enable2fa']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->active = false;
        $user->save();
        return response()->json(['message' => 'User deactivated']);
    }

    public function uploadSignature(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            $b64 = base64_encode($content);

            $archive = Archive::create([
                'fileName' => $file->getClientOriginalName(),
                'file' => $b64,
            ]);

            $user->signatureFile = $b64;
            $user->signature_file_name = $file->getClientOriginalName();
            $user->archiveSignatureId = $archive->id;
            $user->save();

            return response()->json(['message' => 'Signature uploaded successfully']);
        }
        return response()->json(['error' => 'No signature file provided'], 400);
    }

    public function downloadSignature($userId)
    {
        $user = User::findOrFail($userId);
        if (empty($user->signatureFile)) {
            return response()->json(['error' => 'No signature on file'], 404);
        }

        $binary = base64_decode($user->signatureFile);
        return response($binary, 200)->header('Content-Type', 'image/png');
    }
}
""")

write_file(os.path.join(api_dir, 'AccessGroupController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\AccessGroup;
use App\Models\Access;
use Illuminate\Http\Request;

class AccessGroupController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = AccessGroup::with(['accessList'])
            ->where('active', true)
            ->paginate($size);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $ag = AccessGroup::with(['accessList'])->findOrFail($id);
        return response()->json($ag);
    }

    public function store(Request $request)
    {
        $ag = AccessGroup::create(['name' => $request->input('name'), 'active' => true]);
        if ($request->has('accessList')) {
            foreach ($request->input('accessList') as $acc) {
                Access::create([
                    'accessgroup_id' => $ag->id,
                    'module' => $acc['module'],
                    'accessType' => $acc['accessType'],
                    'active' => true,
                ]);
            }
        }
        return response()->json($ag, 201);
    }

    public function update(Request $request, $id)
    {
        $ag = AccessGroup::findOrFail($id);
        $ag->update(['name' => $request->input('name')]);

        if ($request->has('accessList')) {
            $ag->accessList()->delete();
            foreach ($request->input('accessList') as $acc) {
                Access::create([
                    'accessgroup_id' => $ag->id,
                    'module' => $acc['module'],
                    'accessType' => $acc['accessType'],
                    'active' => true,
                ]);
            }
        }

        return response()->json($ag);
    }

    public function destroy($id)
    {
        $ag = AccessGroup::findOrFail($id);
        $ag->active = false;
        $ag->save();
        return response()->json(['message' => 'Access group deactivated']);
    }
}
""")

# 12. StageController & TemplateController
write_file(os.path.join(api_dir, 'StageController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 50);
        $paginator = Stage::with(['stageActions.action'])->where('active', true)->orderBy('priority')->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $stage = Stage::with(['stageActions.action'])->findOrFail($id);
        return response()->json($stage);
    }

    public function store(Request $request)
    {
        $stage = Stage::create($request->all());
        return response()->json($stage, 201);
    }

    public function update(Request $request, $id)
    {
        $stage = Stage::findOrFail($id);
        $stage->update($request->all());
        return response()->json($stage);
    }

    public function destroy($id)
    {
        $stage = Stage::findOrFail($id);
        $stage->active = false;
        $stage->save();
        return response()->json(['message' => 'Stage deleted']);
    }
}
""")

write_file(os.path.join(api_dir, 'TemplateController.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends BaseApiController
{
    public function index()
    {
        return response()->json(Template::with('language')->get());
    }

    public function show($id)
    {
        return response()->json(Template::with('language')->findOrFail($id));
    }

    public function findByTypeAndLanguage($type, $lang)
    {
        $t = Template::where('type', strtoupper($type))
            ->where(function($q) use ($lang) {
                $q->where('language_id', $lang)
                  ->orWhereHas('language', function($lq) use ($lang) {
                      $lq->where('code', $lang);
                  });
            })
            ->first();

        if (!$t) {
            $t = Template::where('type', strtoupper($type))->first();
        }

        return $t ? response()->json($t) : response()->json(['error' => 'Template not found'], 404);
    }

    public function store(Request $request)
    {
        $template = Template::create($request->all());
        return response()->json($template, 201);
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        $template->update($request->all());
        return response()->json($template);
    }

    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        $template->delete();
        return response()->json(['message' => 'Template deleted']);
    }
}
""")

# 13. SystemLookupsController (Countries, Currencies, Departments, PackTypes, PaymentTypes, PaymentTermsTypes, CreditDebitTypes, Types, Languages)
write_file(os.path.join(api_dir, 'LookupControllers.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Department;
use App\Models\PackType;
use App\Models\PaymentType;
use App\Models\PaymentTermsType;
use App\Models\CreditDebitType;
use App\Models\Type;
use App\Models\Language;
use Illuminate\Http\Request;

class CountryController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 300);
        $paginator = Country::where('active', true)->orderBy('name')->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Country::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Country::create($request->all()), 201); }
    public function update(Request $request, $id) { $c = Country::findOrFail($id); $c->update($request->all()); return response()->json($c); }
    public function destroy($id) { Country::findOrFail($id)->update(['active' => false]); return response()->json(['message' => 'Deleted']); }
}

class CurrencyController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 100);
        $paginator = Currency::paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Currency::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Currency::create($request->all()), 201); }
    public function update(Request $request, $id) { $c = Currency::findOrFail($id); $c->update($request->all()); return response()->json($c); }
    public function destroy($id) { Currency::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class DepartmentController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 100);
        $paginator = Department::paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Department::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Department::create($request->all()), 201); }
    public function update(Request $request, $id) { $d = Department::findOrFail($id); $d->update($request->all()); return response()->json($d); }
    public function destroy($id) { Department::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class PackTypeController extends BaseApiController
{
    public function index() { return response()->json(PackType::all()); }
    public function show($id) { return response()->json(PackType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PackType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PackType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PackType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class PaymentTypeController extends BaseApiController
{
    public function index() { return response()->json(PaymentType::all()); }
    public function show($id) { return response()->json(PaymentType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PaymentType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PaymentType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PaymentType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class PaymentTermsTypeController extends BaseApiController
{
    public function index() { return response()->json(PaymentTermsType::all()); }
    public function show($id) { return response()->json(PaymentTermsType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PaymentTermsType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PaymentTermsType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PaymentTermsType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class CreditDebitTypeController extends BaseApiController
{
    public function index() { return response()->json(CreditDebitType::all()); }
    public function show($id) { return response()->json(CreditDebitType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(CreditDebitType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = CreditDebitType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { CreditDebitType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class TypeController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 100);
        $paginator = Type::paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }
    public function show($id) { return response()->json(Type::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Type::create($request->all()), 201); }
    public function update(Request $request, $id) { $t = Type::findOrFail($id); $t->update($request->all()); return response()->json($t); }
    public function destroy($id) { Type::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class LanguageController extends BaseApiController
{
    public function index() { return response()->json(Language::all()); }
    public function show($id) { return response()->json(Language::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Language::create($request->all()), 201); }
    public function update(Request $request, $id) { $l = Language::findOrFail($id); $l->update($request->all()); return response()->json($l); }
    public function destroy($id) { Language::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# 14. UtilityControllers (MetalPrice, MetabaseEmbed, TOTP, Search, Log, AppVersion, Files, Reactivation, Notes, Addresses, Accounts)
write_file(os.path.join(api_dir, 'UtilityControllers.php'), r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Contract;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Log;
use App\Models\Address;
use App\Models\BankAccount;
use App\Models\DetachedNote;
use App\Models\DetachedNoteDetail;
use App\Services\MetalPriceService;
use App\Services\MetabaseEmbedService;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MetalPriceController extends BaseApiController
{
    public function index(MetalPriceService $service)
    {
        return response()->json($service->getPrices());
    }
}

class MetabaseEmbedController extends BaseApiController
{
    public function embed(Request $request, MetabaseEmbedService $service)
    {
        $dashboardId = (int) $request->input('dashboardId', 1);
        $url = $service->generateEmbedUrl($dashboardId);
        return response()->json(['url' => $url]);
    }
}

class TOTPController extends BaseApiController
{
    public function getQrCode(Request $request, $id, TotpService $totp)
    {
        $user = User::findOrFail($id);
        $url = $totp->generateQRUrl($user);
        return response()->json(['qrUrl' => $url, 'secret' => $user->secret]);
    }

    public function update(Request $request, $id, TotpService $totp)
    {
        $user = User::findOrFail($id);
        $code = $request->input('code');

        if (!$totp->validate($user, $code)) {
            return response()->json(['error' => 'Invalid verification code'], 400);
        }

        $user->enable2fa = true;
        $user->save();

        return response()->json(['message' => '2FA verified and enabled successfully']);
    }
}

class SearchController extends BaseApiController
{
    public function search(Request $request, $text)
    {
        $contracts = Contract::with(['purchase.contact', 'sale.contact'])
            ->where('orderCode', 'like', "%{$text}%")
            ->limit(10)
            ->get();

        $contacts = Contact::where('name', 'like', "%{$text}%")
            ->orWhere('codeMeta', 'like', "%{$text}%")
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

class LogController extends BaseApiController
{
    public function listByModuleAndDataId($module, $dataId)
    {
        $logs = Log::with('user')
            ->where('module', strtoupper($module))
            ->where('dataId', $dataId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($logs);
    }
}

class ApplicationController extends BaseApiController
{
    public function getVersion()
    {
        return response()->json(['version' => '1.15.0', 'app' => 'MetaERP']);
    }
}

class FilesController extends BaseApiController
{
    public function show($filename)
    {
        if (Storage::disk('public')->exists($filename)) {
            return Storage::disk('public')->response($filename);
        }
        return response()->json(['error' => 'File not found'], 404);
    }
}

class ContractReactivationController extends BaseApiController
{
    public function reactivate($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->contractStatus = 'ACTIVE';
        $contract->save();

        app(\App\Services\AuditLogService::class)->register('CONTRACT', $contract->id, 'REACTIVATE', null);
        return response()->json($contract);
    }
}

class DetachedNoteController extends BaseApiController
{
    public function index()
    {
        return response()->json(DetachedNote::with(['metaCompany', 'contact', 'currency', 'details'])->get());
    }

    public function show($id)
    {
        return response()->json(DetachedNote::with(['metaCompany', 'contact', 'currency', 'details'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $note = DetachedNote::create($request->all());
        return response()->json($note, 201);
    }

    public function update(Request $request, $id)
    {
        $note = DetachedNote::findOrFail($id);
        $note->update($request->all());
        return response()->json($note);
    }

    public function destroy($id)
    {
        $note = DetachedNote::findOrFail($id);
        $note->delete();
        return response()->json(['message' => 'Detached note deleted']);
    }
}

class AddressController extends BaseApiController
{
    public function index() { return response()->json(Address::with('country')->get()); }
    public function show($id) { return response()->json(Address::with('country')->findOrFail($id)); }
    public function store(Request $request) { return response()->json(Address::create($request->all()), 201); }
    public function update(Request $request, $id) { $a = Address::findOrFail($id); $a->update($request->all()); return response()->json($a); }
    public function destroy($id) { Address::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}

class BankAccountController extends BaseApiController
{
    public function index() { return response()->json(BankAccount::all()); }
    public function show($id) { return response()->json(BankAccount::findOrFail($id)); }
    public function store(Request $request) { return response()->json(BankAccount::create($request->all()), 201); }
    public function update(Request $request, $id) { $b = BankAccount::findOrFail($id); $b->update($request->all()); return response()->json($b); }
    public function destroy($id) { BankAccount::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")
