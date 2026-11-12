<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractOrderCodeSequence extends Model
{
    protected $table = 'contract_order_code_sequence';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'last_id',
        'year',
    ];

    protected $casts = [
        'last_id' => 'integer',
        'year' => 'integer',
    ];
}
