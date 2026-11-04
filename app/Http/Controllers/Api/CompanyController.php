<?php

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
