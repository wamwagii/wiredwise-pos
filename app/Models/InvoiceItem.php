<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InvoiceItem extends Model
{
    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price'
    ];
    
    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];
    
    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (filament()->getTenant()) {
                $query->where('tenant_id', filament()->getTenant()->id);
            }
        });
        
        static::creating(function ($model) {
            if (filament()->getTenant()) {
                $model->tenant_id = filament()->getTenant()->id;
            }
        });
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}