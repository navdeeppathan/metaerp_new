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
}
