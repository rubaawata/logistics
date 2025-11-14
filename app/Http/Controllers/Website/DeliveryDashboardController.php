<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Package;
use App\Models\Seller;
use App\Services\DeliveryAuthService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;

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
            'total_cost' => 'required|numeric',
            'delivered_pieces_count' => 'required|integer|min:1'
        ]);
        //$this->shipmentService->validateTotalCost($shipmentId, $request->total_cost);
        $this->shipmentService->markAsDelivered($shipmentId, $validated['total_cost'], $validated['delivered_pieces_count']);
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

    // TODO
    public function report($id){
        $delivery = Delivery::where('id', '=', $id)->first();
        $package = Package::where('delivery_id', $delivery->id)->whereDate('delivery_date', today())->with('Customer')->with('Seller')
            ->get();
        $data = [
            'packages' => $package,
            'delivery_name' => $delivery->name,
            'report_date' => now()->format('Y-m-d H:i:s'),
        ];
        return view('pdf.delivery_report', $data);
    }

 //TODO
    public function seller($id)
    {
        $seller = Seller::where('id', '=', $id)->first();
        $package = Package::where('seller_id', $seller->id)->whereDate('delivery_date', today())->with('Customer')->with('Seller')
            ->get();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
            'format' => [200, 180],
            'directionality' => 'rtl',
        ]);
        $data = [
            'packages' => $package,
            'seller_name' => $seller->seller_name,
            'report_date' => now()->format('Y-m-d H:i:s'),
        ];


        return view('pdf.seller_report', $data);
    }
}
