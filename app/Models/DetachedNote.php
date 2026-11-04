<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetachedNote extends Model
{
    protected $table = 'detachednote';
    protected $guarded = [];
    protected $casts = ['date' => 'datetime', 'total' => 'double'];

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'meta_company_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function details()
    {
        return $this->hasMany(DetachedNoteDetail::class, 'detached_note_id');
    }
}
