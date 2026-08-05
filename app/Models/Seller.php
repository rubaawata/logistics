<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Seller extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'seller_name',
        'company_name',
        'phone_number',
        'landline_number',
        'email',
        'password',
        'location_link_1',
        'location_text_1',
        'location_link_2',
        'location_text_2',
        'third_party_application_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function Packages()
    {
        return $this->hasMany(Package::class, 'seller_id', 'id');
    }
}
