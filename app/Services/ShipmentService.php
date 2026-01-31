<?php

namespace App\Services;

use App\Repositories\ShipmentRepository;
use Exception;

class ShipmentService
{
    protected ShipmentRepository $repository;
    protected ThirdPartyPackageService $thirdPartyPackageService;

    public function __construct(ShipmentRepository $repository, ThirdPartyPackageService $thirdPartyPackageService)
    {
        $this->repository = $repository;
        $this->thirdPartyPackageService = $thirdPartyPackageService;
    }

    public function getTodayShipments($deliveryId)
    {
        return $this->repository->getTodayAssignedShipments($deliveryId);
    }

    public function markAsDelivered($shipmentId, $totalCost, $deliveredPiecesCount)
    {
        $updated = $this->repository->updateShipmentStatus($shipmentId, 1, [
            'receipt_date' => now(),
            'paid_amount' => $totalCost,
            'delivered_pieces_count' => $deliveredPiecesCount
        ]);

        if($updated) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'deliver');
        }
        return $updated;
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
        /*$paidAmount = 0;
        if($data['reason'] != 'rto') {
            $paidAmount = $this->repository->getDeliveryCost($shipmentId);
        }*/
        $updated = $this->repository->updateShipmentStatus($shipmentId, 3, [
            'failure_reason' => $data['reason'],
            'reschedule_date' => $data['new_date'] ?? null,
            'custom_reason' => $data['custom_reason'] ?? null,
            'paid_amount' => $data['cancel_total_cost'],
        ]);
        if($updated) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'cancel');
        }
        return $updated;
    }

    public function markAsFailedBecauseOfSeller($shipmentId, $data)
    {
        //$deliveryCost = $this->repository->getDeliveryCost($shipmentId);
        $updated = $this->repository->updateShipmentStatus($shipmentId, 3, [
            'failure_reason' => $data['reason'],
            'paid_amount' => 0,
            'delivery_fee_payer' => 'seller',
        ]);
        if($updated) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'cancel');
        }
        return $updated;
    }

    public function markAsDelayed($shipmentId, $data)
    {
        $canceled = $this->repository->saveOldDeliveryDate($shipmentId, $data['new_date'] ?? null);
        if($canceled) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'cancel');
            return;
        }
        $updated = $this->repository->updateShipmentStatus($shipmentId, 5, [
            'reschedule_date' => $data['new_date'] ?? null,
            'delivery_date' => $data['new_date'] ?? null,
            'failure_reason' => 'rescheduled',
        ]);
        if($updated) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'reschedule');
        }
        return $updated;
    }

    public function markAsDelayedForTomorrow($shipmentId, $data)
    {
        $tomorrow = now()->addDay()->toDateString();
        $canceled = $this->repository->saveOldDeliveryDate($shipmentId, $data['new_date'] ?? null);
        if($canceled) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'cancel');
            return;
        }
        $updated = $this->repository->updateShipmentStatus($shipmentId, 5, [
            'reschedule_date' => $tomorrow,
            'delivery_date' => $tomorrow,
            'failure_reason' => $data['reason'],
            'custom_reason' => $data['custom_reason'] ?? null,
        ]);
        if($updated) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'reschedule');
        }
        return $updated;
    }

    public function markAsPending($shipmentId, $delivery_date)
    {
        $updated = $this->repository->updateShipmentStatus($shipmentId, 5, [
            'delivery_date' => $delivery_date,
            'delivery_date_1' => $delivery_date,
            'package_enter_Hub' => 1
        ]);
        if($updated) {
            $this->thirdPartyPackageService->sendNotificationToThirdParty($shipmentId, 'confirm_package_received');
        }
        return $updated;
    }

    public function updatePackage($packageId, $data)
    {
        //TODO: Implement this method
        return true;
        return $this->repository->updateShipmentStatus($packageId, $data['status'], $data);
    }
}
