<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyPackageItem extends Model
{
    protected $fillable = [
        'third_party_package_id',
        'name',
        'description',
        'price',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(ThirdPartyPackage::class, 'third_party_package_id');
    }

    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}

