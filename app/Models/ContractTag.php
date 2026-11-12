<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTag extends Model
{
    protected $table = 'contract_tags';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'contract_id',
        'tags',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }
}
