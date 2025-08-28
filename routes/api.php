<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\InMessagesController;
use App\Http\Controllers\PastWinnerController;
use App\Http\Controllers\CodesController;
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

Route::post('/AIT/airtime', [AirtimeController::class,'updateStatus']);

Route::post('/login', [AuthController::class,'login']);

Route::get('/chart', [InMessagesController::class,'getChart']);

Route::get('/chart/area', [InMessagesController::class,'getAreaChart']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/summary', [InMessagesController::class,'getSummaries']);
    Route::get('search/in-messages/param/{param}', [InMessagesController::class,'searchInMessagesCodes']);
    Route::get('promo-codes/{page?}', [CodesController::class,'getCodes']);
    Route::get('area-codes/{area}/{page?}', [CodesController::class,'getAreaCodes']);
    Route::get('used-codes/{page?}', [CodesController::class,'getUsedCodes']);
    Route::get('in-messages', [InMessagesController::class,'getInMessages']);
    Route::get('in-messages/search/{phone}', [InMessagesController::class,'searchInMessages']);
    Route::get('airtime', [AirtimeController::class,'index']);
    Route::get('airtime/search/{phone}', [AirtimeController::class,'searchAirtime']);
    Route::get('past-winners/{status}', [PastWinnerController::class,'index']);
    Route::post('past-winners/add', [PastWinnerController::class,'store']);
    Route::delete('past-winners/delete/{PastWinner}', [PastWinnerController::class,'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
