<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Seller;

class ThirdPartyPackageService
{
    public function getCustomerId(
        $customer_name, 
        $customer_phone, 
        $customer_email, 
        $third_party_application_id, 
        $location_link = null, 
        $location_text = null)
    {
        // Try to find existing customer
        $customer = Customer::where('name', $customer_name)
            ->where('phone_number', $customer_phone)
            ->where('email', $customer_email)
            ->where('third_party_application_id', $third_party_application_id)
            ->first();

        if ($customer) {
            return $customer->id; // return existing customer id
        }

        // Create new customer
        $customer = Customer::create([
            'name' => $customer_name,
            'phone_number' => $customer_phone,
            'email' => $customer_email,
            'location_link_1' => $location_link,
            'location_text_1' => $location_text,
            'third_party_application_id' => $third_party_application_id,
        ]);

        return $customer->id;
    }

    public function getSellerId(
        $seller_name,
        $seller_company,
        $seller_phone,
        $seller_email,
        $third_party_application_id,
        $location_link = null,
        $location_text = null
    ) {
        // Try to find existing seller
        $seller = Seller::where('seller_name', $seller_name)
            ->where('phone_number', $seller_phone)
            ->where('email', $seller_email)
            ->where('third_party_application_id', $third_party_application_id)
            ->first();

        if ($seller) {
            return $seller->id; // return existing seller id
        }

        // Create new seller
        $seller = Seller::create([
            'seller_name' => $seller_name,
            'company_name' => $seller_company,
            'phone_number' => $seller_phone,
            'email' => $seller_email,
            'location_link_1' => $location_link,
            'location_text_1' => $location_text,
            'third_party_application_id' => $third_party_application_id,
        ]);

        return $seller->id;
    }
}
