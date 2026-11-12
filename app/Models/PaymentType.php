<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $table = 'payment_type';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class, 'payment_type_id', 'id');
    }
}
