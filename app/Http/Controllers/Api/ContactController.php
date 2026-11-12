<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $filter = $request->input('filter');
        $name = $request->input('name');

        $query = Contact::with(['products', 'addresses.country', 'addresses.representatives', 'language', 'types'])
            ->where('active', true);

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%")
                  ->orWhere('code_meta', 'like', "%{$filter}%")
                  ->orWhere('vat', 'like', "%{$filter}%");
            });
        }

        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }

        $paginator = $query->orderBy('name')->paginate($size);

        $transformed = $paginator->getCollection()->map(function ($c) {
            return $this->formatContactDto($c);
        });
        $paginator->setCollection($transformed);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $contact = Contact::with(['products', 'addresses.country', 'addresses.representatives', 'language', 'types'])->findOrFail($id);
        return response()->json($this->formatContactDto($contact));
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $contact = new Contact();
            $this->applyContactFields($contact, $request->all());
            $contact->active = true;
            $contact->save();

            $this->syncRelations($contact, $request->all());

            return response()->json($this->formatContactDto($contact->fresh(['products', 'addresses.country', 'addresses.representatives', 'language', 'types'])), 201);
        });
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $contact = Contact::findOrFail($id);
            $this->applyContactFields($contact, $request->all());
            $contact->save();

            $this->syncRelations($contact, $request->all());

            return response()->json($this->formatContactDto($contact->fresh(['products', 'addresses.country', 'addresses.representatives', 'language', 'types'])));
        });
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->active = false;
        $contact->save();
        return response()->json(['message' => 'Contact deleted']);
    }

    private function applyContactFields(Contact $contact, array $data): void
    {
        if (isset($data['name'])) $contact->name = $data['name'];
        if (isset($data['initials'])) $contact->initials = $data['initials'];
        if (isset($data['vat'])) $contact->vat = $data['vat'];
        if (isset($data['registration'])) $contact->registration = $data['registration'];
        if (isset($data['currency'])) $contact->currency = $data['currency'];
        if (isset($data['website'])) $contact->website = $data['website'];
        if (isset($data['active'])) $contact->active = (bool) $data['active'];

        if (isset($data['codeMeta']) || isset($data['code_meta'])) {
            $contact->code_meta = $data['codeMeta'] ?? $data['code_meta'];
        }
        if (isset($data['companyId']) || isset($data['company_id'])) {
            $contact->company_id = $data['companyId'] ?? $data['company_id'];
        }
        if (isset($data['countryId']) || isset($data['country_id'])) {
            $contact->country_id = $data['countryId'] ?? $data['country_id'];
        }
        if (isset($data['languageId']) || isset($data['language_id'])) {
            $contact->language_id = $data['languageId'] ?? $data['language_id'];
        } elseif (isset($data['language']['id'])) {
            $contact->language_id = $data['language']['id'];
        }
        if (isset($data['additionalInformation']) || isset($data['additional_information'])) {
            $contact->additional_information = $data['additionalInformation'] ?? $data['additional_information'];
        }
        if (isset($data['eoriNumber']) || isset($data['eori_number'])) {
            $contact->eori_number = $data['eoriNumber'] ?? $data['eori_number'];
        }
    }

    private function syncRelations(Contact $contact, array $data): void
    {
        // Type IDs
        if (isset($data['typeIds']) && is_array($data['typeIds'])) {
            $contact->types()->sync($data['typeIds']);
        }

        // Product IDs
        if (isset($data['productIds']) && is_array($data['productIds'])) {
            $contact->products()->sync($data['productIds']);
        }

        // Addresses
        if (isset($data['addresses']) && is_array($data['addresses'])) {
            $addressIds = [];
            foreach ($data['addresses'] as $addrData) {
                if (is_numeric($addrData)) {
                    $addressIds[] = (int) $addrData;
                } elseif (isset($addrData['id']) && $addrData['id']) {
                    $addr = Address::find($addrData['id']);
                    if ($addr) {
                        $addr->update([
                            'street' => $addrData['street'] ?? $addr->street,
                            'number' => $addrData['number'] ?? $addr->number,
                            'neighborhood' => $addrData['neighborhood'] ?? $addr->neighborhood,
                            'city' => $addrData['city'] ?? $addr->city,
                            'state' => $addrData['state'] ?? $addr->state,
                            'zip_code' => $addrData['zipCode'] ?? $addrData['zip_code'] ?? $addr->zip_code,
                            'country_id' => $addrData['countryId'] ?? $addrData['country_id'] ?? $addr->country_id,
                        ]);
                        $addressIds[] = $addr->id;
                    }
                } else {
                    $addr = Address::create([
                        'street' => $addrData['street'] ?? '',
                        'number' => $addrData['number'] ?? '',
                        'neighborhood' => $addrData['neighborhood'] ?? '',
                        'city' => $addrData['city'] ?? '',
                        'state' => $addrData['state'] ?? '',
                        'zip_code' => $addrData['zipCode'] ?? $addrData['zip_code'] ?? '',
                        'country_id' => $addrData['countryId'] ?? $addrData['country_id'] ?? null,
                    ]);
                    $addressIds[] = $addr->id;
                }
            }
            if (!empty($addressIds)) {
                $contact->addresses()->sync($addressIds);
            }
        }
    }

    private function formatContactDto(Contact $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'initials' => $c->initials,
            'typeIds' => $c->types->pluck('id')->values(),
            'companyId' => $c->company_id,
            'vat' => $c->vat,
            'registration' => $c->registration,
            'currency' => $c->currency,
            'codeMeta' => $c->code_meta,
            'countryId' => $c->country_id,
            'bankAccounts' => [],
            'addresses' => $c->addresses->map(function ($a) {
                return [
                    'id' => $a->id,
                    'street' => $a->street,
                    'number' => $a->number,
                    'neighborhood' => $a->neighborhood,
                    'city' => $a->city,
                    'state' => $a->state,
                    'zipCode' => $a->zip_code,
                    'countryId' => $a->country_id,
                    'country' => $a->country,
                    'representatives' => $a->representatives,
                ];
            })->values(),
            'productIds' => $c->products->pluck('id')->values(),
            'language' => $c->language,
            'active' => (bool) $c->active,
            'website' => $c->website,
            'additionalInformation' => $c->additional_information,
            'eoriNumber' => $c->eori_number,
        ];
    }
}
