<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Delivery extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = [
        'name',
        'phone_number',
        'password',
        'relative_phone_number_1',
        'relative_phone_number_2',
        'personal_photo',
        'trust_receipt_photo',
        'id_photo',
        'driver_licence_photo',
        'vehicle_licence_photo',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public $timestamps = false;
    public function Packages()
    {
        return $this->hasMany(Package::class, 'delivery_id', 'id');
    }
}
