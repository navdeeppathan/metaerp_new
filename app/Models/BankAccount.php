<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bankaccounts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'account_number',
        'iban',
        'name',
        'sort_code',
        'swift',
        'description',
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'companies_bank_accounts', 'bank_accounts_id', 'company_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'payment_account_id', 'id');
    }
}
