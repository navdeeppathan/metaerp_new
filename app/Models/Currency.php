<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currency';
    protected $guarded = [];
    protected $casts = ['rate' => 'double'];

    public function getCurrencyCodeAttribute(): string
    {
        return $this->attributes['currencyCode'] ?? $this->attributes['description'] ?? 'USD';
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->attributes['currencySymbol'] ?? $this->attributes['symbol'] ?? '$';
    }
}
