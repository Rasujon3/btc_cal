<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\RateController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('throttle:60,1')->group(function () {
    Route::apiResource('rates', RateController::class);
    Route::post('rates/delete-all', [RateController::class, 'deleteAll']);
    Route::post('btc-calculation', [ApiController::class, 'btcCalculation']);
    Route::post('set-alerm-status', [ApiController::class, 'setAlermStatus']);
    Route::get('btc-to-usd-rate', [ApiController::class, 'btcToUsdRate']);
});
