<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractOrderCodeSequence extends Model
{
    protected $table = 'contract_order_code_sequence';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'last_id' => 'integer',
        'lastId' => 'integer',
        'year' => 'integer',
    ];

    public function getLastIdAttribute()
    {
        return $this->attributes['last_id'] ?? $this->attributes['lastId'] ?? 0;
    }

    public function setLastIdAttribute($value)
    {
        $this->attributes['last_id'] = $value;
        $this->attributes['lastId'] = $value;
    }
}
