<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartyPackage;
use App\Models\ThirdPartyPackageItem;
use App\Models\Area;
use App\Models\ThirdPartyApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ThirdPartyApiController extends Controller
{
    //--------------------------------------------------//
    public function createPackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //--------------------------------------------------//
            'seller_name' => 'required|string|max:255',
            'seller_company' => 'nullable|string|max:255',
            'seller_phone' => 'nullable|string|max:255',
            'seller_email' => 'nullable|email|max:255',

            // Customer Information
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',

            // Shipping Information
            'area_id' => 'required|exists:areas,id',
            'delivery_date' => 'required|date',
            'location_link' => 'nullable|string|max:255',
            'location_text' => 'nullable|string',
            'building_number' => 'nullable|string|max:255',
            'floor_number' => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',

            // Package Details
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'open_package' => 'nullable|boolean',

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
            // Calculate seller price from items (total goods value)
            $sellerPrice = 0;
            foreach ($request->items as $item) {
                $sellerPrice += $item['price'] * $item['quantity'];
            }
            //--------------------------------------------------//


            // Customer will pay goods + delivery (no discount applied here)
            $finalSellerPrice = $sellerPrice;
            $customerPrice = $sellerPrice + $deliveryCost;
            //--------------------------------------------------//
            // Create package
            $package = ThirdPartyPackage::create([
                'third_party_application_id' => $thirdPartyApp->id,
                'seller_name' => $request->seller_name,
                'seller_company' => $request->seller_company,
                'seller_phone' => $request->seller_phone,
                'seller_email' => $request->seller_email,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'area_id' => $request->area_id,
                'delivery_cost' => $deliveryCost,
                'seller_price' => $finalSellerPrice,
                'customer_price' => $customerPrice,
                'delivery_date' => $request->delivery_date,
                'delivery_date_1' => $request->delivery_date,
                'location_link' => $request->location_link,
                'location_text' => $request->location_text,
                'building_number' => $request->building_number,
                'floor_number' => $request->floor_number,
                'apartment_number' => $request->apartment_number,
                'description' => $request->description,
                'notes' => $request->notes,
                'open_package' => $request->open_package ?? false,
                'pieces_count' => array_sum(array_column($request->items, 'quantity')),
                'status' => '5',
                'number_of_attempts' => 0,
                'delivery_fee_payer' => 'customer',
            ]);
            //--------------------------------------------------//
            // Create items
            $sortOrder = 0;
            foreach ($request->items as $item) {
                ThirdPartyPackageItem::create([
                    'third_party_package_id' => $package->id,
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
                    'seller_price' => $package->seller_price,
                    'customer_price' => $package->customer_price,
                    'delivery_cost' => $package->delivery_cost,
                    'items_count' => count($request->items),
                    'total_pieces' => $package->pieces_count,
                    'status' => $package->status,
                    'delivery_date' => $package->delivery_date,
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
    public function getPackage($id)
    {
        $package = ThirdPartyPackage::with('items')
            ->find($id);
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
                'package_id' => $package->id,
                'seller_price' => $package->seller_price,
                'customer_price' => $package->customer_price,
                'delivery_cost' => $package->delivery_cost,
                'status' => $package->status,
                'delivery_date' => $package->delivery_date,
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
        if ($thirdPartyApp) {
            $query->where('third_party_application_id', $thirdPartyApp->id);
        }
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
            $query->where('delivery_date', '>=', $request->date_from);
        }
        //--------------------------------------------------//
        if ($request->has('date_to')) {
            $query->where('delivery_date', '<=', $request->date_to);
        }
        //--------------------------------------------------//
        $perPage = (int) $request->get('per_page', 20);
        $packages = $query->orderBy('id', 'desc')->paginate($perPage);
        //--------------------------------------------------//
        return response()->json([
            'success' => true,
            'data' => $packages->items(),
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
}

