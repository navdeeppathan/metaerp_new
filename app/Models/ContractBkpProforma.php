<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractBkpProforma extends Model
{
    protected $table = 'contract_bkp_proforma';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'commercial_invoice_number_formatted',
        'proforma_invoice_number_formatted',
        'sales_invoice_number',
        'sales_invoice_date',
    ];

    protected $casts = [
        'sales_invoice_date' => 'datetime',
    ];
}
