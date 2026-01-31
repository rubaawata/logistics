<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'location_link_1',
        'location_text_1',
        'third_party_application_id',
    ];
}
