<?php

namespace App\Repositories;

use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;

class DeliveryRepository
{
    public function findByPhone(string $phone): ?Delivery
    {
        return Delivery::where('phone_number', $phone)->first();
    }

    public function validateCredentials(Delivery $delivery, string $password): bool
    {
        return Hash::check($password, $delivery->password);
    }

    public function getAuthDelivery()
    {
        return auth()->guard('delivery')->user();
    }
}
