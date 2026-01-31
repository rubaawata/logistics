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

        if ($selectedThirdPartyId && ($dateFrom || $dateTo)) {
            $query = Package::where('third_party_application_id', $selectedThirdPartyId)
                ->whereNotNull('third_party_application_id')
                ->whereNotNull('receipt_date'); // Only packages that have been received/delivered

            // Filter by receipt date (actual delivery date)
            if ($dateFrom) {
                $query->whereDate('receipt_date', '>=', Carbon::parse($dateFrom)->format('Y-m-d'));
            }
            if ($dateTo) {
                $query->whereDate('receipt_date', '<=', Carbon::parse($dateTo)->format('Y-m-d'));
            }

            $packages = $query->with(['ThirdPartyApplication', 'Customer', 'Area'])
                ->orderBy('receipt_date', 'desc')
                ->get();

            $summary['total_seller_cost'] = $packages->sum('seller_cost') ?? 0;
            $summary['total_delivery_cost'] = $packages->sum('delivery_cost') ?? 0;
            $summary['net_amount'] = $summary['total_delivery_cost'] - $summary['total_seller_cost'];
            $summary['packages_count'] = $packages->count();
        }

        return view('third_party_financial_report.index', compact(
            'thirdParties',
            'selectedThirdPartyId',
            'dateFrom',
            'dateTo',
            'packages',
            'summary'
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

