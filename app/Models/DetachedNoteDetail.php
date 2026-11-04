<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetachedNoteDetail extends Model
{
    protected $table = 'detachednotedetail';
    protected $guarded = [];
    protected $casts = [
        'quantity' => 'double',
        'unitPrice' => 'double',
        'total' => 'double',
    ];

    public function detachedNote()
    {
        return $this->belongsTo(DetachedNote::class, 'detached_note_id');
    }
}
