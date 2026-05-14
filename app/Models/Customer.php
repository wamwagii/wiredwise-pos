<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

class Customer extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'loyalty_card_number',
        'loyalty_points',
        'address',
        'is_active'
    ];
    
    protected $casts = [
        'loyalty_points' => 'decimal:2',
        'address' => 'array',
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
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    public function scopeHasLoyaltyPoints(Builder $query, float $minPoints = 0): Builder
    {
        return $query->where('loyalty_points', '>=', $minPoints);
    }
}