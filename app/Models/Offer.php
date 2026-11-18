<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offer';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'cancellation_date',
        'cancellation_reason',
        'close_date',
        'currency',
        'customer_purchase_order',
        'delivery_plan',
        'delivery_terms',
        'description',
        'due_date',
        'extra_information',
        'internal_notes',
        'loading_date',
        'mode_of_payment',
        'order_code',
        'packing',
        'payment_description',
        'payment_terms',
        'quality_check',
        'quotational_period',
        'sales_invoice_date',
        'sales_invoice_number',
        'sales_order',
        'status',
        'subject',
        'supplies_note',
        'terms',
        'transport_notes',
        'transport_price_agreed',
        'transport_special_notes',
        'type',
        'version',
        'weights',
        'purchase_id',
        'sale_id',
        'contract_id_when_promoted',
        'contract_order_code_when_promoted',
    ];

    protected $casts = [
        'active' => 'boolean',
        'cancellation_date' => 'datetime',
        'close_date' => 'datetime',
        'due_date' => 'datetime',
        'loading_date' => 'datetime',
        'sales_invoice_date' => 'datetime',
        'transport_price_agreed' => 'double',
        'version' => 'integer',
    ];

    public function getCodeAttribute()
    {
        return $this->attributes['order_code'] ?? null;
    }

    public function getOrderCodeAttribute()
    {
        return $this->attributes['order_code'] ?? null;
    }

    public function getOfferDateAttribute()
    {
        return $this->due_date;
    }

    public function getAgreeDateAttribute()
    {
        return $this->active_deal?->payment_date;
    }

    public function getContractCodeAttribute()
    {
        return $this->attributes['contract_code'] ?? $this->attributes['contract_order_code_when_promoted'] ?? null;
    }

    public function getAcceptedDateFormattedAttribute()
    {
        $date = $this->accepted_at ?? $this->close_date ?? null;
        if ($date) {
            return \Carbon\Carbon::parse($date)->format('d M y');
        }
        return null;
    }

    public function getActiveDealAttribute()
    {
        if (strtoupper($this->type ?? '') === 'BUY') {
            return $this->purchase;
        }
        return $this->sale ?? $this->purchase;
    }

    public function getMetaCompanyIdAttribute()
    {
        return $this->active_deal?->meta_company_id;
    }

    public function getContactIdAttribute()
    {
        return $this->active_deal?->contact_id;
    }

    public function getAddressIdAttribute()
    {
        return $this->active_deal?->address_id;
    }

    public function getContactRepresentativeIdAttribute()
    {
        return $this->active_deal?->contact_representative_id;
    }

    public function getCurrencyIdAttribute()
    {
        return $this->active_deal?->currency_id;
    }

    public function getIncotermsAttribute()
    {
        return $this->active_deal?->incoterms;
    }

    public function getPaymentTypeIdAttribute()
    {
        return $this->active_deal?->payment_type_id;
    }

    public function getPaymentTermsTypeIdAttribute()
    {
        return $this->active_deal?->payment_terms_type_id;
    }

    public function getEffectiveMetaCompanyIdAttribute()
    {
        return $this->active_deal?->meta_company_id;
    }

    public function getEffectiveContactIdAttribute()
    {
        return $this->active_deal?->contact_id;
    }

    public function getEffectiveAddressIdAttribute()
    {
        return $this->active_deal?->address_id;
    }

    public function getEffectiveContactRepresentativeIdAttribute()
    {
        return $this->active_deal?->contact_representative_id;
    }

    public function getEffectiveCurrencyIdAttribute()
    {
        return $this->active_deal?->currency_id;
    }

    public function getEffectiveIncotermsAttribute()
    {
        return $this->active_deal?->incoterms;
    }

    public function getEffectivePaymentTypeIdAttribute()
    {
        return $this->active_deal?->payment_type_id;
    }

    public function getEffectivePaymentTermsTypeIdAttribute()
    {
        return $this->active_deal?->payment_terms_type_id;
    }

    public function getEffectivePaymentTermsAttribute()
    {
        return $this->active_deal?->payment_terms ?? $this->payment_terms;
    }

    public function getEffectiveAgreeDateAttribute()
    {
        return $this->active_deal?->payment_date ?? $this->agree_date;
    }

    public function getEffectiveProductsListAttribute()
    {
        if ($this->active_deal) {
            // 1. Check deal_products pivot table (as defined in metaerp.sql production schema)
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('deal_products')) {
                    $dpIds = \Illuminate\Support\Facades\DB::table('deal_products')
                        ->where('deal_id', $this->active_deal->id)
                        ->pluck('products_id');
                    if ($dpIds->isNotEmpty()) {
                        $dealProducts = DealProduct::whereIn('id', $dpIds)->with('product')->get();
                        if ($dealProducts->isNotEmpty()) {
                            return $dealProducts->map(function($dp) {
                                return [
                                    'id' => $dp->id,
                                    'product_id' => $dp->product_id,
                                    'item_name' => $dp->product?->name ?? 'Product #' . $dp->product_id,
                                    'quantity' => (float)($dp->quantity ?? 0),
                                    'uom' => $dp->uom ?? $dp->product?->uom ?? $dp->product?->unitOfMeasurement ?? 'MT',
                                    'unit_price' => (float)($dp->rate ?? $dp->unitPrice ?? 0),
                                    'total' => (float)($dp->total_price ?? $dp->total ?? (($dp->quantity ?? 0) * ($dp->rate ?? $dp->unitPrice ?? 0))),
                                    'pricing_type' => $dp->pricing_type ?? $dp->pricingType ?? 'CUSTOM',
                                ];
                            });
                        }
                    }
                }
            } catch (\Throwable $e) {}

            // 2. Direct column on deal_product
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('deal_product', 'deal_id')) {
                    $dealProducts = DealProduct::where('deal_id', $this->active_deal->id)->with('product')->get();
                    if ($dealProducts->isNotEmpty()) {
                        return $dealProducts->map(function($dp) {
                            return [
                                'id' => $dp->id,
                                'product_id' => $dp->product_id,
                                'item_name' => $dp->product?->name ?? 'Product #' . $dp->product_id,
                                'quantity' => (float)($dp->quantity ?? 0),
                                'uom' => $dp->uom ?? $dp->product?->uom ?? $dp->product?->unitOfMeasurement ?? 'MT',
                                'unit_price' => (float)($dp->rate ?? $dp->unitPrice ?? 0),
                                'total' => (float)($dp->total_price ?? $dp->total ?? (($dp->quantity ?? 0) * ($dp->rate ?? $dp->unitPrice ?? 0))),
                                'pricing_type' => $dp->pricing_type ?? $dp->pricingType ?? 'CUSTOM',
                            ];
                        });
                    }
                }
            } catch (\Throwable $e) {}
        }

        if (!empty($this->products_json)) {
            $decoded = is_string($this->products_json) ? json_decode($this->products_json, true) : $this->products_json;
            if (is_array($decoded) && count($decoded) > 0) {
                return collect($decoded)->map(function($p) {
                    return [
                        'id' => $p['id'] ?? 1,
                        'product_id' => $p['product_id'] ?? null,
                        'item_name' => $p['item_name'] ?? 'Product',
                        'quantity' => (float)($p['quantity'] ?? 0),
                        'uom' => $p['uom'] ?? 'MT',
                        'unit_price' => (float)($p['unit_price'] ?? 0),
                        'total' => (float)($p['total'] ?? (($p['quantity'] ?? 0) * ($p['unit_price'] ?? 0))),
                        'pricing_type' => $p['pricing_type'] ?? 'CUSTOM',
                    ];
                });
            }
        }

        if ($this->product_id) {
            $prod = Product::find($this->product_id);
            return collect([[
                'id' => 1,
                'product_id' => $this->product_id,
                'item_name' => $prod?->name ?? 'Product #' . $this->product_id,
                'quantity' => (float)($this->quantity ?? 0),
                'uom' => $prod?->uom ?? $prod?->unitOfMeasurement ?? 'MT',
                'unit_price' => (float)($this->unitPrice ?? $this->rate ?? 0),
                'total' => (float)($this->total ?? (($this->quantity ?? 0) * ($this->unitPrice ?? 0))),
                'pricing_type' => 'CUSTOM',
            ]]);
        }

        return collect();
    }

    public function getMetaCompanyAttribute()
    {
        return $this->active_deal?->metaCompany;
    }

    public function getContactAttribute()
    {
        return $this->active_deal?->contact;
    }

    public function getAddressAttribute()
    {
        return $this->active_deal?->address;
    }

    public function getContactRepresentativeAttribute()
    {
        return $this->active_deal?->contactRepresentative;
    }

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'meta_company_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function contactRepresentative()
    {
        return $this->belongsTo(Representative::class, 'contact_representative_id', 'id');
    }

    public function currencyRel()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'id');
    }

    public function paymentTermsType()
    {
        return $this->belongsTo(PaymentTermsType::class, 'payment_terms_type_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(Deal::class, 'purchase_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo(Deal::class, 'sale_id', 'id');
    }

    public function promotedContract()
    {
        return $this->belongsTo(Contract::class, 'contract_id_when_promoted', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'offer_id', 'id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'offer_id', 'id');
    }

    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class, 'offer_id', 'id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'offer_member_ids', 'offer_id', 'member_ids');
    }

    public function tags()
    {
        return $this->hasMany(OfferTag::class, 'offer_id', 'id');
    }
}
