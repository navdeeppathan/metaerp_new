<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'due_date' => 'datetime',
        'loading_date' => 'datetime',
        'sales_invoice_date' => 'datetime',
        'cancellation_date' => 'datetime',
        'close_date' => 'datetime',
        'transport_price_agreed' => 'double',
        'weight_tolerance' => 'double',
        'version' => 'integer',
    ];

    protected $appends = [
        'orderCode',
        'salesCompany',
        'purchaseCompany',
        'subsidiary',
        'buyerContract',
        'sellerContract',
    ];

    public function getSalesCompanyAttribute()
    {
        return $this->sale?->meta_company_id ?? $this->attributes['meta_company_id'] ?? null;
    }

    public function getPurchaseCompanyAttribute()
    {
        return $this->purchase?->meta_company_id ?? null;
    }

    public function getSubsidiaryAttribute()
    {
        return $this->sale?->meta_company_id ?? $this->purchase?->meta_company_id ?? $this->attributes['meta_company_id'] ?? null;
    }

    public function getBuyerContractAttribute()
    {
        $purchase = $this->relationLoaded('purchase') ? $this->purchase : null;
        if (!$purchase) {
            return null;
        }

        $products = $purchase->relationLoaded('products') ? $purchase->products : collect();
        return [
            'id' => $purchase->id,
            'contactId' => $purchase->contact_id,
            'contactName' => $purchase->contact?->name,
            'paymentStatus' => $purchase->payment_status,
            'productContracts' => $products->map(function ($p) {
                $unitPrice = (float) ($p->unit_price ?: ($p->rate ?: 0));
                $total = (float) ($p->total_price ?: ($p->total ?: ((float) $p->quantity * $unitPrice)));
                return [
                    'id' => $p->id,
                    'totalPrice' => $total,
                    'quantity' => (float) $p->quantity,
                    'unitPrice' => $unitPrice,
                ];
            })->values()->all(),
        ];
    }

    public function getSellerContractAttribute()
    {
        $sale = $this->relationLoaded('sale') ? $this->sale : null;
        if (!$sale) {
            return null;
        }

        $products = $sale->relationLoaded('products') ? $sale->products : collect();
        return [
            'id' => $sale->id,
            'contactId' => $sale->contact_id,
            'contactName' => $sale->contact?->name,
            'paymentStatus' => $sale->payment_status,
            'productContracts' => $products->map(function ($p) {
                $unitPrice = (float) ($p->unit_price ?: ($p->rate ?: 0));
                $total = (float) ($p->total_price ?: ($p->total ?: ((float) $p->quantity * $unitPrice)));
                return [
                    'id' => $p->id,
                    'totalPrice' => $total,
                    'quantity' => (float) $p->quantity,
                    'unitPrice' => $unitPrice,
                ];
            })->values()->all(),
        ];
    }

    public function getOrderCodeAttribute()
    {
        return $this->attributes['order_code'] ?? $this->attributes['orderCode'] ?? null;
    }

    public function setOrderCodeAttribute($val)
    {
        if (array_key_exists('orderCode', $this->attributes)) {
            $this->attributes['orderCode'] = $val;
        } else {
            $this->attributes['order_code'] = $val;
        }
    }

    public function getDeliveryTermsAttribute()
    {
        return $this->attributes['delivery_terms'] ?? $this->attributes['deliveryTerms'] ?? null;
    }

    public function setDeliveryTermsAttribute($val)
    {
        if (array_key_exists('deliveryTerms', $this->attributes)) {
            $this->attributes['deliveryTerms'] = $val;
        } else {
            $this->attributes['delivery_terms'] = $val;
        }
    }

    public function getModeOfPaymentAttribute()
    {
        return $this->attributes['mode_of_payment'] ?? $this->attributes['modeOfPayment'] ?? null;
    }

    public function setModeOfPaymentAttribute($val)
    {
        if (array_key_exists('modeOfPayment', $this->attributes)) {
            $this->attributes['modeOfPayment'] = $val;
        } else {
            $this->attributes['mode_of_payment'] = $val;
        }
    }

    public function getDateAttribute()
    {
        $val = $this->attributes['loading_date'] ?? $this->attributes['loadingDate'] ?? $this->attributes['date'] ?? $this->attributes['due_date'] ?? null;
        return $val ? Carbon::parse($val) : null;
    }

    public function setDateAttribute($val)
    {
        if (array_key_exists('date', $this->attributes)) {
            $this->attributes['date'] = $val;
        } else {
            $this->attributes['loading_date'] = $val;
        }
    }

    public function getSalesInvoiceNumberAttribute()
    {
        return $this->attributes['sales_invoice_number'] ?? $this->attributes['salesInvoiceNumber'] ?? null;
    }

    public function setSalesInvoiceNumberAttribute($val)
    {
        if (array_key_exists('salesInvoiceNumber', $this->attributes)) {
            $this->attributes['salesInvoiceNumber'] = $val;
        } else {
            $this->attributes['sales_invoice_number'] = $val;
        }
    }

    public function getSalesInvoiceDateAttribute()
    {
        $val = $this->attributes['sales_invoice_date'] ?? $this->attributes['salesInvoiceDate'] ?? null;
        return $val ? Carbon::parse($val) : null;
    }

    public function setSalesInvoiceDateAttribute($val)
    {
        if (array_key_exists('salesInvoiceDate', $this->attributes)) {
            $this->attributes['salesInvoiceDate'] = $val;
        } else {
            $this->attributes['sales_invoice_date'] = $val;
        }
    }

    public function getDueDateAttribute()
    {
        $val = $this->attributes['due_date'] ?? $this->attributes['dueDate'] ?? null;
        return $val ? Carbon::parse($val) : null;
    }

    public function setDueDateAttribute($val)
    {
        if (array_key_exists('dueDate', $this->attributes)) {
            $this->attributes['dueDate'] = $val;
        } else {
            $this->attributes['due_date'] = $val;
        }
    }

    public function getLoadingDateAttribute()
    {
        $val = $this->attributes['loading_date'] ?? $this->attributes['loadingDate'] ?? null;
        return $val ? Carbon::parse($val) : null;
    }

    public function setLoadingDateAttribute($val)
    {
        if (array_key_exists('loadingDate', $this->attributes)) {
            $this->attributes['loadingDate'] = $val;
        } else {
            $this->attributes['loading_date'] = $val;
        }
    }

    public function getWeightToleranceAttribute()
    {
        return $this->attributes['weight_tolerance'] ?? $this->attributes['weightTolerance'] ?? null;
    }

    public function setWeightToleranceAttribute($val)
    {
        if (array_key_exists('weightTolerance', $this->attributes)) {
            $this->attributes['weightTolerance'] = $val;
        } else {
            $this->attributes['weight_tolerance'] = $val;
        }
    }

    public function getCustomerPurchaseOrderAttribute()
    {
        return $this->attributes['customer_purchase_order'] ?? $this->attributes['customerPurchaseOrder'] ?? null;
    }

    public function setCustomerPurchaseOrderAttribute($val)
    {
        if (array_key_exists('customerPurchaseOrder', $this->attributes)) {
            $this->attributes['customerPurchaseOrder'] = $val;
        } else {
            $this->attributes['customer_purchase_order'] = $val;
        }
    }

    public function getQualityCheckAttribute()
    {
        return $this->attributes['quality_check'] ?? $this->attributes['qualityCheck'] ?? null;
    }

    public function setQualityCheckAttribute($val)
    {
        if (array_key_exists('qualityCheck', $this->attributes)) {
            $this->attributes['qualityCheck'] = $val;
        } else {
            $this->attributes['quality_check'] = $val;
        }
    }

    public function getSuppliesNoteAttribute()
    {
        return $this->attributes['supplies_note'] ?? $this->attributes['suppliesNote'] ?? null;
    }

    public function setSuppliesNoteAttribute($val)
    {
        if (array_key_exists('suppliesNote', $this->attributes)) {
            $this->attributes['suppliesNote'] = $val;
        } else {
            $this->attributes['supplies_note'] = $val;
        }
    }

    public function purchase()
    {
        return $this->belongsTo(Deal::class, 'purchase_id');
    }

    public function sale()
    {
        return $this->belongsTo(Deal::class, 'sale_id');
    }

    public function associatedSales()
    {
        return $this->hasMany(Contract::class, 'purchase_id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function getDealsAttribute()
    {
        return collect([$this->purchase, $this->sale])->filter()->values();
    }

    public function packingLists()
    {
        return $this->hasMany(PackingList::class, 'contract_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'contract_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'contract_id');
    }

    public function detachedNotes()
    {
        return $this->hasMany(DetachedNote::class, 'contract_id');
    }

    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class, 'contract_id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'contract_id');
    }

    public function getMetaCompanyAttribute()
    {
        return $this->sale?->metaCompany ?? $this->purchase?->metaCompany;
    }

    public function metaCompany()
    {
        // Safe relationship fallback through sale/purchase deal
        return $this->belongsTo(Company::class, 'purchase_id', 'id')->withDefault(function () {
            return $this->sale?->metaCompany ?? $this->purchase?->metaCompany;
        });
    }
}
