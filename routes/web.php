<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Website\DeliveriesLoginController;
use App\Http\Controllers\Website\DeliveryDashboardController;

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
Route::post('/admin/update-package-status', [AdminController::class, 'updatePackageStatus']);
Route::post('/admin/update-package-delivery', [AdminController::class, 'updatePackageDelivery']);
Route::post('/admin/update-package-delivery-info', [AdminController::class, 'updatePackageDeliveryInfo']);

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
