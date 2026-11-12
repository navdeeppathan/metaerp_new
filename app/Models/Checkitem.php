<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkitem extends Model
{
    protected $table = 'checkitems';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'checked',
        'name',
        'version',
        'checklist_id',
        'checkitem_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'checked' => 'boolean',
        'version' => 'integer',
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id', 'id');
    }

    public function parentChecklist()
    {
        return $this->belongsTo(Checklist::class, 'checkitem_id', 'id');
    }
}
