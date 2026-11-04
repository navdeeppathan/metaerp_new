<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkitem extends Model
{
    protected $table = 'checkitems';
    protected $guarded = [];
    protected $casts = ['checked' => 'boolean'];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }
}
