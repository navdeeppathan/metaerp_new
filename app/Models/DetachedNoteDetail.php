<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetachedNoteDetail extends Model
{
    protected $table = 'detached_note_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'amount',
        'complement',
        'container',
        'credit_debit_type_id',
        'deal_product_id',
        'detached_note_id',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function detachedNote()
    {
        return $this->belongsTo(DetachedNote::class, 'detached_note_id', 'id');
    }

    public function creditDebitType()
    {
        return $this->belongsTo(CreditDebitType::class, 'credit_debit_type_id', 'id');
    }

    public function dealProduct()
    {
        return $this->belongsTo(DealProduct::class, 'deal_product_id', 'id');
    }
}
