<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\ExportDealController;
use App\Http\Controllers\Api\ExportLeadController;
use App\Http\Controllers\Api\ExportCustomerController;
use App\Http\Controllers\Api\UserReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/customer/export', ExportCustomerController::class)->middleware('auth');
Route::get('/lead/export', ExportLeadController::class)->middleware('auth');
Route::get('/deal/export', ExportDealController::class)->middleware('auth');
Route::get('/reporte/radiografia-asesor', [UserReportController::class, 'pdf'])->middleware('auth');


Route::get('/storage-link', function () {
    Artisan::call('storage:link');
});
