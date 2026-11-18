<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected static ?array $columnsCache = null;

    protected $fillable = [
        'name',
        'code',
        'active',
        'version',
        'contact_id',
        // Category
        'productCategory',
        'product_category',
        'category',
        // Formula / Symbol
        'chemicalFormula',
        'symbol',
        // Measurement
        'unitOfMeasurement',
        'uom',
        // Codes
        'hsCode',
        'hs_code',
        'basel_code',
        'base_code_annexix',
        'ec_waste_code',
        'eu_list_of_wastes',
        // Specification / Description
        'description',
        'item_specification',
        'itemSpecification',
    ];

    protected $casts = [
        'active' => 'boolean',
        'version' => 'integer',
    ];

    public function getTableColumns(): array
    {
        if (static::$columnsCache === null) {
            try {
                static::$columnsCache = Schema::getColumnListing($this->getTable());
            } catch (\Throwable $e) {
                static::$columnsCache = [];
            }
        }
        return static::$columnsCache;
    }

    public function hasTableColumn(string $column): bool
    {
        return in_array($column, $this->getTableColumns(), true);
    }

    public function getProductCategoryAttribute()
    {
        return $this->attributes['product_category']
            ?? $this->attributes['productCategory']
            ?? null;
    }

    public function setProductCategoryAttribute($value)
    {
        if ($this->hasTableColumn('product_category')) {
            $this->attributes['product_category'] = $value;
        }
        if ($this->hasTableColumn('productCategory')) {
            $this->attributes['productCategory'] = $value;
        }
    }

    public function getCategoryAttribute()
    {
        return $this->getProductCategoryAttribute();
    }

    public function setCategoryAttribute($value)
    {
        $this->setProductCategoryAttribute($value);
    }

    public function getChemicalFormulaAttribute()
    {
        return $this->attributes['chemicalFormula']
            ?? $this->attributes['symbol']
            ?? null;
    }

    public function setChemicalFormulaAttribute($value)
    {
        if ($this->hasTableColumn('chemicalFormula')) {
            $this->attributes['chemicalFormula'] = $value;
        }
        if ($this->hasTableColumn('symbol')) {
            $this->attributes['symbol'] = $value;
        }
    }

    public function getSymbolAttribute()
    {
        return $this->getChemicalFormulaAttribute();
    }

    public function setSymbolAttribute($value)
    {
        $this->setChemicalFormulaAttribute($value);
    }

    public function getUnitOfMeasurementAttribute()
    {
        return $this->attributes['unitOfMeasurement']
            ?? $this->attributes['uom']
            ?? null;
    }

    public function setUnitOfMeasurementAttribute($value)
    {
        if ($this->hasTableColumn('unitOfMeasurement')) {
            $this->attributes['unitOfMeasurement'] = $value;
        }
        if ($this->hasTableColumn('uom')) {
            $this->attributes['uom'] = $value;
        }
    }

    public function getUomAttribute()
    {
        return $this->getUnitOfMeasurementAttribute();
    }

    public function setUomAttribute($value)
    {
        $this->setUnitOfMeasurementAttribute($value);
    }

    public function getHsCodeAttribute()
    {
        return $this->attributes['hsCode']
            ?? $this->attributes['hs_code']
            ?? null;
    }

    public function setHsCodeAttribute($value)
    {
        if ($this->hasTableColumn('hsCode')) {
            $this->attributes['hsCode'] = $value;
        }
        if ($this->hasTableColumn('hs_code')) {
            $this->attributes['hs_code'] = $value;
        }
    }

    public function getBaselCodeAttribute()
    {
        return $this->attributes['basel_code']
            ?? $this->attributes['base_code_annexix']
            ?? null;
    }

    public function setBaselCodeAttribute($value)
    {
        if ($this->hasTableColumn('basel_code')) {
            $this->attributes['basel_code'] = $value;
        }
        if ($this->hasTableColumn('base_code_annexix')) {
            $this->attributes['base_code_annexix'] = $value;
        }
    }

    public function getBaseCodeAnnexixAttribute()
    {
        return $this->getBaselCodeAttribute();
    }

    public function setBaseCodeAnnexixAttribute($value)
    {
        $this->setBaselCodeAttribute($value);
    }

    public function getEcWasteCodeAttribute()
    {
        return $this->attributes['ec_waste_code']
            ?? $this->attributes['eu_list_of_wastes']
            ?? null;
    }

    public function setEcWasteCodeAttribute($value)
    {
        if ($this->hasTableColumn('ec_waste_code')) {
            $this->attributes['ec_waste_code'] = $value;
        }
        if ($this->hasTableColumn('eu_list_of_wastes')) {
            $this->attributes['eu_list_of_wastes'] = $value;
        }
    }

    public function getEuListOfWastesAttribute()
    {
        return $this->getEcWasteCodeAttribute();
    }

    public function setEuListOfWastesAttribute($value)
    {
        $this->setEcWasteCodeAttribute($value);
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['description']
            ?? $this->attributes['item_specification']
            ?? null;
    }

    public function setDescriptionAttribute($value)
    {
        if ($this->hasTableColumn('description')) {
            $this->attributes['description'] = $value;
        }
        if ($this->hasTableColumn('item_specification')) {
            $this->attributes['item_specification'] = $value;
        }
    }

    public function getItemSpecificationAttribute()
    {
        return $this->getDescriptionAttribute();
    }

    public function setItemSpecificationAttribute($value)
    {
        $this->setDescriptionAttribute($value);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function dealProducts()
    {
        return $this->hasMany(DealProduct::class, 'product_id', 'id');
    }

    public function productContracts()
    {
        return $this->hasMany(ProductContract::class, 'product_id', 'id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'product_id', 'id');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_product_ids', 'product_ids', 'contact_id');
    }
}
