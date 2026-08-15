<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Package;
use App\Services\SellerAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SellerPackagesController extends Controller
{
    /**
     * The status assigned to every package a seller creates.
     * 6 = "بانتظار القبول" (Pending Acceptance) - see config/constants.php
     */
    private const STATUS_PENDING_ACCEPTANCE = 6;

    protected SellerAuthService $authService;

    public function __construct(SellerAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * List only the authenticated seller's own packages.
     */
    public function index(Request $request)
    {
        $seller = $this->authService->getAuthUser();

        $query = Package::where('seller_id', $seller->id)
            ->with(['Customer', 'Area']);

        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', '%' . $search . '%')
                    ->orWhere('id', $search)
                    ->orWhereHas('Customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('Area', function ($area) use ($search) {
                        $area->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $packages = $query->orderBy('id', 'desc')->paginate(15);

        return view('sellers.packages.index', compact('seller', 'packages'));
    }

    /**
     * Show the "add new package" form.
     */
    public function create()
    {
        $seller = $this->authService->getAuthUser();
        $areas = Area::orderBy('name')->get();

        return view('sellers.packages.create', compact('seller', 'areas'));
    }

    /**
     * Persist a new package for the authenticated seller.
     */
    public function store(Request $request)
    {
        $seller = $this->authService->getAuthUser();

        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'address' => ['required', 'string', 'max:255'],
            'location_link' => ['nullable', 'string', 'max:255'],
            'package_cost' => ['required', 'integer', 'min:0'],
            'pieces_count' => ['nullable', 'integer', 'min:1'],
            'delivery_fee_payer' => ['required', 'in:seller,customer'],
            'description' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($seller, $data) {
            // Delivery cost is always derived from the chosen area on the server,
            // never trusted from the request, so a seller cannot tamper with it.
            $area = Area::findOrFail($data['area_id']);

            // Reuse an existing customer with the exact same details, otherwise create one.
            $customer = Customer::firstOrCreate(
                [
                    'name' => $data['recipient_name'],
                    'phone_number' => $data['recipient_phone'],
                    'location_text_1' => $data['address'],
                ],
                [
                    'location_link_1' => $data['location_link'] ?? null,
                ]
            );

            $package = Package::create([
                'seller_id' => $seller->id,
                'customer_id' => $customer->id,
                'area_id' => $area->id,
                'package_cost' => $data['package_cost'],
                'delivery_cost' => $area->delivery_cost ?? 0,
                'pieces_count' => $data['pieces_count'] ?? 1,
                'delivery_fee_payer' => $data['delivery_fee_payer'],
                'location_text' => $data['address'],
                'location_link' => $data['location_link'] ?? null,
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => self::STATUS_PENDING_ACCEPTANCE,
                'is_testing' => false,
            ]);

            // Human-friendly, unique tracking number derived from the package id.
            $package->reference_number = 'TRK-' . now()->format('Ymd') . '-' . str_pad($package->id, 5, '0', STR_PAD_LEFT);
            $package->save();
        });

        return redirect()->route('sellers.packages.index')
            ->with('success', 'تمت إضافة الشحنة بنجاح وهي الآن بانتظار القبول.');
    }

    /**
     * Show a single package, but only if it belongs to the authenticated seller.
     */
    public function show($id)
    {
        $seller = $this->authService->getAuthUser();

        $package = Package::with(['Customer', 'Area'])->findOrFail($id);

        // Authorize ownership through the PackagePolicy (throws 403 otherwise).
        Gate::forUser($seller)->authorize('view', $package);

        return view('sellers.packages.show', compact('seller', 'package'));
    }
}
