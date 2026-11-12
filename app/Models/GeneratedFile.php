<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedFile extends Model
{
    protected $table = 'generatedfiles';
    public $timestamps = false;
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }
}
