<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'currency',
        'customer_purchase_order',
        'delivery_terms',
        'description',
        'due_date',
        'loading_date',
        'mode_of_payment',
        'order_code',
        'packing',
        'payment_terms',
        'quality_check',
        'sales_invoice_date',
        'sales_invoice_number',
        'sales_order',
        'stage_contract_id',
        'supplies_note',
        'transport_notes',
        'transport_price_agreed',
        'transport_special_notes',
        'type',
        'version',
        'purchase_id',
        'sale_id',
        'stage_id',
        'cancellation_date',
        'cancellation_reason',
        'close_date',
        'status',
        'mode_of_transport',
        'port_of_delivery',
        'port_of_loading',
        'weight_tolerance',
        'proforma_invoice_number_formatted',
        'commercial_invoice_number_formatted',
    ];

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
    ];

    public function purchase()
    {
        return $this->belongsTo(Deal::class, 'purchase_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo(Deal::class, 'sale_id', 'id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id', 'id');
    }

    public function buyerContract()
    {
        return $this->hasOne(BuyerContract::class, 'contract_id', 'id');
    }

    public function sellerContract()
    {
        return $this->hasOne(SellerContract::class, 'contract_id', 'id');
    }

    public function packingLists()
    {
        return $this->hasMany(PackingList::class, 'contract_id', 'id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'contract_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'contract_id', 'id');
    }

    public function detachedNotes()
    {
        return $this->hasMany(DetachedNote::class, 'contract_id', 'id');
    }

    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class, 'contract_id', 'id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'contract_id', 'id');
    }

    public function dealProducts()
    {
        return $this->hasMany(DealProduct::class, 'associated_contract_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(DealProduct::class, 'associated_contract_id', 'id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'contract_member_ids', 'contract_id', 'member_ids');
    }

    public function tags()
    {
        return $this->hasMany(ContractTag::class, 'contract_id', 'id');
    }

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'purchase_id', 'id')->withDefault(function () {
            return $this->sale?->metaCompany ?? $this->purchase?->metaCompany;
        });
    }

    public function getMetaCompanyAttribute()
    {
        return $this->sale?->metaCompany ?? $this->purchase?->metaCompany;
    }

    public function getPurchaseCompanyAttribute()
    {
        return $this->purchase?->meta_company_id ?? $this->sale?->meta_company_id;
    }

    public function getSalesCompanyAttribute()
    {
        return $this->sale?->meta_company_id ?? $this->purchase?->meta_company_id;
    }

    public function getPurchaseCompanyNameAttribute()
    {
        return $this->purchase?->metaCompany?->name ?? $this->sale?->metaCompany?->name;
    }

    public function getSalesCompanyNameAttribute()
    {
        return $this->sale?->metaCompany?->name ?? $this->purchase?->metaCompany?->name;
    }

    public function getOrderCodeAttribute()
    {
        return $this->attributes['order_code'] ?? null;
    }

    public function setOrderCodeAttribute($value)
    {
        $this->attributes['order_code'] = $value;
    }

    public static function normalizeDateValue($rawDate)
    {
        if (empty($rawDate)) {
            return null;
        }

        if ($rawDate instanceof \Carbon\Carbon || $rawDate instanceof \DateTimeInterface) {
            return $rawDate;
        }

        if (is_numeric($rawDate)) {
            if ($rawDate > 100000000000) {
                $rawDate = (int) round($rawDate / 1000);
            }
            return \Carbon\Carbon::createFromTimestamp($rawDate);
        }

        $rawDate = trim((string) $rawDate);

        $formats = [
            'd/m/Y h:i A',
            'd/m/Y h:i a',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'd-m-Y h:i A',
            'd-m-Y h:i a',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
        ];

        foreach ($formats as $fmt) {
            try {
                return \Carbon\Carbon::createFromFormat($fmt, $rawDate);
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return \Carbon\Carbon::parse($rawDate);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = static::normalizeDateValue($value);
    }

    public function setLoadingDateAttribute($value)
    {
        $this->attributes['loading_date'] = static::normalizeDateValue($value);
    }

    public function setSalesInvoiceDateAttribute($value)
    {
        $this->attributes['sales_invoice_date'] = static::normalizeDateValue($value);
    }

    public function setCloseDateAttribute($value)
    {
        $this->attributes['close_date'] = static::normalizeDateValue($value);
    }

    public function setCancellationDateAttribute($value)
    {
        $this->attributes['cancellation_date'] = static::normalizeDateValue($value);
    }

    public function getFormattedDueDateAttribute()
    {
        if ($this->due_date) {
            return [
                'day_month' => $this->due_date->format('d M'),
                'year' => $this->due_date->format('y'),
                'full' => $this->due_date->format('d M y'),
            ];
        }
        return null;
    }

    public function getFormattedCloseDateAttribute()
    {
        if ($this->close_date) {
            return [
                'day_month' => $this->close_date->format('d M'),
                'year' => $this->close_date->format('y'),
                'full' => $this->close_date->format('d M y'),
            ];
        }
        return null;
    }

    public function getFormattedCancellationDateAttribute()
    {
        if ($this->cancellation_date) {
            return [
                'day_month' => $this->cancellation_date->format('d M'),
                'year' => $this->cancellation_date->format('y'),
                'full' => $this->cancellation_date->format('d M y'),
            ];
        }
        return null;
    }

    public function getStatusAttribute()
    {
        return $this->attributes['status'] ?? 'OPEN';
    }

    public function getContractStatusAttribute()
    {
        return $this->attributes['status'] ?? 'OPEN';
    }

    public function getAssociationStatusAttribute()
    {
        if ($this->type !== 'SELL') {
            return 'NOT_APPLY';
        }

        $saleProducts = $this->sale?->products;
        if (!$saleProducts || $saleProducts->isEmpty()) {
            return 'PENDING';
        }

        $totalProducts = $saleProducts->count();
        $totalAssociated = $saleProducts->filter(function ($dp) {
            return !empty($dp->associated_contract_id) || !empty($dp->contract_order_code);
        })->count();

        if ($totalProducts == $totalAssociated && $totalProducts > 0) {
            return 'COMPLETE';
        } elseif ($totalAssociated == 0) {
            return 'PENDING';
        } else {
            return 'PARTIAL';
        }
    }

    public function getPurchaseContractCodesAttribute()
    {
        if ($this->type !== 'SELL' || !$this->sale) {
            return collect();
        }

        $codes = collect();
        if ($this->sale->products) {
            foreach ($this->sale->products as $p) {
                if (!empty($p->contract_order_code)) {
                    $codes->push($p->contract_order_code);
                } elseif (!empty($p->associated_contract_id)) {
                    $assoc = $p->associatedContract;
                    if ($assoc && !empty($assoc->order_code)) {
                        $codes->push($assoc->order_code);
                    }
                }
            }
        }
        return $codes->unique()->values();
    }
}

