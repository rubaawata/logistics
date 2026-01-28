<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ThirdPartyApiController;

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
});
