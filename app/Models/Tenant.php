<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    //use HasFactory, SoftDeletes;
    
    protected $table = 'tenants';
    
    protected $fillable = [
        'uuid',
        'name',
        'domain',
        'type',
        'settings',
        'is_active'
    ];
    
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($tenant) {
            $tenant->uuid = Str::uuid();
        });
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}