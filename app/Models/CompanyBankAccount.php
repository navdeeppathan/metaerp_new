<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBankAccount extends Model
{
    protected $table = 'companies_bank_accounts';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'company_id',
        'bank_accounts_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_accounts_id', 'id');
    }
}
