<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public $timestamps = false;
    public function Seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

    public function Customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function Delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id');
    }

    
    public function Area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }
}
