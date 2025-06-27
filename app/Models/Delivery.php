<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    public $timestamps = false;
    public function Packages()
    {
        return $this->hasMany(Package::class, 'delivery_id', 'id');
    }
}
