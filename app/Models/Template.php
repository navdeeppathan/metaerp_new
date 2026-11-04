<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';
    protected $guarded = [];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function getKeysListAttribute()
    {
        if (empty($this->keys)) {
            return [];
        }
        return array_map('trim', explode(',', $this->keys));
    }
}
