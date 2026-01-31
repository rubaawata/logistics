<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartyPackage;
use App\Models\PackageItem;
use App\Models\Area;
use App\Models\ThirdPartyApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\ShipmentService;
use App\Services\ThirdPartyPackageService;
use App\Models\Package;

class ThirdPartyApiController extends Controller
{
    protected ShipmentService $shipmentService;
    protected ThirdPartyPackageService $thirdPartyPackageService;
    public function __construct(ShipmentService $shipmentService, ThirdPartyPackageService $thirdPartyPackageService)
    {
        $this->shipmentService = $shipmentService;
        $this->thirdPartyPackageService = $thirdPartyPackageService;
    }

    //--------------------------------------------------//
    public function createPackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //--------------------------------------------------//
            'seller_name' => 'required|string|max:255',
            'seller_company' => 'nullable|string|max:255',
            'seller_phone' => 'nullable|string|max:255',
            'seller_email' => 'nullable|email|max:255',
            'seller_must_get' => 'required|numeric|min:0',
            'seller_location_link' => 'nullable|string|max:255',
            'seller_location_text' => 'nullable|string',

            // Customer Information
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_must_pay' => 'required|numeric|min:0',

            // Shipping Information
            'area_id' => 'required|exists:areas,id',
            //'delivery_date' => 'required|date',
            'location_link' => 'nullable|string|max:255',
            'location_text' => 'nullable|string',
            'building_number' => 'nullable|string|max:255',
            'floor_number' => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',

            // Package Details
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'open_package' => 'nullable|boolean',
            'reference_number' => 'nullable|string|max:100',
            'delivery_fee_payer' => 'required|string|in:customer,seller',


