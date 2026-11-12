<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offer';
    public $timestamps = false;
    protected $guarded = [];

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
        return $this->belongsTo(Deal::class, 'purchase_id');
    }

    public function sale()
    {
        return $this->belongsTo(Deal::class, 'sale_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'offer_id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'offer_id');
    }

    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class, 'offer_id');
    }

    public function getMetaCompanyAttribute()
    {
        return $this->sale?->metaCompany ?: $this->purchase?->metaCompany;
    }

    public function getContactAttribute()
    {
        return $this->sale?->contact ?: $this->purchase?->contact;
    }

    public function getProductsAttribute()
    {
        $deal = $this->sale ?: $this->purchase;
        return $deal ? $deal->products : collect();
    }
}
