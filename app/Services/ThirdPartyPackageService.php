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
        $location_text = null
    ) {
        try {
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
        } catch (\Exception $e) {
            return null;
        }
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
        try {
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
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateCustomerPartial(
        int $customer_id,
        ?string $customer_name = null,
        ?string $customer_phone = null,
        ?string $customer_email = null,
        ?string $location_link = null,
        ?string $location_text = null
    ) {
        try {
            $customer = Customer::find($customer_id);

            if (!$customer) {
                return null;
            }

            // Only update fields that are not null
            $data = array_filter([
                'name' => $customer_name,
                'phone_number' => $customer_phone,
                'email' => $customer_email,
                'location_link_1' => $location_link,
                'location_text_1' => $location_text,
            ], fn($value) => !is_null($value));

            if (!empty($data)) {
                $customer->update($data);
            }

            return $customer->id;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateSellerPartial(
        int $seller_id,
        ?string $seller_name = null,
        ?string $seller_company = null,
        ?string $seller_phone = null,
        ?string $seller_email = null,
        ?string $location_link = null,
        ?string $location_text = null
    ) {
        try {
            $seller = Seller::find($seller_id);

            if (!$seller) {
                return null; // or throw exception if you prefer
            }

            // Only update fields that are not null
            $data = array_filter([
                'seller_name' => $seller_name,
                'company_name' => $seller_company,
                'phone_number' => $seller_phone,
                'email' => $seller_email,
                'location_link_1' => $location_link,
                'location_text_1' => $location_text,
            ], fn($value) => !is_null($value));

            if (!empty($data)) {
                $seller->update($data);
            }

            return $seller->id;
        } catch (\Exception $e) {
            return null;
        }
    }
}