            // Items Array
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        //--------------------------------------------------//
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        //--------------------------------------------------//
        try {
            DB::beginTransaction();
            //--------------------------------------------------//
            $thirdPartyApp = $request->get('third_party_app');
            if (!$thirdPartyApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third party application not found'
                ], 401);
            }

            // Get area delivery cost
            $area = Area::findOrFail($request->area_id);
            $deliveryCost = $area->delivery_cost ?? 0;
            //--------------------------------------------------//
            // Get customer id
            $customerId = $this->thirdPartyPackageService->getCustomerId(
                $request->customer_name,
                $request->customer_phone,
                $request->customer_email,
                $thirdPartyApp->id,
                $request->location_link,
                $request->location_text
            );
            //--------------------------------------------------//
            // Get seller id
            $sellerId = $this->thirdPartyPackageService->getSellerId(
                $request->seller_name,
                $request->seller_company,
                $request->seller_phone,
                $request->seller_email,
                $thirdPartyApp->id,
                $request->seller_location_link,
                $request->seller_location_text
            );
            //--------------------------------------------------//
            // Calculate delivery date
            $deliveryDate = now();
            //--------------------------------------------------//
            // Create package
            $package = Package::create([
                'third_party_application_id' => $thirdPartyApp->id,
                'reference_number' => $request->reference_number ?? null,
                'seller_cost' => $request->seller_must_get,
                'seller_id' => $sellerId,
                'customer_id' => $customerId,
                'area_id' => $request->area_id,
                'delivery_cost' => $deliveryCost,
                'package_cost' => $request->customer_must_pay,
                'delivery_date' => $deliveryDate,
                'delivery_date_1' => $deliveryDate,
                'location_link' => $request->location_link,
                'location_text' => $request->location_text,
                'building_number' => $request->building_number,
                'floor_number' => $request->floor_number,
                'apartment_number' => $request->apartment_number,
                'description' => $request->description,
                'notes' => $request->notes,
                'open_package' => $request->open_package ?? false,
                'pieces_count' => array_sum(array_column($request->items, 'quantity')),
                'status' => '6',
                'number_of_attempts' => 0,
                'delivery_fee_payer' => $request->delivery_fee_payer,
            ]);
            //--------------------------------------------------//
            // Create items
            $sortOrder = 0;
            foreach ($request->items as $item) {
                PackageItem::create([
                    'package_id' => $package->id,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'sort_order' => $sortOrder++,
                ]);
            }
            //--------------------------------------------------//
            DB::commit();
            //--------------------------------------------------//
            return response()->json([
                'success' => true,
                'message' => 'Package created successfully',
                'data' => [
                    'package_id' => $package->id,
                    'seller_price' => $package->seller_cost,
                    'customer_price' => $package->package_cost,
                    'delivery_cost' => $package->delivery_cost,
                    'items_count' => count($request->items),
                    'total_pieces' => $package->pieces_count,
                    'status' => getPackageStatusEN($package->status),
                    'reference_number' => $package->reference_number,
                ]
            ], 201);
            //--------------------------------------------------//
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //--------------------------------------------------//
    public function getPackage(Request $request, $id)
    {
        $thirdPartyApp = $request->get('third_party_app');
        if (!$thirdPartyApp) {
            return response()->json([
                'success' => false,
                'message' => 'Third party application not found'
            ], 401);
        }

        //--------------------------------------------------//
        $package = ThirdPartyPackage::with('items')->where('third_party_application_id', $thirdPartyApp->id)->where('id_per_user', $id)->first();
        //--------------------------------------------------//
        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found'
            ], 404);
        }
        //--------------------------------------------------//
        return response()->json([
            'success' => true,
            'data' => [
                'package_id' => $package->id_per_user,
                'seller_price' => $package->seller_price,
                'customer_price' => $package->customer_price,
                'delivery_cost' => $package->delivery_cost,
                'status' => $this->getStatusesText($package->status),
                'reference_number' => $package->reference_number,
                'canceld_by' => $this->getCanceldByText($package->canceled_by),
                'delivery_date' => $package->delivery_date,
                'seller_name' => $package->seller_name,
                'seller_company' => $package->seller_company,
                'seller_phone' => $package->seller_phone,
                'seller_email' => $package->seller_email,
                'customer_name' => $package->customer_name,
                'customer_phone' => $package->customer_phone,
                'customer_email' => $package->customer_email,
                'area_id' => $this->gatAreaName($package->area_id),
                'location_link' => $package->location_link,
                'location_text' => $package->location_text,
                'building_number' => $package->building_number,
                'floor_number' => $package->floor_number,
                'apartment_number' => $package->apartment_number,
                'description' => $package->description,
                'items' => $package->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total' => $item->total,
                    ];
                }),
            ]
        ]);
    }

    //--------------------------------------------------//
    public function listPackages(Request $request)
    {
        $query = ThirdPartyPackage::with('items');

        // Filter by third party application (from API key)
        $thirdPartyApp = $request->get('third_party_app');
        if (!$thirdPartyApp) {
            return response()->json([
                'success' => false,
                'message' => 'Third party application not found'
            ], 401);
        }
        $query->where('third_party_application_id', $thirdPartyApp->id);
        //--------------------------------------------------//
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        //--------------------------------------------------//
        // Filter by area if provided
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        //--------------------------------------------------//
        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        //--------------------------------------------------//
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        //--------------------------------------------------//
        $perPage = (int) $request->get('per_page', 20);
        $packages = $query->orderBy('id_per_user', 'desc')->paginate($perPage);
        //--------------------------------------------------//
        // Map packages and items
        $mappedPackages = $packages->map(function ($package) {
            //dump($package->id_per_user);
            return [
                'package_id'       => $package->id_per_user,
                'seller_price'     => $package->seller_price,
                'customer_price'   => $package->customer_price,
                'delivery_cost'    => $package->delivery_cost,
                'status'           => $this->getStatusesText($package->status),
                'reference_number' => $package->reference_number,
                'canceld_by'       => $this->getCanceldByText($package->canceled_by),
                'delivery_date'    => $package->delivery_date,
                'seller_name'      => $package->seller_name,
                'seller_company'   => $package->seller_company,
                'seller_phone'     => $package->seller_phone,
                'seller_email'     => $package->seller_email,
                'customer_name'    => $package->customer_name,
                'customer_phone'   => $package->customer_phone,
                'customer_email'   => $package->customer_email,
                'area_id'          => $this->gatAreaName($package->area_id),
                'location_link'    => $package->location_link,
                'location_text'    => $package->location_text,
                'building_number'  => $package->building_number,
                'floor_number'     => $package->floor_number,
                'apartment_number' => $package->apartment_number,
                'description'      => $package->description,
                'items'            => $package->items->map(function ($item) {
                    return [
                        'id'          => $item->id,
                        'name'        => $item->name,
                        'description' => $item->description,
                        'price'       => $item->price,
                        'quantity'    => $item->quantity,
                        'total'       => $item->price * $item->quantity,
                    ];
                }),
            ];
        });
        //--------------------------------------------------//
        return response()->json([
            'success' => true,
            'data' => $mappedPackages,
            'pagination' => [
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'per_page' => $packages->perPage(),
                'total' => $packages->total(),
            ]
        ]);
    }

    //--------------------------------------------------//
    public function getStatuses()
    {
        $statuses = config('constants.PACKAGE_STATUS', []);
        //--------------------------------------------------//
        $data = [];
        foreach ($statuses as $code => $label) {
            $data[] = [
                'code' => (int) $code,
                'key' => (string) $code,
                'label' => $label,
            ];
        }
        //--------------------------------------------------//
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    //--------------------------------------------------//
    public function getAreas()
    {
        $areas = Area::select('id', 'name')->get();
        //--------------------------------------------------//
        return response()->json([
            'success' => true,
            'data' => $areas
        ]);
    }

    //--------------------------------------------------//
    public function setWebhookUrl(Request $request)
    {
        try {
            DB::beginTransaction();
            //--------------------------------------------------//
            // Validate request
            $validator = Validator::make($request->all(), [
                'webhook_url' => [
                    'required',
                    'url',
                    'max:512',
                    // extra safety: only allow http/https
                    'regex:/^https?:\/\/.+$/i',
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            //--------------------------------------------------//
            // Get authenticated user
            $thirdPartyApp = $request->get('third_party_app');
            if (!$thirdPartyApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third party application not found'
                ], 401);
            }
            //--------------------------------------------------//
            // Save webhook URL
            $thirdPartyApp->webhook_url = $request->webhook_url;
            $thirdPartyApp->save();
            //--------------------------------------------------//
            DB::commit();
            //--------------------------------------------------//
            return response()->json([
                'success' => true,
                'message' => 'Webhook URL saved successfully',
                'data' => [
                    'webhook_url' => $thirdPartyApp->webhook_url,
                ],
            ], 200);
            //--------------------------------------------------//
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save webhook URL',
                //'error' => $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------//
    public function cancelPackage(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            //--------------------------------------------------//
            // Get authenticated user
            $thirdPartyApp = $request->get('third_party_app');
            if (!$thirdPartyApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third party application not found'
                ], 401);
            }
            //--------------------------------------------------//
            // Find the package
            $package = ThirdPartyPackage::where('third_party_application_id', $thirdPartyApp->id)->where('id_per_user', $id)->first();
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            // Check if already cancelled or delivered
            if (in_array($package->status, ['cancelled', 'delivered'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package cannot be cancelled because it is already ' . $this->getStatusesText($package->status),
                ], 400);
            }
            //--------------------------------------------------//
            // Update package status
            $package->status      = 'cancelled';
            $package->canceled_by  = 'your_side';
            $package->save();
            //--------------------------------------------------//
            // If main_package_id exists, notify shipment service
            if ($package->main_package_id) {
                $this->shipmentService->markAsFailed(
                    $package->main_package_id,
                    [
                        'reason'           => 'Cancelled by third party' . $thirdPartyApp->name,
                        'cancel_total_cost' => 0,
                    ]
                );
            }
            //--------------------------------------------------//
            DB::commit();
            //--------------------------------------------------//
            return response()->json([
                'success' => true,
                'message' => 'Package cancelled successfully',
                'data'    => [
                    'package_id' => $package->id_per_user,
                    'status'     => $package->status,
                    'canceld_by' => $this->getCanceldByText($package->canceled_by),
                ]
            ]);
            //--------------------------------------------------//
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel package',
                //'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------//
    public function updatePackage(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            //--------------------------------------------------//
            // Get authenticated third-party app
            $thirdPartyApp = $request->get('third_party_app');
            if (!$thirdPartyApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third party application not found'
                ], 401);
            }
            //--------------------------------------------------//
            // Find the package
            $package = ThirdPartyPackage::where('third_party_application_id', $thirdPartyApp->id)
                ->where('id_per_user', $id)
                ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }
            //--------------------------------------------------//
            // Check if package is cancelled or delivered
            if (in_array($package->status, ['cancelled', 'delivered'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package cannot be updated because it is already ' . $this->getStatusesText($package->status),
                ], 400);
            }
            //--------------------------------------------------//
            // Validate input
            $validator = Validator::make($request->all(), [
                'seller_name'     => 'required|string|max:255',
                'seller_company'  => 'nullable|string|max:255',
                'seller_phone'    => 'nullable|string|max:255',
                'seller_email'    => 'nullable|email|max:255',
                'customer_name'   => 'required|string|max:255',
                'customer_phone'  => 'required|string|max:255',
                'customer_email'  => 'nullable|email|max:255',
                'location_link'   => 'nullable|string|max:255',
                'location_text'   => 'nullable|string',
                'building_number' => 'nullable|string|max:255',
                'floor_number'    => 'nullable|string|max:255',
                'apartment_number' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            //--------------------------------------------------//
            // Update package fields
            $updateData = $validator->validated();
            $package->update($updateData);
            //--------------------------------------------------//
            // Call shipment service if main_package_id exists
            if ($package->main_package_id) {
                $this->shipmentService->updatePackage(
                    $package->main_package_id,
                    $updateData
                );
            }
            //--------------------------------------------------//
            DB::commit();
            //--------------------------------------------------//
            return response()->json([
                'success' => true,
                'message' => 'Package updated successfully',
                'data' => [
                    'package_id' => $package->id_per_user,
                    'status'     => $this->getStatusesText($package->status),
                ]
            ]);
            //--------------------------------------------------//
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update package',
                //'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //--------------------------------------------------//
    private function getStatusesText($status)
    {
        switch ($status) {
            case 'pending':
                return 'Pending';
            case 'cancelled':
                return 'Cancelled';
            case 'delivered':
                return 'Delivered';
            case 'delayed':
                return 'Delayed';
            case 'received_from_seller':
                return 'Received from Seller';
            case 'out_of_delivery':
                return 'Out of Delivery';
            default:
                return 'Unknown';
        }
    }

    //--------------------------------------------------//
    private function getCanceldByText($canceld_by)
    {
        switch ($canceld_by) {
            case 'seller':
                return 'Canceld By Seller';
            case 'customer':
                return 'Canceld By Customer';
            case 'too_many_attempts':
                return 'Canceld because of Too Many Attempts';
            case 'system':
                return 'Canceld By System';
            case 'your_side':
                return 'Canceld from Your Side';
            default:
                return 'Canceld By Unknown';
        }
    }

    //--------------------------------------------------//
    private function getPackageIdPerThirdParty($third_party_app_id)
    {
        $lastPackageId = ThirdPartyPackage::where('third_party_application_id', $third_party_app_id)
            ->max('id_per_user');

        return $lastPackageId ? $lastPackageId + 1 : 1;
    }
}
