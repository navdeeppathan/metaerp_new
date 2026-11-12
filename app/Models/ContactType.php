<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactType extends Model
{
    protected $table = 'contact_type_ids';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'contact_id',
        'type_ids',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['type_ids'] ?? null;
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }


    public function type()
    {
        return $this->belongsTo(Type::class, 'type_ids', 'id');
    }
}
