<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Archive;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Company::with(['address.country', 'address.representatives', 'bankAccounts', 'country'])
            ->where('active', true)
            ->paginate($size);

        $transformed = $paginator->getCollection()->map(function ($c) {
            return $this->formatCompanyDto($c);
        });
        $paginator->setCollection($transformed);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $company = Company::with(['address.country', 'address.representatives', 'bankAccounts', 'country'])->findOrFail($id);
        return response()->json($this->formatCompanyDto($company));
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $company = new Company();
            $this->applyCompanyFields($company, $request->all());
            $company->active = true;
            $company->save();

            if ($request->has('bankAccounts') && is_array($request->input('bankAccounts'))) {
                $this->syncBankAccounts($company, $request->input('bankAccounts'));
            }

            return response()->json($this->formatCompanyDto($company->fresh(['address.country', 'address.representatives', 'bankAccounts', 'country'])), 201);
        });
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $company = Company::findOrFail($id);
            $this->applyCompanyFields($company, $request->all());
            $company->save();

            if ($request->has('bankAccounts') && is_array($request->input('bankAccounts'))) {
                $this->syncBankAccounts($company, $request->input('bankAccounts'));
            }

            return response()->json($this->formatCompanyDto($company->fresh(['address.country', 'address.representatives', 'bankAccounts', 'country'])));
        });
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
        return $this->handleFileUpload($request, $companyId, 'LOGO');
    }

    public function uploadFooter(Request $request, $companyId)
    {
        return $this->handleFileUpload($request, $companyId, 'FOOTER');
    }

    public function uploadStamp(Request $request, $companyId)
    {
        return $this->handleFileUpload($request, $companyId, 'STAMP');
    }

    private function handleFileUpload(Request $request, $companyId, string $type)
    {
        $company = Company::findOrFail($companyId);
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file provided'], 400);
        }

        $file = $request->file('file');
        $origName = $file->getClientOriginalName();
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        $archive = Archive::create([
            'content' => $base64,
        ]);

        $typeName = strtolower($type);
        if ($type === 'LOGO') {
            $company->logo_file = $base64;
            $company->archive_logo_id = $archive->id;
            $company->logo_name = $origName;
        } elseif ($type === 'FOOTER') {
            $company->footer_file = $base64;
            $company->archive_footer_id = $archive->id;
            $company->footer_name = $origName;
        } elseif ($type === 'STAMP') {
            $company->stamp_file = $base64;
            $company->archive_stamp_id = $archive->id;
            $company->stamp_name = $origName;
        }

        $company->save();

        return response()->json([
            'message' => "Uploaded the {$typeName} file successfully: {$origName}"
        ], 200);
    }

    private function applyCompanyFields(Company $company, array $data): void
    {
        if (isset($data['name'])) $company->name = $data['name'];
        if (isset($data['code'])) $company->code = $data['code'];
        if (isset($data['email'])) $company->email = $data['email'];
        if (isset($data['active'])) $company->active = (bool) $data['active'];

        if (isset($data['countryId']) || isset($data['country_company_id'])) {
            $company->country_company_id = $data['countryId'] ?? $data['country_company_id'];
        }
        if (isset($data['addressId']) || isset($data['address_id'])) {
            $company->address_id = $data['addressId'] ?? $data['address_id'];
        }
        if (isset($data['companyNumber']) || isset($data['company_number'])) {
            $company->company_number = $data['companyNumber'] ?? $data['company_number'];
        }
        if (isset($data['vatNumber']) || isset($data['vat_number'])) {
            $company->vat_number = $data['vatNumber'] ?? $data['vat_number'];
        }
        if (isset($data['phoneNumber']) || isset($data['phone_number'])) {
            $company->phone_number = $data['phoneNumber'] ?? $data['phone_number'];
        }
        if (isset($data['faxNumber']) || isset($data['fax_number'])) {
            $company->fax_number = $data['faxNumber'] ?? $data['fax_number'];
        }
        if (isset($data['webSite']) || isset($data['website'])) {
            $company->website = $data['webSite'] ?? $data['website'];
        }
        if (isset($data['domainName']) || isset($data['domain_name'])) {
            $company->domain_name = $data['domainName'] ?? $data['domain_name'];
        }
        if (isset($data['eoriNumber']) || isset($data['eori_number'])) {
            $company->eori_number = $data['eoriNumber'] ?? $data['eori_number'];
        }
        if (isset($data['logoUrl']) || isset($data['logo_url'])) {
            $company->logo_url = $data['logoUrl'] ?? $data['logo_url'];
        }
    }

    private function syncBankAccounts(Company $company, array $bankAccounts): void
    {
        $ids = [];
        foreach ($bankAccounts as $acc) {
            if (is_numeric($acc)) {
                $ids[] = (int) $acc;
            } elseif (isset($acc['id'])) {
                $ids[] = (int) $acc['id'];
            }
        }
        $company->bankAccounts()->sync($ids);
    }

    private function formatCompanyDto(Company $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'countryId' => $c->country_company_id,
            'logoUrl' => $c->logo_url,
            'logoFile' => $c->logo_file,
            'footerFile' => $c->footer_file,
            'stampFile' => $c->stamp_file,
            'bankAccounts' => $c->bankAccounts,
            'address' => $c->address,
            'companyNumber' => $c->company_number,
            'vatNumber' => $c->vat_number,
            'phoneNumber' => $c->phone_number,
            'faxNumber' => $c->fax_number,
            'email' => $c->email,
            'webSite' => $c->website,
            'domainName' => $c->domain_name,
            'active' => (bool) $c->active,
            'eoriNumber' => $c->eori_number,
            'logoName' => $c->logo_name,
            'footerName' => $c->footer_name,
            'stampName' => $c->stamp_name,
        ];
    }
}
