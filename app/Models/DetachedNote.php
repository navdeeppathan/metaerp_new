<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetachedNote extends Model
{
    protected $table = 'detached_note';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'complement',
        'contract_id',
        'generated_file_id',
        'kind',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function generatedFile()
    {
        return $this->belongsTo(GeneratedFile::class, 'generated_file_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(DetachedNoteDetail::class, 'detached_note_id', 'id');
    }

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function getNumberAttribute()
    {
        return $this->attributes['complement'] ?? ('DN-' . $this->id);
    }

    public function getDateAttribute()
    {
        return $this->contract?->date ?? null;
    }

    public function getMetaCompanyAttribute()
    {
        return $this->contract?->sale?->metaCompany ?? $this->contract?->purchase?->metaCompany;
    }

    public function getContactAttribute()
    {
        return $this->contract?->sale?->contact ?? $this->contract?->purchase?->contact;
    }

    public function getCurrencyAttribute()
    {
        return $this->contract?->sale?->currency ?? $this->contract?->purchase?->currency;
    }

    public function getTotalAttribute()
    {
        return $this->relationLoaded('details') ? $this->details->sum('amount') : ($this->details()->sum('amount') ?: 0);
    }
}

