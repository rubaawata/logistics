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
            ->where('status', 5)->with('customer')->with('Area')
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

    public function getDeliveryCost($shipmentId)
    {
        $pakcage = Package::where('id', $shipmentId)->first();
        return $pakcage->delivery_cost;
    }

    public function saveOldDeliveryDate($shipmentId, $oldDate)
    {
        $package = Package::where('id', $shipmentId)->first();
        $canceled = false;
        switch($package->number_of_attempts) {
            case 0:
                $package->delivery_date_1 = $package->delivery_date;
                break;
            case 1:
                $package->delivery_date_2 = $package->delivery_date;
                break;
            case 2:
                $package->delivery_date_3 = $package->delivery_date;
                break;
            default:
                $canceled = true;
                $package->status = 3; // Cancelled
                $package->failure_reason = 'too_many_attempts';
                break;
        }
        $package->number_of_attempts += 1;
        $package->save();

        return $canceled;
    }

}
