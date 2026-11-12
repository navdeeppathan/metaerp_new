<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'contact_type_ids';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'contact_id',
        'type_ids',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_type_ids', 'type_ids', 'contact_id');
    }
}
