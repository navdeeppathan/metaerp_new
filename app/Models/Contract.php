<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'date' => 'datetime',
        'loadingDate' => 'datetime',
    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Deal::class, 'purchase_id');
    }

    public function sale()
    {
        return $this->belongsTo(Deal::class, 'sale_id');
    }

    public function packingLists()
    {
        return $this->hasMany(PackingList::class, 'contract_id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'contract_id');
    }

    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class, 'contract_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'contract_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'contract_id')->orderBy('created_at', 'desc');
    }
}
