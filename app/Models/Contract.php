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
}

