<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedFile extends Model
{
    protected $table = 'generatedfiles';
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
