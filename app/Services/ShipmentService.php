<?php

namespace App\Services;

use App\Repositories\ShipmentRepository;
use Exception;

class ShipmentService
{
    protected ShipmentRepository $repository;

    public function __construct(ShipmentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getTodayShipments($deliveryId)
    {
        return $this->repository->getTodayAssignedShipments($deliveryId);
    }

    public function markAsDelivered($shipmentId, $totalCost)
    {
        return $this->repository->updateShipmentStatus($shipmentId, 1, [
            'receipt_date' => now()
        ]);
    }

    public function validateTotalCost($shipmentId, $totalCost)
    {
        $expectedCost = $this->repository->getTotalCost($shipmentId);
        if ($expectedCost != $totalCost) {
            throw new Exception("يجب أن تتطابق التكلفة الإجمالية مع التكلفة المتوقعة.");
        }
    }

    public function markAsFailed($shipmentId, $data)
    {
        return $this->repository->updateShipmentStatus($shipmentId, 3, [
            'failure_reason' => $data['reason'],
            'reschedule_date' => $data['new_date'] ?? null,
            'custom_reason' => $data['custom_reason'] ?? null
        ]);
    }
}
