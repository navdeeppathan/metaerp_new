<?php

namespace App\Http\Controllers\Api;

use App\Models\Offer;
use App\Models\Deal;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfferController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Offer::with([
            'purchase.metaCompany', 'purchase.contact',
            'sale.metaCompany', 'sale.contact',
        ])
        ->where('active', true)
        ->orderBy('id', 'desc')
        ->paginate($size);

        $transformed = $paginator->getCollection()->map(function ($o) {
            return $this->formatOfferDto($o);
        });
        $paginator->setCollection($transformed);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $offer = Offer::with([
            'purchase.metaCompany', 'purchase.contact',
            'sale.metaCompany', 'sale.contact',
            'generatedFiles', 'members', 'tags'
        ])->findOrFail($id);

        return response()->json($this->formatOfferDto($offer));
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $offer = new Offer();
            $this->applyOfferFields($offer, $request->all());
            $offer->active = true;
            $offer->version = 1;
            $offer->status = $request->input('status', 'OPEN');

            // Handle Deals
            $type = $request->input('type', 'BUY');
            $offer->type = $type;

            if (in_array($type, ['BUY', 'BUY_AND_SELL']) && $request->filled('purchase')) {
                $pDeal = $this->createOrUpdateDeal(null, $request->input('purchase'));
                $offer->purchase_id = $pDeal->id;
            }

            if (in_array($type, ['SELL', 'BUY_AND_SELL']) && $request->filled('sale')) {
                $sDeal = $this->createOrUpdateDeal(null, $request->input('sale'));
                $offer->sale_id = $sDeal->id;
            }

            $offer->save();

            if (!$offer->order_code) {
                $offer->order_code = sprintf('OFFER-%s-%d', date('y'), $offer->id);
                $offer->save();
            }

            return response()->json($this->formatOfferDto($offer->fresh([
                'purchase.metaCompany', 'purchase.contact',
                'sale.metaCompany', 'sale.contact', 'members', 'tags'
            ])), 201);
        });
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $offer = Offer::findOrFail($id);
            $this->applyOfferFields($offer, $request->all());

            if ($request->has('purchase') && $offer->purchase_id) {
                $this->createOrUpdateDeal($offer->purchase_id, $request->input('purchase'));
            }
            if ($request->has('sale') && $offer->sale_id) {
                $this->createOrUpdateDeal($offer->sale_id, $request->input('sale'));
            }

            $offer->save();

            return response()->json($this->formatOfferDto($offer->fresh([
                'purchase.metaCompany', 'purchase.contact',
                'sale.metaCompany', 'sale.contact', 'members', 'tags'
            ])));
        });
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->active = false;
        $offer->save();
        return response()->json(['message' => 'Offer deleted']);
    }

    private function applyOfferFields(Offer $offer, array $data): void
    {
        if (isset($data['orderCode']) || isset($data['order_code'])) {
            $offer->order_code = $data['orderCode'] ?? $data['order_code'];
        }
        if (isset($data['description'])) $offer->description = $data['description'];
        if (isset($data['paymentTerms']) || isset($data['payment_terms'])) {
            $offer->payment_terms = $data['paymentTerms'] ?? $data['payment_terms'];
        }
        if (isset($data['currency'])) $offer->currency = $data['currency'];
        if (isset($data['type'])) $offer->type = $data['type'];
        if (isset($data['qualityCheck']) || isset($data['quality_check'])) {
            $offer->quality_check = $data['qualityCheck'] ?? $data['quality_check'];
        }
        if (isset($data['packing'])) $offer->packing = $data['packing'];
        if (isset($data['suppliesNote']) || isset($data['supplies_note'])) {
            $offer->supplies_note = $data['suppliesNote'] ?? $data['supplies_note'];
        }
        if (isset($data['customerPurchaseOrder']) || isset($data['customer_purchase_order'])) {
            $offer->customer_purchase_order = $data['customerPurchaseOrder'] ?? $data['customer_purchase_order'];
        }
        if (isset($data['deliveryTerms']) || isset($data['delivery_terms'])) {
            $offer->delivery_terms = $data['deliveryTerms'] ?? $data['delivery_terms'];
        }
        if (isset($data['modeOfPayment']) || isset($data['mode_of_payment'])) {
            $offer->mode_of_payment = $data['modeOfPayment'] ?? $data['mode_of_payment'];
        }
        if (isset($data['transportNotes']) || isset($data['transport_notes'])) {
            $offer->transport_notes = $data['transportNotes'] ?? $data['transport_notes'];
        }
        if (isset($data['transportPriceAgreed']) || isset($data['transport_price_agreed'])) {
            $offer->transport_price_agreed = (float) ($data['transportPriceAgreed'] ?? $data['transport_price_agreed']);
        }
        if (isset($data['transportSpecialNotes']) || isset($data['transport_special_notes'])) {
            $offer->transport_special_notes = $data['transportSpecialNotes'] ?? $data['transport_special_notes'];
        }
        if (isset($data['cancellationReason']) || isset($data['cancellation_reason'])) {
            $offer->cancellation_reason = $data['cancellationReason'] ?? $data['cancellation_reason'];
        }
        if (isset($data['internalNotes']) || isset($data['internal_notes'])) {
            $offer->internal_notes = $data['internalNotes'] ?? $data['internal_notes'];
        }
        if (isset($data['deliveryPlan']) || isset($data['delivery_plan'])) {
            $offer->delivery_plan = $data['deliveryPlan'] ?? $data['delivery_plan'];
        }
        if (isset($data['quotationalPeriod']) || isset($data['quotational_period'])) {
            $offer->quotational_period = $data['quotationalPeriod'] ?? $data['quotational_period'];
        }
        if (isset($data['weights'])) $offer->weights = $data['weights'];
        if (isset($data['paymentDescription']) || isset($data['payment_description'])) {
            $offer->payment_description = $data['paymentDescription'] ?? $data['payment_description'];
        }
        if (isset($data['terms'])) $offer->terms = $data['terms'];
        if (isset($data['extraInformation']) || isset($data['extra_information'])) {
            $offer->extra_information = $data['extraInformation'] ?? $data['extra_information'];
        }
        if (isset($data['subject'])) $offer->subject = $data['subject'];
        if (isset($data['status'])) $offer->status = $data['status'];

        if (isset($data['dueDate']) || isset($data['due_date'])) {
            $offer->due_date = $this->parseTimestamp($data['dueDate'] ?? $data['due_date']);
        }
        if (isset($data['loadingDate']) || isset($data['loading_date'])) {
            $offer->loading_date = $this->parseTimestamp($data['loadingDate'] ?? $data['loading_date']);
        }
    }

    private function createOrUpdateDeal(?int $dealId, array $data): Deal
    {
        $deal = $dealId ? Deal::find($dealId) : new Deal();
        if (!$deal) $deal = new Deal();

        if (isset($data['metaCompany']['id']) || isset($data['meta_company_id'])) {
            $deal->meta_company_id = $data['metaCompany']['id'] ?? $data['meta_company_id'];
        }
        if (isset($data['contact']['id']) || isset($data['contact_id'])) {
            $deal->contact_id = $data['contact']['id'] ?? $data['contact_id'];
        }
        if (isset($data['currency']['id']) || isset($data['currency_id'])) {
            $deal->currency_id = $data['currency']['id'] ?? $data['currency_id'];
        }
        if (isset($data['paymentType']['id']) || isset($data['payment_type_id'])) {
            $deal->payment_type_id = $data['paymentType']['id'] ?? $data['payment_type_id'];
        }
        if (isset($data['paymentTermsType']['id']) || isset($data['payment_terms_type_id'])) {
            $deal->payment_terms_type_id = $data['paymentTermsType']['id'] ?? $data['payment_terms_type_id'];
        }
        if (isset($data['paymentTerms']) || isset($data['payment_terms'])) {
            $deal->payment_terms = $data['paymentTerms'] ?? $data['payment_terms'];
        }
        if (isset($data['incoterms'])) $deal->incoterms = $data['incoterms'];
        if (isset($data['notes'])) $deal->notes = $data['notes'];

        $deal->save();
        return $deal;
    }

    private function parseTimestamp($value): ?Carbon
    {
        if (!$value) return null;
        if (is_numeric($value)) {
            if ($value > 9999999999) return Carbon::createFromTimestampMs($value);
            return Carbon::createFromTimestamp($value);
        }
        return Carbon::parse($value);
    }

    private function formatOfferDto(Offer $o): array
    {
        return [
            'id' => $o->id,
            'orderCode' => $o->order_code,
            'description' => $o->description,
            'paymentTerms' => $o->payment_terms,
            'salesInvoiceNumber' => $o->sales_invoice_number,
            'salesInvoiceDate' => $o->sales_invoice_date ? $o->sales_invoice_date->timestamp : null,
            'salesOrder' => $o->sales_order,
            'currency' => $o->currency,
            'type' => $o->type,
            'dueDate' => $o->due_date ? $o->due_date->timestamp : null,
            'tags' => $o->tags->pluck('tag')->values(),
            'qualityCheck' => $o->quality_check,
            'packing' => $o->packing,
            'suppliesNote' => $o->supplies_note,
            'customerPurchaseOrder' => $o->customer_purchase_order,
            'deliveryTerms' => $o->delivery_terms,
            'modeOfPayment' => $o->mode_of_payment,
            'generatedFiles' => $o->generatedFiles,
            'memberIds' => $o->members->pluck('id')->values(),
            'active' => (bool) $o->active,
            'version' => (int) ($o->version ?? 1),
            'transportNotes' => $o->transport_notes,
            'transportPriceAgreed' => (float) ($o->transport_price_agreed ?? 0.0),
            'transportSpecialNotes' => $o->transport_special_notes,
            'loadingDate' => $o->loading_date ? $o->loading_date->timestamp : null,
            'purchase' => $o->purchase,
            'sale' => $o->sale,
            'status' => $o->status,
            'cancellationReason' => $o->cancellation_reason,
            'internalNotes' => $o->internal_notes,
            'deliveryPlan' => $o->delivery_plan,
            'quotationalPeriod' => $o->quotational_period,
            'weights' => $o->weights,
            'paymentDescription' => $o->payment_description,
            'terms' => $o->terms,
            'extraInformation' => $o->extra_information,
            'subject' => $o->subject,
            'contractIdWhenPromoted' => $o->contract_id_when_promoted,
            'contractOrderCodeWhenPromoted' => $o->contract_order_code_when_promoted,
        ];
    }
}
