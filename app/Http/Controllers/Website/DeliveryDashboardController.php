<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\DeliveryAuthService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;

class DeliveryDashboardController extends Controller
{
    protected DeliveryAuthService $authService;
    protected ShipmentService $shipmentService;

    public function __construct(DeliveryAuthService $authService, ShipmentService $shipmentService)
    {
        $this->authService = $authService;
        $this->shipmentService = $shipmentService;
    }

    public function dashboard()
    {
        $delivery = $this->authService->getAuthUser();
        $shipments = $this->shipmentService->getTodayShipments($delivery->id);

        return view('deliveries.dashboard', compact('delivery', 'shipments'));
    }

    public function markAsDelivered(Request $request, $shipmentId)
    {
        $validated = $request->validate([
            'total_cost' => 'required|numeric'
        ]);
        $this->shipmentService->validateTotalCost($shipmentId, $request->total_cost);
        $this->shipmentService->markAsDelivered($shipmentId, $validated['total_cost']);
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الشحنة وإزالتها من القائمة بنجاح.',
        ]);
    }

    public function markAsFailed(Request $request, $shipmentId)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'new_date' => 'nullable|date',
            'custom_reason' => 'nullable|string'
        ]);

        $this->shipmentService->markAsFailed($shipmentId, $validated);

        return response()->json([
            'success' => true,
            'message' => 'تم الإبلاغ عن تعذر التوصيل.',
        ]);
    }

    public function profile()
    {
        $delivery = $this->authService->getAuthUser();
        return view('deliveries.profile', compact('delivery'));
    }
}
