<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use crocodicstudio\crudbooster\controllers\CBController;
use Illuminate\Support\Carbon;

use App\Models\Package;
use App\Models\Delivery;
use App\Models\Seller;
use App\Models\Area;
use App\Models\Customer;

class AdminController extends CBController
{

    public function getHome(Request $request)
    {
        if (!CRUDBooster::myId()) {
            return redirect(CRUDBooster::adminPath('login'));
        }

        $dateRange = request('datefilter');

        if ($dateRange) {
            [$startDate, $endDate] = explode(' - ', $dateRange);

            $selected_date_from = Carbon::createFromFormat('m/d/Y', trim($startDate))->startOfDay();
            $selected_date_to = Carbon::createFromFormat('m/d/Y', trim($endDate))->endOfDay();
        } else {
            // Use today's date for both start and end
            $selected_date_from = Carbon::today()->startOfDay();
            $selected_date_to = Carbon::today()->endOfDay();
        }
        $selected_date_filter = $selected_date_from . '-' . $selected_date_to;
        /*$date_from = $request->input('date_from');

        if ($date_from && $date_from != null) {
            $selected_date_from = new Carbon($date_from);
        } else {
            $selected_date_from = Carbon::today();
        }*/

        $selected_area = $request->input('area_id');
        $selected_customer = $request->input('customer_id');
        $selected_seller = $request->input('seller_id');
        $selected_delivery = $request->input('delivery_id');
        $selected_package_id = $request->input('package_id');
        $selected_status = $request->input('package_status');

        //$selected_date_to = $request->input('date_to');

        $delivery_workers = $this->getDeliveryDailyReport($selected_date_from, $selected_date_to);
        //$sellers = $this->getSellerDailyReport($selected_date);
        //$packages = $this->getTodayPackages($selected_date);
        $packages = Package::whereDate('delivery_date', '>=', $selected_date_from)
            ->with(['Seller', 'Customer', 'Delivery', 'Area']);


        if (!is_null($selected_date_to) && $selected_date_to !== 'null') {
            
            $selected_date_to = Carbon::parse($selected_date_to)->endOfDay();
            $packages->where('delivery_date', '<=', $selected_date_to);
        }

        if (!is_null($selected_area) && $selected_area !== 'null') {
            $packages->whereHas('Area', function ($query) use ($selected_area) {
                $query->where('id', $selected_area);
            });
        }

        if (!is_null($selected_customer) && $selected_customer !== 'null') {
            $packages->whereHas('Customer', function ($query) use ($selected_customer) {
                $query->where('id', $selected_customer);
            });
        }

        if (!is_null($selected_seller) && $selected_seller !== 'null') {
            $packages->whereHas('Seller', function ($query) use ($selected_seller) {
                $query->where('id', $selected_seller);
            });
        }

        if (!is_null($selected_delivery) && $selected_delivery !== 'null') {
            $packages->whereHas('Delivery', function ($query) use ($selected_delivery) {
                $query->where('id', $selected_delivery);
            });
        }

        if (!is_null($selected_package_id) && $selected_package_id !== 'null') {
            $packages->where('id', $selected_package_id);
        }

        if (!is_null($selected_status) && $selected_status !== 'null') {
            $packages->where('status', $selected_status);
        }

        $packages = $packages->get();

        $deliveries = Delivery::get();

        $areas = Area::all();

        $sellers = Seller::all();

        $customers = Customer::all();

        return view('home', compact('delivery_workers',  
                                                          'packages', 
                                                          'deliveries', 
                                                          'areas', 
                                                          'sellers', 
                                                          'customers',
                                                          'selected_area',
                                                          'selected_customer',
                                                          'selected_seller',
                                                          'selected_delivery',
                                                          'selected_package_id',
                                                          'selected_status'));
    }

    private function getDeliveryDailyReport($selected_date_from, $selected_date_to)
    {
        $delivery_workers_data = Delivery::whereHas('packages', function ($query) use ($selected_date_from, $selected_date_to) {
            $query->whereDate('delivery_date', '>=', $selected_date_from);
            if (!is_null($selected_date_to) && $selected_date_to !== 'null') {
                $query->where('delivery_date', '<=', $selected_date_to);
            }
        })
        ->with([
            'packages' => function ($query) use ($selected_date_from, $selected_date_to) {
                $query->whereDate('delivery_date', $selected_date_from);
                if (!is_null($selected_date_to) && $selected_date_to !== 'null') {
                    $query->where('delivery_date', '<=', $selected_date_to);
                }
            }
        ])
        ->get();

        $delivery_workers = [];

        foreach ($delivery_workers_data as $item) {
            $delivery = [];
            $delivery['name'] = $item['name'];
            $delivery['phone_number'] = $item['phone_number'];
            $delivery['total_pieces_count'] = $item['packages']->sum('pieces_count');
            $delivery['remaining_pieces'] = $item['packages']->sum('pieces_count') - $item['packages']->where('status', '1')->sum('delivered_pieces_count');
            $delivery['total_amount'] = $item['packages']->sum('paid_amount');

            $delivery_workers[] = $delivery;
        }

        return $delivery_workers;
    }

