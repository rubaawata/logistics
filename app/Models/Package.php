<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'third_party_application_id',
        'reference_number',
        'seller_cost',
        'seller_id',
        'customer_id',
        'area_id',
        'delivery_cost',
        'package_cost',
        'delivery_date',
        'delivery_date_1',
        'location_link',
        'location_text',
        'building_number',
        'floor_number',
        'apartment_number',
        'description',
        'notes',
        'open_package',
        'pieces_count',
        'status',
        'number_of_attempts',
        'delivery_fee_payer',
    ];
    
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
