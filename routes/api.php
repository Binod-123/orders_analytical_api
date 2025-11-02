<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\V1\ApiController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::prefix('v1/analytics')->middleware(['jwt.auth', 'throttle:60,1'])->group(function () {
    Route::match(['get', 'post'], '/all_products', [ApiController::class, 'all_Products']);
    Route::match(['get', 'post'], '/search-orders', [ApiController::class, 'searchOrders']);
    Route::match(['get', 'post'], '/sales-summary', [ApiController::class, 'salesSummary']);
    Route::get('/recent-orders', [ApiController::class, 'recentOrders']);
});