    private function getSellerDailyReport($selected_date)
    {
        $sellers_data = Seller::whereHas('packages', function ($query) use ($selected_date) {
            $query->whereDate('delivery_date', $selected_date);
        })
            ->with([
                'packages' => function ($query) use ($selected_date) {
                    $query->whereDate('delivery_date', $selected_date);
                }
            ])
            ->get();

        $sellers = [];

        foreach ($sellers_data as $item) {
            $seller = [];
            $seller['name'] = $item['seller_name'];
            $seller['phone_number'] = $item['phone_number'];

            $total_amount = 0;
            $delivered_package_count = 0;
            $undelivered_package_count = 0;
            foreach ($item['packages'] as $package) {
                if ($package['status'] != 'Delivered') {
                    $undelivered_package_count++;
                } else {
                    $delivered_package_count++;
                    $total_amount += $package['delivery_cost'] + $package['package_cost'];
                }

            }

            $seller['total_amount'] = $total_amount;
            $seller['delivered_package_count'] = $delivered_package_count;
            $seller['undelivered_package_count'] = $undelivered_package_count;

            $sellers[] = $seller;
        }

        return $sellers;
    }

    private function getTodayPackages($selected_date)
    {
        $packages = Package::where('delivery_date', $selected_date)
            ->with(['Seller', 'Customer', 'Delivery', 'Area'])
            ->get();
        return $packages;
    }

    public function updatePackageStatus(Request $request)
    {
        $package_id = $request->package_id;
        $new_status = $request->new_status;
        try {
            $updated = Package::where('id', $package_id)->update(['status' => $new_status]);
            if ($updated) {
                return response()->json([
                    'success' => true
                ], 200);
            } else {
                return response()->json([
                    'success' => false
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePackageDelivery(Request $request)
    {
        $package_id = $request->package_id;
        $delivery_id = $request->delivery_id;
        try {
            $updated = Package::where('id', $package_id)->update(['delivery_id' => $delivery_id]);
            if ($updated) {
                return response()->json([
                    'success' => true
                ], 200);
            } else {
                return response()->json([
                    'success' => false
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false
            ], 500);
        }
    }

    public function updatePackageDeliveryInfo(Request $request)
    {
        $package_id = $request->package_id;
        $location_text = $request->location_text;
        $location_link = $request->location_link;
        $delivery_date = $request->delivery_date;
        //$delivery_date = \Carbon\Carbon::createFromFormat('d/m/Y', $delivery_date)->format('Y-m-d');
        $delivery_date = new Carbon($delivery_date);
        try {
            $updated = Package::where('id', $package_id)
                ->update([
                    'location_text' => $location_text,
                    'location_link' => $location_link,
                    'delivery_date' => $delivery_date,
                    'status' => 'Changed'
                ]);
            if ($updated) {
                return response()->json([
                    'success' => true
                ], 200);
            } else {
                return response()->json([
                    'success' => false
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPackagesCountReport(Request $request)  {

        $month = $request->input('month');

        $months = DB::table('packages')
                    ->select(DB::raw("DATE_FORMAT(delivery_date, '%Y-%m') as formatted_date"))
                    ->distinct()
                    ->pluck('formatted_date');
        
        if ($month && $month != null) {
            $selected_month = new Carbon($month);
        } else {
            $selected_month = new Carbon($months[0]);
        }
        $deliveries_data = Delivery::all();
        $deliveries = [];
        list($year, $month) = explode('-', $selected_month);
        foreach($deliveries_data as $item) {
            $total_packages = DB::table('packages')
                                    ->whereYear('delivery_date', $year)
                                    ->whereMonth('delivery_date', $month)
                                    ->where('delivery_id', $item['id'])
                                    ->count();
            $total_delivered_packages = DB::table('packages')
                                    ->whereYear('delivery_date', $year)
                                    ->whereMonth('delivery_date', $month)
                                    ->where('delivery_id', $item['id'])
                                    ->where('status', '1') //status 1 mean موصلة
                                    ->count();
            $total_none_delivered_packages = DB::table('packages')
                                    ->whereYear('delivery_date', $year)
                                    ->whereMonth('delivery_date', $month)
                                    ->where('delivery_id', $item['id'])
                                    ->where('status', '!=', '1') // status 1 mean موصلة
                                    ->count();
            $deliveries []= [
                'id' => $item['id'],
                'name' => $item['name'],
                'total_packages' => $total_packages,
                'total_delivered_packages' => $total_delivered_packages,
                'total_none_delivered_packages' => $total_none_delivered_packages
            ];
        }

        return view('delivery_reports.packages_count_report', compact('deliveries', 'selected_month', 'months'));
    }

}