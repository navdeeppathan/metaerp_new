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
