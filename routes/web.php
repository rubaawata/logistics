<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

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