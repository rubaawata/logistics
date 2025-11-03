<?php

namespace App\Repositories;

use App\Models\Package;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class ShipmentRepository
{
    public function getTodayAssignedShipments($deliveryId)
    {
        return Package::where('delivery_id', $deliveryId)
            ->whereDate('delivery_date', today())
            ->where('status', 5)->with('customer')
            ->get();
    }

    public function updateShipmentStatus($shipmentId, $status, $additionalData = [])
    {
        return Package::where('id', $shipmentId)->update(array_merge([
            'status' => $status,
        ], $additionalData));
    }

    public function getTotalCost($shipmentId)
    {
        $pakcage = Package::where('id', $shipmentId)->first();
        return $pakcage->package_cost + $pakcage->delivery_cost;
    }
}
