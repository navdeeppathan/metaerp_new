<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bankaccounts';
    public $timestamps = false;
    protected $guarded = [];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'companies_bank_accounts', 'bank_accounts_id', 'company_id');
    }
}
