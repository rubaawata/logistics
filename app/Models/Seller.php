<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'seller_name',
        'company_name',
        'phone_number',
        'email',
        'location_link_1',
        'location_text_1',
        'third_party_application_id',
    ];
    
    public function Packages()
    {
        return $this->hasMany(Package::class, 'delivery_id', 'id');
    }
}
