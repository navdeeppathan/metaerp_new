<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditDebitType extends Model
{
    protected $table = 'credit_debit_type';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function detachedNoteDetails()
    {
        return $this->hasMany(DetachedNoteDetail::class, 'credit_debit_type_id', 'id');
    }
}
