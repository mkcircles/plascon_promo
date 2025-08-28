<?php

use App\Http\Controllers\AirtimeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InMessagesController;
use App\Http\Controllers\CodesController;

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

Route::get('/etherone/airtime/callback', [AirtimeController::class,'receiveEtherOneCallBack'])->name('etherone.airtime.callback');

Route::get('/receive/{msisdn}/{message}', [InMessagesController::class,'receiveMessages'])->name('api.message');
Route::get('/codes/generate/{area}/{count}', [CodesController::class,'generateCodes'])->name('generate.codes');


Route::get('{any}', function () {
    return view('app');
})->where('any','.*');
