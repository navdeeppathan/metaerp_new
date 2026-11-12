<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedFile extends Model
{
    protected $table = 'generatedfiles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'content',
        'generated_type_id',
        'name',
        'template_id',
        'contract_id',
        'offer_id',
        'packing_list_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id', 'id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    public function detachedNotes()
    {
        return $this->hasMany(DetachedNote::class, 'generated_file_id', 'id');
    }
}
