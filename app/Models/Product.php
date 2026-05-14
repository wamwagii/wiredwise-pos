<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

class Product extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'barcode',
        'description',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'min_stock_threshold',
        'unit',
        'category',
        'sub_category',
        'tax_info',
        'is_active'
    ];
    
    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_threshold' => 'integer',
        'tax_info' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    // Automatically scope queries to current tenant
    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if ($tenant = Filament::getTenant()) {
                $query->where('tenant_id', $tenant->id);
            }
        });
        
        // Automatically set tenant_id when creating
        static::creating(function ($model) {
            if ($tenant = Filament::getTenant()) {
                $model->tenant_id = $tenant->id;
            }
        });
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_threshold');
    }
    
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }
}