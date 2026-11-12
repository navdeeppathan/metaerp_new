<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    protected $table = 'uploadedfiles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'content',
        'contract_id',
        'file_name',
        'url',
        'offer_id',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }
}
