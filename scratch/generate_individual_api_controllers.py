import os

def write_file(filename, code):
    path = os.path.join(r'c:\Users\navde\Downloads\metaerp\app\Http\Controllers\Api', filename)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(code)
    print("Created:", filename)

# ApplicationController.php
write_file('ApplicationController.php', r"""<?php

namespace App\Http\Controllers\Api;

class ApplicationController extends BaseApiController
{
    public function getVersion()
    {
        return response()->json(['version' => '1.15.0', 'app' => 'MetaERP']);
    }
}
""")

# CountryController.php
write_file('CountryController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
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
""")

# CurrencyController.php
write_file('CurrencyController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use Illuminate\Http\Request;

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
""")

# DepartmentController.php
write_file('DepartmentController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use Illuminate\Http\Request;

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
""")

# PackTypeController.php
write_file('PackTypeController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\PackType;
use Illuminate\Http\Request;

class PackTypeController extends BaseApiController
{
    public function index() { return response()->json(PackType::all()); }
    public function show($id) { return response()->json(PackType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PackType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PackType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PackType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# PaymentTypeController.php
write_file('PaymentTypeController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends BaseApiController
{
    public function index() { return response()->json(PaymentType::all()); }
    public function show($id) { return response()->json(PaymentType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PaymentType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PaymentType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PaymentType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# PaymentTermsTypeController.php
write_file('PaymentTermsTypeController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\PaymentTermsType;
use Illuminate\Http\Request;

class PaymentTermsTypeController extends BaseApiController
{
    public function index() { return response()->json(PaymentTermsType::all()); }
    public function show($id) { return response()->json(PaymentTermsType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(PaymentTermsType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = PaymentTermsType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { PaymentTermsType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# CreditDebitTypeController.php
write_file('CreditDebitTypeController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\CreditDebitType;
use Illuminate\Http\Request;

class CreditDebitTypeController extends BaseApiController
{
    public function index() { return response()->json(CreditDebitType::all()); }
    public function show($id) { return response()->json(CreditDebitType::findOrFail($id)); }
    public function store(Request $request) { return response()->json(CreditDebitType::create($request->all()), 201); }
    public function update(Request $request, $id) { $p = CreditDebitType::findOrFail($id); $p->update($request->all()); return response()->json($p); }
    public function destroy($id) { CreditDebitType::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# TypeController.php
write_file('TypeController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use Illuminate\Http\Request;

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
""")

# LanguageController.php
write_file('LanguageController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends BaseApiController
{
    public function index() { return response()->json(Language::all()); }
    public function show($id) { return response()->json(Language::findOrFail($id)); }
    public function store(Request $request) { return response()->json(Language::create($request->all()), 201); }
    public function update(Request $request, $id) { $l = Language::findOrFail($id); $l->update($request->all()); return response()->json($l); }
    public function destroy($id) { Language::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# MetalPriceController.php
write_file('MetalPriceController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Services\MetalPriceService;

class MetalPriceController extends BaseApiController
{
    public function index(MetalPriceService $service)
    {
        return response()->json($service->getPrices());
    }
}
""")

# MetabaseEmbedController.php
write_file('MetabaseEmbedController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Services\MetabaseEmbedService;
use Illuminate\Http\Request;

class MetabaseEmbedController extends BaseApiController
{
    public function embed(Request $request, MetabaseEmbedService $service)
    {
        $dashboardId = (int) $request->input('dashboardId', 1);
        $url = $service->generateEmbedUrl($dashboardId);
        return response()->json(['url' => $url]);
    }
}
""")

# TOTPController.php
write_file('TOTPController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;

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
""")

# SearchController.php
write_file('SearchController.php', r"""<?php

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
""")

# LogController.php
write_file('LogController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Log;

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
""")

# FilesController.php
write_file('FilesController.php', r"""<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Storage;

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
""")

# ContractReactivationController.php
write_file('ContractReactivationController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;

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
""")

# DetachedNoteController.php
write_file('DetachedNoteController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\DetachedNote;
use Illuminate\Http\Request;

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
""")

# AddressController.php
write_file('AddressController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends BaseApiController
{
    public function index() { return response()->json(Address::with('country')->get()); }
    public function show($id) { return response()->json(Address::with('country')->findOrFail($id)); }
    public function store(Request $request) { return response()->json(Address::create($request->all()), 201); }
    public function update(Request $request, $id) { $a = Address::findOrFail($id); $a->update($request->all()); return response()->json($a); }
    public function destroy($id) { Address::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# BankAccountController.php
write_file('BankAccountController.php', r"""<?php

namespace App\Http\Controllers\Api;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends BaseApiController
{
    public function index() { return response()->json(BankAccount::all()); }
    public function show($id) { return response()->json(BankAccount::findOrFail($id)); }
    public function store(Request $request) { return response()->json(BankAccount::create($request->all()), 201); }
    public function update(Request $request, $id) { $b = BankAccount::findOrFail($id); $b->update($request->all()); return response()->json($b); }
    public function destroy($id) { BankAccount::findOrFail($id)->delete(); return response()->json(['message' => 'Deleted']); }
}
""")

# Remove old multi-class files if they exist
for old in ['LookupControllers.php', 'UtilityControllers.php']:
    p = os.path.join(api_dir, old)
    if os.path.exists(p):
        os.remove(p)
