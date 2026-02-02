<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ThirdPartyApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\ThirdPartyFinancialReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ThirdPartyFinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $thirdParties = ThirdPartyApplication::where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $selectedThirdPartyId = $request->input('third_party_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $packages = collect();
        $summary = [
            'total_seller_cost' => 0,
            'total_delivery_cost' => 0,
            'net_amount' => 0,
            'packages_count' => 0,
        ];

        if ($selectedThirdPartyId) {
            $thirdParty = ThirdPartyApplication::find($selectedThirdPartyId);
            $discount = $thirdParty ? ($thirdParty->discount ?? 0) : 0;
            $cancellationFeePercentage = $thirdParty ? ($thirdParty->cancellation_fee_percentage ?? 25) : 25; 

            $query = Package::where('third_party_application_id', $selectedThirdPartyId)
                ->whereNotNull('third_party_application_id');
                // Show all packages, but calculate only for status NOT 5 and NOT 6

            // Filter by delivery_date only, and show all packages where delivery_date is null
            if ($dateFrom || $dateTo) {
                $query->where(function($q) use ($dateFrom, $dateTo) {
                    // Show packages where delivery_date is null
                    $q->whereNull('delivery_date')
                    // OR delivery_date is within the date range
                    ->orWhere(function($subQ) use ($dateFrom, $dateTo) {
                        if ($dateFrom) {
                            $subQ->whereDate('delivery_date', '>=', Carbon::parse($dateFrom)->format('Y-m-d'));
                        }
                        if ($dateTo) {
                            $subQ->whereDate('delivery_date', '<=', Carbon::parse($dateTo)->format('Y-m-d'));
                        }
                    });
                });
            }

            $packages = $query->with(['ThirdPartyApplication', 'Customer', 'Area'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate costs with business logic - only for packages NOT status 5 and NOT 6
            // Should receive from customer: package_cost + delivery_cost (when customer pays delivery)
            // Actually received from customer: paid_amount
            // Third party pays: seller_cost (seller_must_get)
            // Third party profit = paid_amount - seller_cost
            // Third party pays: delivery_cost_after_discount (to delivery service)
            // Net = profit - delivery_cost_after_discount
            
            $totalShouldReceive = 0; // package_cost + delivery_cost (when customer pays) - what they should get
            $totalActuallyReceived = 0; // paid_amount - what they actually got
            $totalSellerCost = 0; // What third party pays to sellers
            $totalDeliveryCostBeforeDiscount = 0;
            $totalDeliveryCostAfterDiscount = 0;
            $totalShipmentsCost = 0;

            foreach ($packages as $package) {
                $totalShipmentsCost += $package->cost_of_shipments ?? 0;
                // Only calculate costs for packages that are NOT status 5 and NOT 6
                if (in_array($package->status, [5, 6])) {
                    continue; 
                }
                // If status is 3 (cancelled) AND package_enter_Hub is 0, delivery company didn't take the order
                // So don't take money - set all amounts to 0
                if ($package->status == 3 && ($package->package_enter_Hub ?? 0) == 0) {
                    continue; // Skip calculation - all amounts are 0
                }

                $packageCost = $package->package_cost ?? 0; // customer_must_pay
                $paidAmount = $package->paid_amount ?? 0; // what third party actually received
                $sellerCost = $package->seller_cost ?? 0; // seller_must_get (what third party pays)
                $deliveryCost = $package->delivery_cost ?? 0;
                $deliveryFeePayer = $package->delivery_fee_payer ?? 'customer';

                // What third party should receive: package_cost + delivery_cost (if customer pays delivery)
                $shouldReceive = $packageCost;
                if ($deliveryFeePayer == 'customer') {
                    $shouldReceive += $deliveryCost;
                }

                // For cancelled packages (status 3), apply cancellation fee percentage to delivery_cost
                // But only if package_enter_Hub is not 0 (delivery company took the order)
                // For cancelled packages, do NOT apply discount percentage
                if ($package->status == 3 && ($package->package_enter_Hub ?? 0) != 0) {
                    $deliveryCost = $deliveryCost * ($cancellationFeePercentage / 100);
                    // For cancelled packages, don't apply discount - delivery_cost_after_discount = delivery_cost
                    $deliveryCostAfterDiscount = $deliveryCost;
                } else {
                    // Apply discount to delivery_cost (what third party pays to delivery service)
                    $deliveryCostAfterDiscount = $deliveryCost * (1 - ($discount / 100));
                }

                $totalShouldReceive += $shouldReceive;
                $totalActuallyReceived += $paidAmount;
                $totalSellerCost += $sellerCost;
                $totalDeliveryCostBeforeDiscount += $deliveryCost;
                $totalDeliveryCostAfterDiscount += $deliveryCostAfterDiscount;
            }

            // Third party profit = paid_amount - seller_cost (using actual received amount)
            $totalProfit = $totalActuallyReceived - $totalSellerCost;
            // Net = profit - delivery_cost_after_discount
            $netAmount = $totalProfit - $totalDeliveryCostAfterDiscount;

            $summary['total_should_receive'] = $totalShouldReceive; // package_cost + delivery_cost (what they should get)
            $summary['total_actually_received'] = $totalActuallyReceived; // paid_amount (what they actually got)
            $summary['total_seller_cost'] = $totalSellerCost; // What third party pays to sellers
            $summary['total_profit'] = $totalProfit; // paid_amount - seller_cost
            $summary['total_delivery_cost_before_discount'] = $totalDeliveryCostBeforeDiscount;
            $summary['total_delivery_cost_after_discount'] = $totalDeliveryCostAfterDiscount;
            $summary['discount_percentage'] = $discount;
            $summary['discount_amount'] = $totalDeliveryCostBeforeDiscount - $totalDeliveryCostAfterDiscount;
            $summary['net_amount'] = $netAmount; // profit - delivery_cost_after_discount
            $summary['total_shipments_cost'] = $totalShipmentsCost;
            $summary['packages_count'] = $packages->count();
        }

        $thirdParty = null;
        if ($selectedThirdPartyId) {
            $thirdParty = ThirdPartyApplication::find($selectedThirdPartyId);
        }

        return view('third_party_financial_report.index', compact(
            'thirdParties',
            'selectedThirdPartyId',
            'dateFrom',
            'dateTo',
            'packages',
            'summary',
            'thirdParty'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'third_party_id' => 'required|exists:third_party_applications,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $thirdParty = ThirdPartyApplication::findOrFail($request->third_party_id);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $filename = 'third_party_financial_report_' . $thirdParty->company_name . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new ThirdPartyFinancialReportExport($request->third_party_id, $dateFrom, $dateTo, $thirdParty->company_name),
            $filename
        );
    }
}

