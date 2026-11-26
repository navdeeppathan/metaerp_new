<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        // Support migration/SQLite schema aliases
        'fileName',
        'html',
        'fileType',
        'user_id',
        'version',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = [
        'name',
        'content',
        'html',
    ];

    public static function getTableColumns(): array
    {
        static $columns = null;
        if ($columns === null) {
            try {
                $columns = Schema::getColumnListing('generatedfiles');
            } catch (\Throwable $e) {
                $columns = [];
            }
        }
        return $columns;
    }

    /**
     * Filter out attributes that do not exist in the physical database table before saving.
     */
    public function save(array $options = [])
    {
        $cols = static::getTableColumns();
        if (!empty($cols)) {
            foreach (array_keys($this->attributes) as $key) {
                if (!in_array($key, $cols)) {
                    unset($this->attributes[$key]);
                }
            }
        }
        return parent::save($options);
    }

    /**
     * Get Document Name (supports both 'name' in MySQL and 'fileName' in SQLite).
     */
    public function getNameAttribute()
    {
        if (isset($this->attributes['name']) && !empty($this->attributes['name'])) {
            return $this->attributes['name'];
        }
        if (isset($this->attributes['fileName']) && !empty($this->attributes['fileName'])) {
            return preg_replace('/\.html$/i', '', $this->attributes['fileName']);
        }
        return 'DOCUMENT_' . ($this->attributes['id'] ?? 'FILE');
    }

    public function setNameAttribute($value)
    {
        $cols = static::getTableColumns();
        if (empty($cols) || in_array('name', $cols)) {
            $this->attributes['name'] = $value;
        }
        if (empty($cols) || in_array('fileName', $cols)) {
            $this->attributes['fileName'] = $value . '.html';
        }
    }

    /**
     * Get Document base64 Content (matches React Buffer.from(content, 'base64')).
     */
    public function getContentAttribute()
    {
        if (isset($this->attributes['content']) && !empty($this->attributes['content'])) {
            return $this->attributes['content'];
        }
        if (isset($this->attributes['html']) && !empty($this->attributes['html'])) {
            return base64_encode($this->attributes['html']);
        }
        return '';
    }

    public function setContentAttribute($value)
    {
        $cols = static::getTableColumns();
        if (empty($cols) || in_array('content', $cols)) {
            $this->attributes['content'] = $value;
        }
        $decoded = base64_decode($value, true);
        $htmlVal = ($decoded !== false && (str_contains($decoded, '<') || ctype_print($decoded))) ? $decoded : $value;
        if (empty($cols) || in_array('html', $cols)) {
            $this->attributes['html'] = $htmlVal;
        }
    }

    /**
     * Get Document decoded HTML for direct rendering / editing.
     */
    public function getHtmlAttribute()
    {
        if (isset($this->attributes['html']) && !empty($this->attributes['html'])) {
            return $this->attributes['html'];
        }
        if (isset($this->attributes['content']) && !empty($this->attributes['content'])) {
            $raw = $this->attributes['content'];
            $decoded = base64_decode($raw, true);
            if ($decoded !== false && (str_contains($decoded, '<') || str_contains($decoded, 'html') || str_contains($decoded, 'div'))) {
                return $decoded;
            }
            return $raw;
        }
        return '';
    }

    public function setHtmlAttribute($value)
    {
        $cols = static::getTableColumns();
        if (empty($cols) || in_array('html', $cols)) {
            $this->attributes['html'] = $value;
        }
        if (empty($cols) || in_array('content', $cols)) {
            $this->attributes['content'] = base64_encode($value);
        }
    }

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
