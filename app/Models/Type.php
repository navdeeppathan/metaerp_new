<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'types';
    protected $guarded = [];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_types', 'type_id', 'contact_id');
    }
}
