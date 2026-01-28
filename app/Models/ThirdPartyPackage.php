<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyPackage extends Model
{
    protected $fillable = [
        'third_party_application_id',
        'seller_name',
        'seller_company',
        'seller_phone',
        'seller_email',
        'customer_name',
        'customer_phone',
        'customer_email',
        'area_id',
        'delivery_id',
        'delivery_cost',
        'seller_price',
        'customer_price',
        'price_per_piece',
        'delivery_date',
        'receipt_date',
        'location_link',
        'location_text',
        'building_number',
        'floor_number',
        'apartment_number',
        'image',
        'description',
        'pieces_count',
        'notes',
        'open_package',
        'status',
        'number_of_attempts',
        'failure_reason',
        'reschedule_date',
        'custom_reason',
        'delivered_pieces_count',
        'paid_amount',
        'delivery_fee_payer',
        'delivery_date_1',
        'delivery_date_2',
        'delivery_date_3',
    ];

    protected $casts = [
        'delivery_cost' => 'decimal:2',
        'seller_price' => 'decimal:2',
        'customer_price' => 'decimal:2',
        'price_per_piece' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'open_package' => 'boolean',
        'delivery_date' => 'date',
        'receipt_date' => 'date',
        'reschedule_date' => 'date',
        'delivery_date_1' => 'date',
        'delivery_date_2' => 'date',
        'delivery_date_3' => 'date',
    ];

    public function thirdPartyApplication()
    {
        return $this->belongsTo(ThirdPartyApplication::class, 'third_party_application_id');
    }

    public function Area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function Delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function items()
    {
        return $this->hasMany(ThirdPartyPackageItem::class, 'third_party_package_id')->orderBy('sort_order');
    }
}

