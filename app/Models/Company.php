<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'companyId');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'company_id');
    }

    public function representatives()
    {
        return $this->hasMany(Representative::class, 'company_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'company_id');
    }
}
