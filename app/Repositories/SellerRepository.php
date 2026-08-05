<?php

namespace App\Repositories;

use App\Models\Seller;
use Illuminate\Support\Facades\Hash;

class SellerRepository
{
    public function findByPhone(string $phone): ?Seller
    {
        return Seller::where('phone_number', $phone)->first();
    }

    public function validateCredentials(Seller $seller, string $password): bool
    {
        return Hash::check($password, $seller->password);
    }

    public function getAuthSeller()
    {
        return auth()->guard('seller')->user();
    }
}
