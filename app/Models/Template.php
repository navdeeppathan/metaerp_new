<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'content',
        'country_id',
        'description',
        'name',
        'not_required_keys',
        'type',
        'language_id',
        'original_content',
    ];

    protected $appends = [
        'html',
    ];

    public function getHtmlAttribute()
    {
        $raw = $this->content;
        if (empty($raw)) {
            $raw = $this->original_content ?? $this->originalContent ?? null;
        }
        if (empty($raw)) {
            return '';
        }

        // Try base64 decode
        $decoded = base64_decode($raw, true);
        if ($decoded !== false && (
            str_contains($decoded, '<html') || 
            str_contains($decoded, '<div') || 
            str_contains($decoded, '<body') || 
            str_contains($decoded, '<table') || 
            str_contains($decoded, '<p') || 
            str_contains($decoded, '<h2') || 
            str_contains($decoded, '<span') ||
            str_contains($decoded, '<!DOCTYPE')
        )) {
            return $decoded;
        }

        if ($decoded !== false && ctype_print(str_replace(["\r", "\n", "\t", " "], '', $decoded))) {
            return $decoded;
        }

        return $raw;
    }

    public function setHtmlAttribute($value)
    {
        $this->attributes['content'] = base64_encode($value);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function actions()
    {
        return $this->hasMany(Action::class, 'template_id', 'id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'template_id', 'id');
    }
}
