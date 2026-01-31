<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ThirdPartyApiController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Third-party API routes (require API key authentication)
Route::prefix('v1/third-party')->middleware('api.key')->group(function () {
    // Package management with items
    Route::post('/packages', [ThirdPartyApiController::class, 'createPackage']);
    Route::get('/packages', [ThirdPartyApiController::class, 'listPackages']);
    Route::get('/packages/{id}', [ThirdPartyApiController::class, 'getPackage']);
    
    Route::get('/areas', [ThirdPartyApiController::class, 'getAreas']);
    Route::get('/statuses', [ThirdPartyApiController::class, 'getStatuses']);
    Route::post('/set-webhook-url', [ThirdPartyApiController::class, 'setWebhookUrl']);
    Route::post('/cancel-package/{id}', [ThirdPartyApiController::class, 'cancelPackage']);
    Route::put('/update-package/{id}', [ThirdPartyApiController::class, 'updatePackage']);
});


Route::post('/admin/test', [AdminController::class, 'test']);