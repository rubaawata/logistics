<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Website\DeliveriesLoginController;
use App\Http\Controllers\Website\DeliveryDashboardController;
use App\Http\Controllers\Website\SellersLoginController;
use App\Http\Controllers\Website\SellerPackagesController;
use App\Http\Controllers\ThirdPartyFinancialReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});


Route::get('/admin', [AdminController::class, 'getHome']);
Route::get('/admin/pending-packages', [AdminController::class, 'getPendingPackages']);
Route::get('/admin/waiting-and-new-packages', [AdminController::class, 'getWaitingAndNewPackages']);
Route::post('/admin/update-package-status', [AdminController::class, 'updatePackageStatus']);
Route::post('/admin/update-package-delivery', [AdminController::class, 'updatePackageDelivery']);
Route::post('/admin/update-package-delivery-info', [AdminController::class, 'updatePackageDeliveryInfo']);
Route::post('/admin/confirm-package-received', [AdminController::class, 'confirmPackageReceived']);
Route::post('/admin/update-package-shipments', [AdminController::class, 'updatePackageShipments']);

Route::get('/admin/packages-count-report', [AdminController::class, 'getPackagesCountReport']);

Route::get('/clear/cache', function () {
    \Artisan::call('optimize:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('event:generate');
    \Artisan::call('view:clear');
    return 'done';
});

//TODO
Route::get('admin/export-delivery-report/{id}', [DeliveryDashboardController::class, 'report']);
Route::get('admin/export-seller-report/{id}', [DeliveryDashboardController::class, 'seller']);

//TODO
Route::get('admin/export-third-party-report/{id}/{date?}', [DeliveryDashboardController::class, 'thirdParty']);

// Third Party Financial Report
Route::get('/admin/third-party-financial-report', [ThirdPartyFinancialReportController::class, 'index'])->name('third-party-financial-report.index');
Route::post('/admin/third-party-financial-report/export', [ThirdPartyFinancialReportController::class, 'export'])->name('third-party-financial-report.export');




Route::prefix('delivery')->name('deliveries.')->group(function () {
    Route::get('/login', [DeliveriesLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [DeliveriesLoginController::class, 'login'])->name('login.submit');
    Route::get('/logout', [DeliveriesLoginController::class, 'logout'])->name('logout');

    Route::middleware('auth:delivery')->group(function () {
        Route::get('/dashboard', [DeliveryDashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/shipments/{shipment}/delivered', [DeliveryDashboardController::class, 'markAsDelivered'])
            ->name('shipments.delivered');
        Route::post('/shipments/{shipment}/failed', [DeliveryDashboardController::class, 'markAsFailed'])
            ->name('shipments.failed');
    });
});


Route::prefix('seller')->name('sellers.')->group(function () {
    Route::get('/login', [SellersLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [SellersLoginController::class, 'login'])->name('login.submit');
    Route::get('/logout', [SellersLoginController::class, 'logout'])->name('logout');

    Route::middleware('auth:seller')->group(function () {
        Route::get('/packages', [SellerPackagesController::class, 'index'])->name('packages.index');
        Route::get('/packages/create', [SellerPackagesController::class, 'create'])->name('packages.create');
        Route::post('/packages', [SellerPackagesController::class, 'store'])->name('packages.store');
        Route::get('/packages/{id}/edit', [SellerPackagesController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{id}', [SellerPackagesController::class, 'update'])->name('packages.update');
        Route::get('/packages/{id}', [SellerPackagesController::class, 'show'])->name('packages.show');
    });
});
